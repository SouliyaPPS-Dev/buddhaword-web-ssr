<?php
namespace App\Services;
 
class BookConverter
{
    private string $tmpFile;
    private string $outDir;
    private string $slug;
    private string $title;

    public function __construct(string $tmpFile, string $slug, string $outDir, string $title = '')
    {
        $this->tmpFile = $tmpFile;
        $this->slug = $slug;
        $this->outDir = rtrim($outDir, '/');
        $this->title = $title ?: $slug;
    }

    public function convert(): array
    {
        $ext = strtolower(pathinfo($this->tmpFile, PATHINFO_EXTENSION));
        return match ($ext) {
            'pdf' => $this->convertPdf(),
            'docx' => $this->convertDocx(),
            'doc' => $this->convertDoc(),
            default => ['success' => false, 'error' => "Unsupported format: $ext"],
        };
    }

    private function convertPdf(): array
    {
        if (!class_exists('\\Smalot\\PdfParser\\Parser')) {
            return ['success' => false, 'error' => 'PDF parser library not available'];
        }

        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($this->tmpFile);
            $pages = $pdf->getPages();
            $totalPages = count($pages);

            if ($totalPages === 0) {
                return ['success' => false, 'error' => 'PDF has no pages'];
            }

            if (!is_dir($this->outDir)) {
                mkdir($this->outDir, 0755, true);
            }

            $pagesData = [];
            $textIndex = [];

            foreach ($pages as $i => $page) {
                $pageNum = $i + 1;
                $rawText = $page->getText();
                $text = $this->cleanPageNumbers($this->cleanText($rawText));
                $pagesData[] = [
                    'page' => $pageNum,
                    'text' => $text,
                    'words' => [],
                ];
                $firstLine = mb_substr(preg_replace('/\s+/', ' ', $text), 0, 100);
                $textIndex[] = [
                    'page' => $pageNum,
                    'preview' => trim($firstLine),
                ];
            }

            // Save pages.json
            file_put_contents(
                $this->outDir . '/pages.json',
                json_encode($pagesData, JSON_UNESCAPED_UNICODE)
            );

            // Save index.json
            file_put_contents(
                $this->outDir . '/index.json',
                json_encode($textIndex, JSON_UNESCAPED_UNICODE)
            );

            // Save book.json
            $bookInfo = [
                'title' => $this->title,
                'year' => intval(date('Y')),
                'totalPages' => $totalPages,
                'type' => 'pdf',
            ];
            file_put_contents(
                $this->outDir . '/book.json',
                json_encode($bookInfo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            );

            // Generate cover from first page (if possible)
            $this->generateCover($pages[0]);

            return [
                'success' => true,
                'totalPages' => $totalPages,
                'type' => 'pdf',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'PDF conversion error: ' . $e->getMessage()];
        }
    }

    private function convertDocx(): array
    {
        if (!class_exists('\\ZipArchive')) {
            return ['success' => false, 'error' => 'ZIP extension not available'];
        }

        try {
            $zip = new \ZipArchive();
            if ($zip->open($this->tmpFile) !== true) {
                return ['success' => false, 'error' => 'Cannot open DOCX file'];
            }

            $xmlContent = $zip->getFromName('word/document.xml');
            $zip->close();

            if ($xmlContent === false) {
                return ['success' => false, 'error' => 'Invalid DOCX: missing document.xml'];
            }

            $xml = simplexml_load_string($xmlContent);
            if ($xml === false) {
                return ['success' => false, 'error' => 'Invalid DOCX: cannot parse XML'];
            }

            $namespaces = $xml->getNamespaces(true);
            $body = $xml->body ?? $xml->children($namespaces['w'])->body ?? null;
            if (!$body) {
                return ['success' => false, 'error' => 'Invalid DOCX: no body element'];
            }

            $paragraphs = $body->xpath('//w:p');
            if (!$paragraphs) {
                $paragraphs = $body->children($namespaces['w'])->p ?? [];
            }

            $texts = [];
            foreach ($paragraphs as $p) {
                $parts = [];
                foreach ($p->children($namespaces['w'])->r ?? $p->xpath('.//w:t') as $r) {
                    $t = (string)($r->t ?? $r);
                    if (trim($t) !== '') {
                        $parts[] = $t;
                    }
                }
                $line = $this->cleanText(implode('', $parts));
                if ($line !== '') {
                    $texts[] = $line;
                }
            }

            return $this->splitIntoPages($texts, 'docx');
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'DOCX conversion error: ' . $e->getMessage()];
        }
    }

    private function convertDoc(): array
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            $tmpTxt = tempnam(sys_get_temp_dir(), 'doc_') . '.txt';
            $cmd = sprintf(
                'textutil -convert txt -output %s %s 2>/dev/null',
                escapeshellarg($tmpTxt),
                escapeshellarg($this->tmpFile)
            );
            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0 || !file_exists($tmpTxt)) {
                return ['success' => false, 'error' => 'Failed to convert .doc file (textutil not available)'];
            }

            $text = file_get_contents($tmpTxt);
            @unlink($tmpTxt);
        } else {
            $tmpTxt = tempnam(sys_get_temp_dir(), 'doc_') . '.txt';
            $cmd = sprintf(
                'antiword %s 2>/dev/null > %s',
                escapeshellarg($this->tmpFile),
                escapeshellarg($tmpTxt)
            );
            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0 || !file_exists($tmpTxt) || filesize($tmpTxt) === 0) {
                $cmd2 = sprintf(
                    'catdoc %s 2>/dev/null > %s',
                    escapeshellarg($this->tmpFile),
                    escapeshellarg($tmpTxt)
                );
                exec($cmd2, $output2, $exitCode2);
                if ($exitCode2 !== 0 || !file_exists($tmpTxt) || filesize($tmpTxt) === 0) {
                    @unlink($tmpTxt);
                    return ['success' => false, 'error' => 'Failed to convert .doc file. Install antiword or catdoc on server'];
                }
            }

            $text = file_get_contents($tmpTxt);
            @unlink($tmpTxt);
        }

        if (trim($text) === '') {
            return ['success' => false, 'error' => 'No text extracted from .doc file'];
        }

        $lines = explode("\n", $text);
        $texts = [];
        foreach ($lines as $line) {
            $line = $this->cleanText($line);
            if ($line !== '') {
                $texts[] = $line;
            }
        }

        return $this->splitIntoPages($texts, 'docx');
    }

    private function splitIntoPages(array $paragraphs, string $type): array
    {
        $totalChars = array_sum(array_map('mb_strlen', $paragraphs));
        $totalPages = max(1, (int)ceil($totalChars / 2000));
        $charsPerPage = (int)ceil($totalChars / $totalPages);

        if (!is_dir($this->outDir)) {
            mkdir($this->outDir, 0755, true);
        }

        $pagesData = [];
        $textIndex = [];
        $currentText = [];
        $currentCount = 0;
        $pageNum = 1;

        foreach ($paragraphs as $text) {
            $textLen = mb_strlen($text) + 1;
            if ($currentCount + $textLen > $charsPerPage * 1.5 && $currentCount > 0) {
                $pageText = $this->cleanText(implode("\n", $currentText));
                $pagesData[] = [
                    'page' => $pageNum,
                    'text' => $pageText,
                    'words' => [],
                ];
                $firstLine = mb_substr(preg_replace('/\s+/', ' ', $pageText), 0, 100);
                $textIndex[] = [
                    'page' => $pageNum,
                    'preview' => trim($firstLine),
                ];
                $pageNum++;
                $currentText = [$text];
                $currentCount = $textLen;
            } else {
                $currentText[] = $text;
                $currentCount += $textLen;
            }
        }

        if (!empty($currentText)) {
            $pageText = $this->cleanText(implode("\n", $currentText));
            $pagesData[] = [
                'page' => $pageNum,
                'text' => $pageText,
                'words' => [],
            ];
            $firstLine = mb_substr(preg_replace('/\s+/', ' ', $pageText), 0, 100);
            $textIndex[] = [
                'page' => $pageNum,
                'preview' => trim($firstLine),
            ];
        }

        file_put_contents(
            $this->outDir . '/pages.json',
            json_encode($pagesData, JSON_UNESCAPED_UNICODE)
        );
        file_put_contents(
            $this->outDir . '/index.json',
            json_encode($textIndex, JSON_UNESCAPED_UNICODE)
        );

        $bookInfo = [
            'title' => $this->title,
            'year' => intval(date('Y')),
            'totalPages' => count($pagesData),
            'type' => $type,
        ];
        file_put_contents(
            $this->outDir . '/book.json',
            json_encode($bookInfo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        return [
            'success' => true,
            'totalPages' => count($pagesData),
            'type' => $type,
        ];
    }

    private function generateCover(\Smalot\PdfParser\Page $firstPage): void
    {
        $coverPath = $this->outDir . '/cover.png';
        if (file_exists($coverPath)) return;

        $text = $firstPage->getText();
        $text = preg_replace('/\s+/', ' ', trim($text));
        $title = mb_substr($text, 0, 100);

        $img = imagecreatetruecolor(400, 600);
        $bg = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $bg);
        $textColor = imagecolorallocate($img, 80, 50, 30);

        $fontFile = $this->findLaoFont();
        if ($fontFile) {
            $size = 12;
            $lines = explode("\n", wordwrap($title, 40, "\n", true));
            $y = 50;
            foreach ($lines as $line) {
                imagettftext($img, $size, 0, 20, $y, $textColor, $fontFile, $line);
                $y += 30;
            }
        } else {
            imagestring($img, 3, 20, 50, $title ?: $this->title, $textColor);
        }

        imagepng($img, $coverPath);
        imagedestroy($img);
    }

    private function findLaoFont(): ?string
    {
        $candidates = [
            '/System/Library/Fonts/Supplemental/NotoSansLao.ttf',
            '/usr/share/fonts/truetype/noto/NotoSansLao-Regular.ttf',
            '/usr/share/fonts/noto/NotoSansLao-Regular.ttf',
            '/usr/share/fonts/truetype/lao/NotoSansLao-Regular.ttf',
        ];
        foreach ($candidates as $path) {
            if (file_exists($path)) return $path;
        }

        // Search for any TTF with Lao in name
        $search = PHP_OS_FAMILY === 'Darwin'
            ? glob('/System/Library/Fonts/**/*Lao*')
            : glob('/usr/share/fonts/**/*Lao*', GLOB_NOSORT);
        foreach ($search as $font) {
            if (is_file($font)) return $font;
        }

        return null;
    }

    private function cleanText(string $text): string
    {
        $text = preg_replace('/[\x{200B}\x{200C}\x{200D}\x{200E}\x{200F}\x{2060}\x{2061}\x{2062}\x{2063}\x{2064}\x{FEFF}\x{00AD}]/u', '', $text);
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim($text);
    }

    private function cleanPageNumbers(string $text): string
    {
        $text = preg_replace('/^\d+\s+/u', '', $text);
        $text = preg_replace('/\s+\d+$/u', '', $text);
        return trim($text);
    }
}
