<?php
namespace App\Controllers;
 
class FilesController
{
    private function assetsDir()
    {
        return __DIR__ . '/../../public/assets';
    }

    private function tmpDir()
    {
        $dir = __DIR__ . '/../../storage/tmp';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    private function booksPath() 
    {
        return $this->assetsDir() . '/books.json';
    }

    private function loadBooks()
    {
        $path = $this->booksPath();
        if (!file_exists($path)) return [];
        return json_decode(file_get_contents($path), true) ?: [];
    }

    private function saveBooks($books)
    {
        file_put_contents($this->booksPath(), json_encode($books, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private function json($data, $code = 200)
    {
        if (ob_get_level()) ob_clean();
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function detectTitle($filename)
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/[_\s]+/', ' ', $name);
        return trim($name);
    }

    private function slugify($name)
    {
        $name = pathinfo($name, PATHINFO_FILENAME);
        $name = preg_replace('/[\s_()\[\]{}]+/u', '-', $name);
        $name = preg_replace('/[^a-zA-Z0-9\x{0E80}-\x{0EFF}-]/u', '', $name);
        $name = preg_replace('/-+/', '-', $name);
        $name = trim($name, '-');
        $name = mb_strtolower($name);
        $name = mb_substr($name, 0, 60);
        return $name ?: 'book'; 
    }
 
    public function index()
    {
        $books = array_reverse($this->loadBooks());
        $canonicalUrl = canonicalUrl();

        return view('pages.upload.index', [
            'books' => $books,
            'seo' => [
                'title' => 'ຈັດການປຶ້ມ - ຄຳສອນພຸດທະ',
                'description' => 'ຈັດການຂໍ້ມູນປຶ້ມ ອັບໂຫຼດ ແກ້ໄຂ ລຶບ',
                'robots' => 'noindex, nofollow',
            ]
        ]);
    }

    public function store()
    {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'error' => 'Upload failed'], 400);
        }

        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['pdf', 'docx', 'doc'])) {
            $this->json(['success' => false, 'error' => 'Only PDF, DOCX, DOC files allowed'], 400);
        }

        $title = !empty($_POST['title']) ? trim($_POST['title']) : $this->detectTitle($file['name']);
        $slug = $this->slugify(!empty($_POST['slug']) ? $_POST['slug'] : $title);

        // Ensure unique slug
        $books = $this->loadBooks();
        $existingSlugs = array_column($books, 'slug');
        $baseSlug = $slug;
        $counter = 1;
        while (in_array($slug, $existingSlugs)) {
            $slug = $baseSlug . '-' . $counter++;
        }

        // Save to temp location
        $tmpPath = $this->tmpDir() . '/' . uniqid('book_') . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $tmpPath)) {
            $this->json(['success' => false, 'error' => 'Could not save uploaded file'], 500);
        }

        $outDir = $this->assetsDir() . '/' . $slug;

        // Convert using PHP-based converter (no shell_exec needed)
        $converter = new \App\Services\BookConverter($tmpPath, $slug, $outDir, $title);
        $result = $converter->convert();

        if (!$result || !($result['success'] ?? false)) {
            // Clean up temp and partial output dir
            @unlink($tmpPath);
            $this->rrmdir($outDir);
            $this->json(['success' => false, 'error' => $result['error'] ?? 'Conversion failed'], 500);
        }

        // Save original PDF to book directory for future reprocess
        if ($ext === 'pdf') {
            $origFilename = basename($file['name']);
            rename($tmpPath, $outDir . '/' . $origFilename);
        } else {
            @unlink($tmpPath);
        }

        // Save cover if provided
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $coverExt = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
            if (in_array($coverExt, ['png', 'jpg', 'jpeg'])) {
                foreach (['png', 'jpg', 'jpeg'] as $e) {
                    $existing = $outDir . '/cover.' . $e;
                    if (file_exists($existing)) @unlink($existing);
                }
                $dest = $outDir . '/cover.' . $coverExt;
                move_uploaded_file($_FILES['cover']['tmp_name'], $dest);
            }
        }

        // Update books.json
        $entry = [
            'slug' => $slug,
            'file' => '',
            'year' => intval($_POST['year'] ?? date('Y')),
            'totalPages' => $result['totalPages'],
            'type' => $result['type'],
        ];
        $books[] = $entry;
        $this->saveBooks($books);

        $this->json([
            'success' => true,
            'book' => [
                'slug' => $slug,
                'title' => $title,
                'type' => $result['type'],
                'totalPages' => $result['totalPages'],
            ]
        ]);
    }

    public function update()
    {
        $slug = $_POST['slug'] ?? '';
        if (!$slug) {
            $this->json(['success' => false, 'error' => 'Missing slug'], 400);
        }

        $books = $this->loadBooks();
        $found = false;
        foreach ($books as &$b) {
            if ($b['slug'] === $slug) {
                if (isset($_POST['title'])) {
                    $b['title'] = trim($_POST['title']);
                }
                if (isset($_POST['year'])) {
                    $b['year'] = intval($_POST['year']);
                }
                $found = true;
                break;
            }
        }
        unset($b);

        if (!$found) {
            $this->json(['success' => false, 'error' => 'Book not found'], 404);
        }

        $this->saveBooks($books);

        // Also update book.json inside the book dir
        $infoPath = $this->assetsDir() . '/' . $slug . '/book.json';
        if (file_exists($infoPath)) {
            $info = json_decode(file_get_contents($infoPath), true) ?: [];
            if (isset($_POST['title'])) $info['title'] = trim($_POST['title']);
            if (isset($_POST['year'])) $info['year'] = intval($_POST['year']);
            file_put_contents($infoPath, json_encode($info, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        $this->json(['success' => true]);
    }

    public function destroy()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $slug = $input['slug'] ?? ($_POST['slug'] ?? '');
        if (!$slug) {
            $this->json(['success' => false, 'error' => 'Missing slug'], 400);
        }

        // Remove from books.json
        $books = $this->loadBooks();
        $filtered = array_values(array_filter($books, function ($b) use ($slug) {
            return $b['slug'] !== $slug;
        }));

        if (count($filtered) === count($books)) {
            $this->json(['success' => false, 'error' => 'Book not found'], 404);
        }

        $this->saveBooks($filtered);

        // Delete book directory
        $bookDir = $this->assetsDir() . '/' . $slug;
        $this->rrmdir($bookDir);

        $this->json(['success' => true]);
    }

    public function uploadCover()
    {
        $slug = $_POST['slug'] ?? '';
        if (!$slug || !isset($_FILES['cover']) || $_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'error' => 'Invalid request'], 400);
        }

        $bookDir = $this->assetsDir() . '/' . $slug;
        if (!is_dir($bookDir)) {
            $this->json(['success' => false, 'error' => 'Book not found'], 404);
        }

        $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['png', 'jpg', 'jpeg'])) {
            $this->json(['success' => false, 'error' => 'Only PNG, JPG, JPEG allowed'], 400);
        }

        // Delete existing covers
        foreach (['png', 'jpg', 'jpeg'] as $e) {
            $existing = $bookDir . '/cover.' . $e;
            if (file_exists($existing)) @unlink($existing);
        }

        $dest = $bookDir . '/cover.' . $ext;
        if (!move_uploaded_file($_FILES['cover']['tmp_name'], $dest)) {
            $this->json(['success' => false, 'error' => 'Could not save cover'], 500);
        }

        $this->json([
            'success' => true,
            'coverUrl' => url('/assets/' . $slug . '/cover.' . $ext)
        ]);
    }

    public function reprocess()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $slug = $input['slug'] ?? ($_POST['slug'] ?? '');
        if (!$slug) {
            $this->json(['success' => false, 'error' => 'Missing slug'], 400);
        }

        $books = $this->loadBooks();
        $entry = null;
        foreach ($books as &$b) {
            if ($b['slug'] === $slug) {
                $entry = &$b;
                break;
            }
        }

        if (!$entry) {
            $this->json(['success' => false, 'error' => 'Book not found'], 404);
        }

        // Re-generate pages.json and index.json from existing files if possible
        // For PDF: regenerate from stored PDF in the book directory
        $bookDir = $this->assetsDir() . '/' . $slug;
        $pdfFile = null;
        foreach (['pdf', 'PDF'] as $ext) {
            $candidates = glob($bookDir . '/*.' . $ext);
            if (!empty($candidates)) {
                $pdfFile = $candidates[0];
                break;
            }
        }

        if ($pdfFile && $entry['type'] === 'pdf') {
            $title = $entry['title'] ?? $slug;
            $converter = new \App\Services\BookConverter($pdfFile, $slug, $bookDir, $title);
            $result = $converter->convert();

            if ($result && ($result['success'] ?? false)) {
                $entry['totalPages'] = $result['totalPages'];
                $this->saveBooks($books);
                $this->json(['success' => true, 'totalPages' => $result['totalPages']]);
            } else {
                $this->json(['success' => false, 'error' => $result['error'] ?? 'Reprocess failed'], 500);
            }
        } else {
            $this->json(['success' => false, 'error' => 'No source file to reprocess'], 400);
        }
    }

    public function apiBooks()
    {
        $books = array_reverse($this->loadBooks());
        $assetsDir = $this->assetsDir();
        foreach ($books as &$b) {
            $slug = $b['slug'];
            $bookDir = $assetsDir . '/' . $slug;
            $infoPath = $bookDir . '/book.json';
            $b['title'] = $b['slug'];
            if (file_exists($infoPath)) {
                $meta = json_decode(file_get_contents($infoPath), true);
                $b['title'] = $meta['title'] ?? $b['title'];
            }
            $b['coverUrl'] = '';
            foreach (['png', 'jpg', 'jpeg'] as $ext) {
                if (file_exists($bookDir . '/cover.' . $ext)) {
                    $b['coverUrl'] = url('/assets/' . $slug . '/cover.' . $ext);
                    break;
                }
            }
        }
        $this->json(['books' => $books]);
    }

    private function rrmdir($dir)
    {
        if (!is_dir($dir)) return;
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->rrmdir($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
