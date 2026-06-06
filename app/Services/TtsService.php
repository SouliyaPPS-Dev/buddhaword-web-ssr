<?php
namespace App\Services;

use Afaya\EdgeTTS\Service\EdgeTTS;

class TtsService {
    private $voiceMap = [
        'lo-LA' => 'lo-LA-KeomanyNeural', 
        'th-TH' => 'th-TH-NiwatNeural',
        'en-US' => 'en-US-GuyNeural',
    ];

    private $googleLangMap = [
        'lo-LA' => 'lo',
        'th-TH' => 'th',
        'en-US' => 'en',
    ];

    private $voiceRssLangMap = [
        'th-TH' => 'th-th',
        'en-US' => 'en-us',
        'lo-LA' => 'lo-la'
    ];

    private $maxChars = 10000;
    private $cacheEnabled = true;
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

    public function __construct() {
        if ($this->cacheEnabled) {
            $this->initCacheTable();
        }
    }

    public function synthesize($text, $languageCode = 'lo-LA') {
        $voice = $this->voiceMap[$languageCode] ?? 'en-US-AriaNeural';
        $text = $this->normalizeText($text, $languageCode);

        if (mb_strlen($text, 'UTF-8') > $this->maxChars) {
            $text = mb_substr($text, 0, $this->maxChars, 'UTF-8');
        }

        if ($this->cacheEnabled) {
            $cached = $this->getFromCache($text, $languageCode);
            if ($cached !== null) return $cached;
        }

        // 0. Pre-generated files in storage/tts/ (works on production after upload)
        $fileResult = $this->synthesizeFromFile($text, $languageCode);
        if (!isset($fileResult['error'])) {
            $this->saveToCache($text, $languageCode, $fileResult);
            return $fileResult;
        }

        // 1. Native Edge TTS (Localhost with sockets)
        if (extension_loaded('sockets') && class_exists(EdgeTTS::class)) {
            try {
                $result = $this->synthesizeEdgeTTS($text, $voice);
                if (!isset($result['error'])) {
                    $this->saveAudioToFile($text, $languageCode, $result['audioContent'], $result['timepoints'] ?? []);
                    $this->saveToCache($text, $languageCode, $result);
                    return $result;
                }
            } catch (\Throwable $e) {}
        }

        // 2. HTTP Methods (Fallbacks for Production or if Edge fails)
        $result = $this->synthesizeHttp($text, $voice, $languageCode);
        if (!isset($result['error'])) {
            $this->saveAudioToFile($text, $languageCode, $result['audioContent'], $result['timepoints'] ?? []);
            $this->saveToCache($text, $languageCode, $result);
            return $result;
        }

        // 3. TtsLibrary (Local pre-generated)
        $result = $this->synthesizeLibrary($text, $languageCode);
        if (!isset($result['error'])) {
            $this->saveToCache($text, $languageCode, $result);
            return $result;
        }

        return $result;
    }

    private function saveAudioToFile($text, $languageCode, $audioContent, $timepoints = []) {
        $hash = md5($text . '|' . $languageCode);
        $dir = __DIR__ . '/../../storage/tts';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        $mp3File = $dir . '/' . $hash . '.mp3';
        $metaFile = $dir . '/' . $hash . '.json';

        if (!file_exists($mp3File)) {
            $decoded = base64_decode($audioContent);
            if ($decoded !== false && strlen($decoded) > 100) {
                file_put_contents($mp3File, $decoded);
            }
        }

        if (!file_exists($metaFile)) {
            $words = preg_split('/\s+/u', $text);
            $totalChars = 0;
            if (empty($timepoints)) {
                foreach ($words as $word) {
                    $timepoints[] = ['markName' => $word, 'timeSeconds' => round($totalChars / 4.5, 3)];
                    $totalChars += mb_strlen($word) + 1;
                }
            }
            file_put_contents($metaFile, json_encode([
                'text' => $text,
                'language' => $languageCode,
                'timepoints' => $timepoints,
            ], JSON_UNESCAPED_UNICODE));
        }
    }

    private function synthesizeFromFile($text, $languageCode) {
        $hash = md5($text . '|' . $languageCode);
        $dir = __DIR__ . '/../../storage/tts';
        $mp3File = $dir . '/' . $hash . '.mp3';
        $metaFile = $dir . '/' . $hash . '.json';

        if (!file_exists($mp3File)) {
            return ['error' => true, 'message' => 'No pre-generated file found'];
        }

        $meta = [];
        if (file_exists($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true) ?: [];
        }

        return [
            'audioContent' => base64_encode(file_get_contents($mp3File)),
            'timepoints' => $meta['timepoints'] ?? $this->generateTimepoints($text),
            'cached' => true,
        ];
    }

    private function synthesizeHttp($text, $voice, $languageCode) {
        $errors = [];
        
        // A. Google Translate TTS (Primary Lao fallback)
        $result = $this->attemptTts($text, $languageCode);
        if (!isset($result['error'])) return $result;
        $errors[] = 'GoogleTTS: ' . $result['message'];

        // B. FreeTTS
        $result = $this->attemptFreeTts($text, $voice);
        if (!isset($result['error'])) return $result;
        $errors[] = 'FreeTTS: ' . $result['message'];

        // C. Last Resort: Thai voice for Lao text (only if everything else fails)
        if ($languageCode === 'lo-LA') {
            $result = $this->attemptTts($text, 'th-TH');
            if (!isset($result['error'])) return $result;
            $errors[] = 'ThaiFallback: ' . $result['message'];
        }

        return [
            'error' => true, 
            'message' => 'All TTS methods failed for ' . $languageCode . '. ' . implode(' | ', $errors),
            'debug_info' => [
                'sockets' => extension_loaded('sockets'),
                'open_basedir' => ini_get('open_basedir')
            ]
        ];
    }

    private function attemptTts($text, $langCode) {
        $googleLang = $this->googleLangMap[$langCode] ?? ($langCode === 'th-TH' ? 'th' : 'en');
        // Very small chunks for Lao to avoid 400 errors
        $chunks = $this->splitText($text, 100); 
        $allAudio = '';

        foreach ($chunks as $chunk) {
            $audio = $this->fetchGoogleTts($chunk, $googleLang);
            if (!$audio || is_array($audio)) break;
            $allAudio .= $audio;
        }

        if (strlen($allAudio) < 100) return ['error' => true, 'message' => 'Fetch failed'];
        return ['audioContent' => base64_encode($allAudio), 'timepoints' => $this->generateTimepoints($text)];
    }

    private function fetchGoogleTts($chunk, $lang) {
        $quoted = urlencode($chunk);
        // Best clients for Lao: 'gtx', 't'
        $clients = ['gtx', 't', 'tw-ob', 'webapp'];
        $hosts = ['translate.googleapis.com', 'translate.google.com', 'translate.google.com.la'];
        
        foreach ($hosts as $host) {
            foreach ($clients as $client) {
                $url = "https://{$host}/translate_tts?ie=UTF-8&q={$quoted}&tl={$lang}&client={$client}&total=1&idx=0&textlen=" . strlen($chunk);
                $res = $this->httpGet($url, 10);
                if ($res && !is_array($res) && strlen($res) > 100) {
                    if (ord($res[0]) === 0x3C || stripos($res, 'cookies are not enabled') !== false) continue;
                    return $res;
                }
            }
        }
        return null;
    }

    private function attemptFreeTts($text, $voice) {
        $chunks = $this->splitText($text, 150);
        $allAudio = '';
        foreach (['https://freetts.org', 'https://tts.monster'] as $baseUrl) {
            $allAudio = '';
            foreach ($chunks as $chunk) {
                $payload = json_encode(['text' => $chunk, 'voice' => $voice, 'rate' => '+0%', 'pitch' => '+0Hz']);
                $ch = curl_init($baseUrl . '/api/tts');
                curl_setopt_array($ch, [
                    CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 12, CURLOPT_CONNECTTIMEOUT => 6, CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'User-Agent: ' . $this->userAgent, 'Origin: ' . $baseUrl, 'Referer: ' . $baseUrl . '/']
                ]);
                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($code === 402) return ['error' => true, 'message' => 'Limit Reached'];
                if ($code !== 200) break;
                $data = json_decode($resp, true);
                if (!isset($data['file_id'])) break;
                $audio = $this->httpGet($baseUrl . '/api/audio/' . $data['file_id'], 12);
                if (!$audio || is_array($audio)) break;
                $allAudio .= $audio;
            }
            if (strlen($allAudio) > 100) break;
        }
        if (strlen($allAudio) < 100) return ['error' => true, 'message' => 'No audio'];
        return ['audioContent' => base64_encode($allAudio), 'timepoints' => $this->generateTimepoints($text)];
    }

    private function attemptVoiceRss($text, $languageCode) {
        $apiKey = getenv('VOICERSS_API_KEY');
        if (!$apiKey) return ['error' => true, 'message' => 'No key'];
        $lang = $this->voiceRssLangMap[$languageCode] ?? 'en-us';
        $chunks = $this->splitText($text, 180);
        $allAudio = '';
        foreach ($chunks as $chunk) {
            $url = 'https://api.voicerss.org/?key=' . urlencode($apiKey) . '&hl=' . $lang . '&src=' . urlencode($chunk) . '&c=MP3&f=44khz_16bit_stereo';
            $audio = $this->httpGet($url, 15);
            if (!$audio || is_array($audio)) break;
            $allAudio .= $audio;
        }
        if (strlen($allAudio) < 100) return ['error' => true, 'message' => 'Fetch failed'];
        return ['audioContent' => base64_encode($allAudio), 'timepoints' => $this->generateTimepoints($text)];
    }

    private function httpGet($url, $timeout = 15, $maxRedirects = 3) {
        if ($maxRedirects < 0) return null;
        if (function_exists('curl_version')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_USERAGENT => $this->userAgent,
                CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, CURLOPT_HEADER => true,
                CURLOPT_FOLLOWLOCATION => !ini_get('open_basedir')
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            curl_close($ch);
            if ($resp !== false) {
                $head = substr($resp, 0, $headSize);
                $body = substr($resp, $headSize);
                if ($code >= 300 && $code < 400 && preg_match('/location:\s*([^\r\n]+)/i', $head, $m)) {
                    $newUrl = trim($m[1]);
                    if (strpos($newUrl, 'http') !== 0) {
                        $parts = parse_url($url);
                        $newUrl = $parts['scheme'] . '://' . $parts['host'] . $newUrl;
                    }
                    return $this->httpGet($newUrl, $timeout, $maxRedirects - 1);
                }
                if ($code === 200 && strlen($body) > 100) return $body;
            }
        }
        if (ini_get('allow_url_fopen')) {
            $ctx = stream_context_create(['http' => ['timeout' => $timeout, 'user_agent' => $this->userAgent, 'follow_location' => 0]]);
            $res = @file_get_contents($url, false, $ctx);
            if (isset($http_response_header)) {
                foreach ($http_response_header as $h) {
                    if (preg_match('/^Location:\s*(.*)$/i', $h, $m)) {
                        $newUrl = trim($m[1]);
                        if (strpos($newUrl, 'http') !== 0) {
                            $parts = parse_url($url);
                            $newUrl = $parts['scheme'] . '://' . $parts['host'] . $newUrl;
                        }
                        return $this->httpGet($newUrl, $timeout, $maxRedirects - 1);
                    }
                }
            }
            if ($res !== false && strlen($res) > 100) return $res;
        }
        return null;
    }

    private function synthesizeEdgeTTS($text, $voice) {
        $tts = new EdgeTTS();
        $tts->synthesizeStream($text, $voice, ['rate' => '-10%', 'volume' => '0%', 'pitch' => '+0Hz']);
        $audio = $tts->toBase64();
        if (empty($audio)) return ['error' => true, 'message' => 'No audio'];
        $tps = [];
        foreach ($tts->getWordBoundaries() as $b) {
            $tps[] = ['markName' => $b['text'], 'timeSeconds' => round($b['offset'] / 10000000, 3)];
        }
        return ['audioContent' => $audio, 'timepoints' => $tps];
    }

    private function synthesizeLibrary($text, $languageCode) {
        try {
            $lib = new \App\Services\TtsLibrary();
            return $lib->synthesize($text, $languageCode);
        } catch (\Throwable $e) { return ['error' => true, 'message' => $e->getMessage()]; }
    }

    private function normalizeText($text, $lang) {
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);
        return trim($text);
    }

    private function splitText($text, $maxLen) {
        $chunks = []; $rem = trim($text);
        while (mb_strlen($rem, 'UTF-8') > 0) {
            if (mb_strlen($rem, 'UTF-8') <= $maxLen) { $chunks[] = $rem; break; }
            $chunk = mb_substr($rem, 0, $maxLen, 'UTF-8');
            $ls = mb_strrpos($chunk, ' ', 0, 'UTF-8');
            if ($ls === false || $ls === 0) {
                foreach (['ກໍ', 'ທີ່', 'ແລະ', 'ໃນ', 'ຂອງ', '।', '!', '?', '.', ','] as $sep) {
                    $lsep = mb_strrpos($chunk, $sep, 0, 'UTF-8');
                    if ($lsep !== false && $lsep > ($maxLen * 0.5)) {
                        $chunk = mb_substr($chunk, 0, $lsep + mb_strlen($sep, 'UTF-8'), 'UTF-8');
                        break;
                    }
                }
            } else { $chunk = mb_substr($chunk, 0, $ls, 'UTF-8'); }
            $chunks[] = $chunk;
            $rem = mb_substr($rem, mb_strlen($chunk, 'UTF-8'), null, 'UTF-8');
        }
        return $chunks;
    }

    private function generateTimepoints($text) {
        $tps = []; $tc = 0;
        foreach (preg_split('/\s+/u', $text) as $w) {
            $tps[] = ['markName' => $w, 'timeSeconds' => round($tc / 4.5, 3)];
            $tc += mb_strlen($w, 'UTF-8') + 1;
        }
        return $tps;
    }

    private function getFromCache($text, $lang) {
        try {
            $db = \App\Core\Database::getInstance();
            $hash = md5($text . '|' . $lang);
            $stmt = $db->prepare("SELECT audio_content, timepoints FROM tts_cache WHERE text_hash = ? AND language = ? AND expires_at > NOW()");
            $stmt->execute([$hash, $lang]);
            $row = $stmt->fetch();
            if ($row) {
                $db->prepare("UPDATE tts_cache SET accessed_count = accessed_count + 1, last_accessed = NOW() WHERE text_hash = ? AND language = ?")->execute([$hash, $lang]);
                return ['audioContent' => $row['audio_content'], 'timepoints' => json_decode($row['timepoints'], true) ?? []];
            }
        } catch (\Throwable $e) { $this->cacheEnabled = false; }
        return null;
    }

    private function saveToCache($text, $lang, $result) {
        try {
            $db = \App\Core\Database::getInstance();
            $hash = md5($text . '|' . $lang);
            $audio = $result['audioContent'] ?? '';
            $tps = json_encode($result['timepoints'] ?? [], JSON_UNESCAPED_UNICODE);
            $stmt = $db->prepare("INSERT INTO tts_cache (text_hash, text_content, language, audio_content, timepoints, expires_at) VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY)) ON DUPLICATE KEY UPDATE audio_content = VALUES(audio_content), timepoints = VALUES(timepoints), expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY), accessed_count = accessed_count + 1");
            $stmt->execute([$hash, $text, $lang, $audio, $tps]);
        } catch (\Throwable $e) { $this->cacheEnabled = false; }
    }

    private function initCacheTable() {
        try {
            $db = \App\Core\Database::getInstance();
            $db->exec("CREATE TABLE IF NOT EXISTS tts_cache (id INT AUTO_INCREMENT PRIMARY KEY, text_hash VARCHAR(64) NOT NULL, text_content TEXT NOT NULL, language VARCHAR(10) NOT NULL, audio_content LONGTEXT NOT NULL, timepoints TEXT DEFAULT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, accessed_count INT DEFAULT 1, expires_at TIMESTAMP NULL DEFAULT NULL, UNIQUE KEY idx_text_hash_lang (text_hash, language), INDEX idx_expires (expires_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Throwable $e) { $this->cacheEnabled = false; }
    }
}
