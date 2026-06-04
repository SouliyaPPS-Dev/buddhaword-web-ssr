<?php
namespace App\Controllers;

use App\Services\TtsService;

class TtsController {
    private $supportedLangs = ['lo-LA', 'th-TH', 'en-US'];

    public function synthesize() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $text = trim($input['text'] ?? $_POST['text'] ?? '');
            $lang = trim($input['lang'] ?? $_POST['lang'] ?? 'lo-LA');

            if (!strlen($text)) {
                return $this->json(['error' => 'No text provided']);
            }

            if (!in_array($lang, $this->supportedLangs)) {
                $lang = 'lo-LA';
            }

            if (ini_get('max_execution_time') > 0 && ini_get('max_execution_time') < 60) {
                set_time_limit(60);
            }

            $service = new TtsService();
            $result = $service->synthesize($text, $lang);

            $this->json($result);
        } catch (\Throwable $e) {
            $this->json(['error' => 'TTS error: ' . $e->getMessage()]);
        }
    }

    public function check() {
        header('Content-Type: text/html; charset=utf-8');

        $checks = [];

        $checks[] = '<h3>PHP Info</h3>';
        $checks[] = 'PHP Version: ' . PHP_VERSION . '<br>';
        $checks[] = 'Server: ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . '<br>';
        $checks[] = 'Host: ' . ($_SERVER['HTTP_HOST'] ?? 'unknown') . '<br>';

        $checks[] = '<h3>Required Extensions</h3>';
        foreach (['json', 'mbstring', 'sockets', 'curl'] as $ext) {
            $ok = extension_loaded($ext);
            $checks[] = ($ok ? '✅' : '❌') . " {$ext}<br>";
        }

        $checks[] = '<h3>Composer Vendor Check</h3>';
        $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
        $checks[] = 'autoload.php exists: ' . (file_exists($autoloadPath) ? '✅ yes' : '❌ no') . '<br>';

        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
            $checks[] = 'EdgeTTS class: ' . (class_exists('Afaya\EdgeTTS\Service\EdgeTTS') ? '✅ found' : '❌ not found') . '<br>';
        }

        $checks[] = '<h3>Outbound Connection Tests</h3>';
        $host = 'speech.platform.bing.com';
        $port = 443;
        $conn = @fsockopen($host, $port, $errno, $errstr, 5);
        if ($conn) {
            $checks[] = "✅ TCP {$host}:{$port} — connected<br>";
            fclose($conn);
        } else {
            $checks[] = "❌ TCP {$host}:{$port} — {$errstr} ({$errno})<br>";
        }

        // Test TLS connection with stream context (like EdgeTTS uses)
        $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $tls = @stream_socket_client(
            'tls://' . $host . ':443', $errno, $errstr, 10,
            STREAM_CLIENT_CONNECT, $ctx
        );
        if ($tls) {
            $checks[] = '✅ TLS ' . $host . ':443 — connected (stream_socket_client)<br>';
            fclose($tls);
        } else {
            $checks[] = '❌ TLS ' . $host . ':443 — ' . $errstr . ' (' . $errno . ')<br>';
        }

        $checks[] = '<h3>Outbound HTTP Test (Google TTS English)</h3>';
        $gtUrl = 'https://translate.googleapis.com/translate_tts?ie=UTF-8&q=hello&tl=en&client=tw-ob';
        if (function_exists('curl_version')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $gtUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0',
            ]);
            $gtAudio = curl_exec($ch);
            $gtCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $gtErr = curl_error($ch);
            curl_close($ch);
            $checks[] = "cURL HTTP status: {$gtCode}<br>";
            if ($gtErr) $checks[] = "cURL error: " . htmlspecialchars($gtErr) . "<br>";
            $checks[] = 'Response size: ' . strlen($gtAudio) . ' bytes<br>';
        } elseif (ini_get('allow_url_fopen')) {
            $gtAudio = @file_get_contents($gtUrl);
            $checks[] = 'file_get_contents size: ' . strlen($gtAudio) . ' bytes<br>';
        } else {
            $checks[] = '⚠️ Neither cURL nor allow_url_fopen available<br>';
        }

        $checks[] = '<h3>Outbound HTTP Test (Google TTS Lao tl=lo)</h3>';
        $checks[] = 'Testing tl=lo with Lao text...<br>';
        $loUrl = 'https://translate.googleapis.com/translate_tts?ie=UTF-8&q=ພຸດທະວັນນະ&tl=lo&client=tw-ob';
        if (function_exists('curl_version')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $loUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                CURLOPT_REFERER => 'https://translate.google.com/',
            ]);
            $loAudio = curl_exec($ch);
            $loCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $loErr = curl_error($ch);
            curl_close($ch);
            $checks[] = "cURL HTTP status: {$loCode}<br>";
            if ($loErr) $checks[] = "cURL error: " . htmlspecialchars($loErr) . "<br>";
            $checks[] = 'Response size: ' . strlen($loAudio) . ' bytes<br>';
            if ($loCode === 200 && strlen($loAudio) >= 100) {
                $checks[] = '✅ tl=lo works on this server<br>';
            } else {
                $checks[] = '❌ tl=lo FAILED, will fall back to tl=th<br>';
                $checks[] = 'Testing tl=th as fallback...<br>';
                $thUrl = 'https://translate.googleapis.com/translate_tts?ie=UTF-8&q=ພຸດທະວັນນະ&tl=th&client=tw-ob';
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $thUrl,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_USERAGENT => 'Mozilla/5.0',
                ]);
                $thAudio = curl_exec($ch);
                $thCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $thErr = curl_error($ch);
                curl_close($ch);
                $checks[] = "tl=th HTTP status: {$thCode}<br>";
                if ($thErr) $checks[] = "tl=th error: " . htmlspecialchars($thErr) . "<br>";
                $checks[] = 'tl=th response size: ' . strlen($thAudio) . ' bytes<br>';
            }
        }

        $checks[] = '<h3>FreeTTS API Test (Lao voice lo-LA-KeomanyNeural)</h3>';
        $ftUrl = 'https://freetts.org/api/tts';
        if (function_exists('curl_version')) {
            $payload = json_encode([
                'text' => 'ພຸດທະວັນນະ', 'voice' => 'lo-LA-KeomanyNeural',
                'rate' => '+0%', 'pitch' => '+0Hz',
            ]);
            $ch = curl_init($ftUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Origin: https://freetts.org',
                    'Referer: https://freetts.org/',
                ],
            ]);
            $ftResp = curl_exec($ch);
            $ftCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $ftErr = curl_error($ch);
            curl_close($ch);
            $checks[] = "POST HTTP status: {$ftCode}<br>";
            if ($ftErr) $checks[] = "cURL error: " . htmlspecialchars($ftErr) . "<br>";
            $ftData = json_decode($ftResp, true);
            if ($ftCode === 200 && isset($ftData['file_id'])) {
                $checks[] = '✅ FreeTTS Lao voice WORKS (file_id: ' . htmlspecialchars($ftData['file_id']) . ')<br>';

                // Try downloading the audio
                $audio = @file_get_contents('https://freetts.org/api/audio/' . $ftData['file_id']);
                if ($audio !== false && strlen($audio) >= 100) {
                    $checks[] = '✅ Audio download: ' . strlen($audio) . ' bytes<br>';
                } else {
                    $checks[] = '❌ Audio download failed<br>';
                }
            } else {
                $checks[] = '❌ FreeTTS FAILED: ' . htmlspecialchars(substr($ftResp, 0, 200)) . '<br>';
            }
        }

        $checks[] = '<h3>TTS Service Test (Lao synthesize)</h3>';
        if (file_exists($autoloadPath)) {
            try {
                $service = new TtsService();
                $result = $service->synthesize('ພຸດທະວັນນະ ແມ່ນຄໍາສັບ', 'lo-LA');
                if (isset($result['error'])) {
                    $checks[] = '❌ Error: ' . htmlspecialchars($result['message']) . '<br>';
                } else {
                    $audioLen = strlen(base64_decode($result['audioContent']));
                    $checks[] = '✅ Audio generated: ' . $audioLen . ' bytes, ' . count($result['timepoints']) . ' word boundaries<br>';
                }
            } catch (\Throwable $e) {
                $checks[] = '❌ Exception: ' . htmlspecialchars($e->getMessage()) . '<br>';
            }
        } else {
            $checks[] = '⚠️ Skipped (autoload not found)<br>';
        }

        echo '<html><body style="font-family:sans-serif;padding:20px;font-size:14px">';
        echo '<h2>TTS Diagnostics</h2>';
        echo implode("\n", $checks);
        echo '</body></html>';
    }

    private function json($data) {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
