<?php
namespace App\Controllers;

use App\Services\TtsService;

class TtsController {
    private $supportedLangs = ['lo-LA', 'th-TH', 'en-US'];

    public function synthesize() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $text = $input['text'] ?? '';
            $lang = $input['language'] ?? 'lo-LA';

            if (empty($text)) {
                $this->json(['error' => true, 'message' => 'Text is required']);
            }

            $service = new TtsService();
            $result = $service->synthesize($text, $lang);
            
            $this->json($result);
        } catch (\Throwable $e) {
            $this->json(['error' => true, 'message' => $e->getMessage()]);
        }
    }

    public function check() {
        $checks = [];
        $checks[] = '<h3>PHP Info</h3>';
        $checks[] = 'PHP Version: ' . PHP_VERSION . '<br>';
        $checks[] = 'Server: ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '<br>';
        $checks[] = 'Host: ' . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . '<br>';

        $checks[] = '<h3>Required Extensions</h3>';
        $checks[] = (extension_loaded('json') ? '✅ json' : '❌ json') . '<br>';
        $checks[] = (extension_loaded('mbstring') ? '✅ mbstring' : '❌ mbstring') . '<br>';
        $checks[] = (extension_loaded('sockets') ? '✅ sockets' : '❌ sockets (Blocked by InfinityFree - using HTTP fallbacks)') . '<br>';
        
        if (!extension_loaded('sockets')) {
            $checks[] = '<div style="background:#fff3cd;padding:10px;border-radius:8px;margin:10px 0;border:1px solid #ffeeba;">';
            $checks[] = '<strong>💡 How to get Sockets support:</strong><br>';
            $checks[] = 'Free shared hosting (InfinityFree, ByetHost) always blocks sockets for security.<br>';
            $checks[] = 'To use native EdgeTTS (WebSockets), consider these free alternatives that allow sockets:<br>';
            $checks[] = '1. <strong>Railway.app</strong> (Fastest setup, supports all PHP extensions)<br>';
            $checks[] = '2. <strong>Koyeb.com</strong> (Free tier for Docker/Node/PHP)<br>';
            $checks[] = '3. <strong>Oracle Cloud Free Tier</strong> (Full VPS instance)<br>';
            $checks[] = '4. <strong>Hugging Face Spaces</strong> (Docker based, very flexible)<br>';
            $checks[] = '</div>';
        }

        $checks[] = (function_exists('stream_socket_client') ? '✅ stream_socket_client' : '❌ stream_socket_client') . '<br>';
        $checks[] = (extension_loaded('curl') ? '✅ curl' : '❌ curl') . '<br>';

        $checks[] = '<h3>Google TTS Lao (tl=lo)</h3>';
        $loText = urlencode('ພຸດທະ');
        $clients = ['t', 'gtx', 'tw-ob'];
        foreach ($clients as $client) {
            $url = "https://translate.google.com/translate_tts?ie=UTF-8&q={$loText}&tl=lo&client={$client}";
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_REFERER => 'https://translate.google.com/',
            ]);
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $checks[] = "Client '{$client}': HTTP {$code} (" . strlen($res) . " bytes)<br>";
        }

        $checks[] = '<h3>FreeTTS Test</h3>';
        $ftUrl = 'https://freetts.org/api/tts';
        $payload = json_encode(['text' => 'ພຸດທະ', 'voice' => 'lo-LA-KeomanyNeural', 'rate' => '+0%', 'pitch' => '+0Hz']);
        $ch = curl_init($ftUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false, CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'User-Agent: Mozilla/5.0']
        ]);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $checks[] = "Status: {$code}<br>";
        if ($code === 200) {
            $data = json_decode($res, true);
            $checks[] = "File ID: " . ($data['file_id'] ?? 'none') . "<br>";
        } else {
            $checks[] = "Error: " . htmlspecialchars(substr($res, 0, 100)) . "<br>";
        }

        $checks[] = '<h3>TTS Service Test (Lao)</h3>';
        try {
            $service = new TtsService();
            $result = $service->synthesize('ພຸດທະ', 'lo-LA');
            if (isset($result['error'])) {
                $checks[] = '❌ Error: ' . htmlspecialchars($result['message']) . '<br>';
                if (isset($result['debug_info'])) $checks[] = 'Debug: ' . json_encode($result['debug_info']) . '<br>';
            } else {
                $audioLen = strlen(base64_decode($result['audioContent']));
                $checks[] = "✅ SUCCESS! Generated {$audioLen} bytes of Lao audio.<br>";
            }
        } catch (\Throwable $e) {
            $checks[] = '❌ Exception: ' . htmlspecialchars($e->getMessage()) . '<br>';
        }

        echo '<html><body style="font-family:sans-serif;padding:20px;font-size:14px">';
        echo '<h2>TTS Diagnostics</h2>';
        echo implode("\n", $checks);
        echo '</body></html>';
    }

    public function play($hash) {
        $dir = realpath(__DIR__ . '/../../storage/tts');
        $mp3File = $dir . '/' . basename($hash) . '.mp3';

        if (!$dir || !file_exists($mp3File)) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Audio not found']);
            exit;
        }

        while (ob_get_level()) ob_end_clean();
        header('Content-Type: audio/mpeg');
        header('Content-Length: ' . filesize($mp3File));
        header('Accept-Ranges: bytes');
        header('Cache-Control: public, max-age=31536000');
        readfile($mp3File);
        exit;
    }

    public function cache() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $text = $input['text'] ?? '';
            $lang = $input['language'] ?? 'lo-LA';
            $force = !empty($input['force']);

            if (empty($text)) {
                $this->json(['error' => true, 'message' => 'Text is required']);
            }

            $dir = __DIR__ . '/../../storage/tts';
            if (!is_dir($dir)) @mkdir($dir, 0755, true);

            $hash = md5($text . '|' . $lang);
            $mp3File = $dir . '/' . $hash . '.mp3';
            $metaFile = $dir . '/' . $hash . '.json';

            if (file_exists($mp3File) && !$force) {
                $meta = file_exists($metaFile) ? json_decode(file_get_contents($metaFile), true) : [];
                $this->json([
                    'cached' => true,
                    'hash' => $hash,
                    'url' => url('/api/tts/play/' . $hash),
                    'timepoints' => $meta['timepoints'] ?? [],
                ]);
            }

            $lib = new \App\Services\TtsLibrary();
            $result = $lib->generateWithPython($text, $lang, $hash);
            if (isset($result['error'])) {
                $this->json($result);
            }

            $this->json([
                'cached' => false,
                'hash' => $hash,
                'url' => url('/api/tts/play/' . $hash),
                'audioContent' => $result['audioContent'],
                'timepoints' => $result['timepoints'] ?? [],
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => true, 'message' => $e->getMessage()]);
        }
    }

    private function json($data) {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
