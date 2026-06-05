<?php
namespace App\Services;

class TtsLibrary {
    private $storageDir;

    private $voiceMap = [
        'lo-LA' => ['voice' => 'lo-LA-KeomanyNeural', 'male' => 'lo-LA-ChanthavongNeural'],
        'th-TH' => ['voice' => 'th-TH-NiwatNeural'],
        'en-US' => ['voice' => 'en-US-GuyNeural'],
    ];

    public function __construct() {
        $this->storageDir = __DIR__ . '/../../storage/tts';
        if (!is_dir($this->storageDir)) {
            @mkdir($this->storageDir, 0755, true);
        }
    }

    public function synthesize($text, $languageCode) {
        if (!isset($this->voiceMap[$languageCode])) {
            return ['error' => true, 'message' => 'Language not supported'];
        }

        $hash = md5($text . '|' . $languageCode);
        $mp3File = $this->storageDir . '/' . $hash . '.mp3';
        $metaFile = $this->storageDir . '/' . $hash . '.json';

        if (file_exists($mp3File) && file_exists($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true);
            return [
                'audioContent' => base64_encode(file_get_contents($mp3File)),
                'timepoints' => $meta['timepoints'] ?? [],
                'cached' => true,
            ];
        }

        $generated = $this->generateWithPython($text, $languageCode, $hash);
        if (!isset($generated['error'])) {
            return $generated;
        }

        return ['error' => true, 'message' => 'Local Lao audio not found'];
    }

    public function generateWithPython($text, $languageCode, $hash = null) {
        if (!isset($this->voiceMap[$languageCode])) {
            return ['error' => true, 'message' => 'Language not supported'];
        }

        if ($hash === null) {
            $hash = md5($text . '|' . $languageCode);
        }

        $voice = $this->voiceMap[$languageCode]['voice'];
        $mp3File = $this->storageDir . '/' . $hash . '.mp3';
        $metaFile = $this->storageDir . '/' . $hash . '.json';

        $which = trim(shell_exec('which edge-tts 2>/dev/null'));
        if (!$which || !is_executable($which)) {
            return ['error' => true, 'message' => 'edge-tts not installed'];
        }

        $textFile = tempnam(sys_get_temp_dir(), 'tts_');
        if ($textFile === false) {
            return ['error' => true, 'message' => 'Cannot create temp file'];
        }
        file_put_contents($textFile, $text);

        $cmd = $which . ' --file ' . escapeshellarg($textFile)
             . ' --voice ' . escapeshellarg($voice)
             . ' --write-media ' . escapeshellarg($mp3File)
             . ' 2>&1';

        $output = shell_exec($cmd);
        @unlink($textFile);

        if (!file_exists($mp3File) || filesize($mp3File) < 100) {
            if (file_exists($mp3File)) @unlink($mp3File);
            return ['error' => true, 'message' => 'Python TTS generation failed'];
        }

        $duration = $this->estimateDuration($mp3File);

        $timepoints = [];
        $totalChars = 0;
        $words = preg_split('/\s+/u', $text);
        foreach ($words as $word) {
            $timepoints[] = ['markName' => $word, 'timeSeconds' => round($totalChars / 4.5, 3)];
            $totalChars += mb_strlen($word) + 1;
        }

        file_put_contents($metaFile, json_encode([
            'text' => $text,
            'language' => $languageCode,
            'voice' => $voice,
            'duration' => $duration,
            'timepoints' => $timepoints,
        ], JSON_UNESCAPED_UNICODE));

        return [
            'audioContent' => base64_encode(file_get_contents($mp3File)),
            'timepoints' => $timepoints,
            'generated' => true,
        ];
    }

    public function generateBatch(array $items, callable $onProgress = null) {
        $results = [];
        $total = count($items);
        foreach ($items as $i => $item) {
            $text = $item['text'] ?? '';
            $lang = $item['lang'] ?? 'lo-LA';
            if (!trim($text)) continue;

            $hash = md5($text . '|' . $lang);
            $mp3File = $this->storageDir . '/' . $hash . '.mp3';

            if (file_exists($mp3File)) {
                $results[] = ['text' => $text, 'lang' => $lang, 'status' => 'skipped'];
                if ($onProgress) $onProgress($i + 1, $total, 'skipped');
                continue;
            }

            $result = $this->generateWithPython($text, $lang, $hash);
            $results[] = [
                'text' => $text,
                'lang' => $lang,
                'status' => isset($result['error']) ? 'failed' : 'generated',
            ];
            if ($onProgress) $onProgress($i + 1, $total, isset($result['error']) ? 'failed' : 'generated');
        }
        return $results;
    }

    public function getStorageDir() {
        return $this->storageDir;
    }

    public function getVoiceList() {
        return $this->voiceMap;
    }

    public function getVoiceForLanguage($languageCode) {
        return $this->voiceMap[$languageCode]['voice'] ?? null;
    }

    public function countFiles() {
        $mp3s = glob($this->storageDir . '/*.mp3');
        return $mp3s ? count($mp3s) : 0;
    }

    public function totalSize() {
        $total = 0;
        foreach (glob($this->storageDir . '/*.mp3') as $f) {
            $total += filesize($f);
        }
        return $total;
    }

    private function estimateDuration($mp3File) {
        $size = filesize($mp3File);
        return round($size / 16000, 2);
    }
}
