<?php
namespace App\Services;

use Afaya\EdgeTTS\Service\EdgeTTS;

class TtsService {
    private $voiceMap = [
        'lo-LA' => 'lo-LA-KeomanyNeural',  // no male voice available for Lao
        'th-TH' => 'th-TH-NiwatNeural',
        'en-US' => 'en-US-GuyNeural',
    ];

    private $googleLangMap = [
        'lo-LA' => 'lo',
        'th-TH' => 'th',
        'en-US' => 'en',
    ];

    private $googleLangFallback = [
        'lo-LA' => 'th',
    ];

    private $voiceRssLangMap = [
        'lo-LA' => 'lo-la',
        'th-TH' => 'th-th',
        'en-US' => 'en-us',
    ];

    private $maxChars = 10000;
    private $cacheEnabled = true;

    public function __construct() {
        if ($this->cacheEnabled) {
            $this->initCacheTable();
        }
    }

    public function synthesize($text, $languageCode = 'lo-LA') {
        $voice = $this->voiceMap[$languageCode] ?? 'en-US-AriaNeural';

        if (mb_strlen($text) > $this->maxChars) {
            $text = mb_substr($text, 0, $this->maxChars);
            $last = mb_strrpos($text, '.');
            if ($last > mb_strrpos($text, '?')) $last = mb_strrpos($text, '?');
            if ($last > mb_strrpos($text, '!')) $last = mb_strrpos($text, '!');
            if ($last > 0) $text = mb_substr($text, 0, $last + 1);
        }

        if ($this->cacheEnabled) {
            $cached = $this->getFromCache($text, $languageCode);
            if ($cached !== null) {
                return $cached;
            }
        }

        try {
            if (extension_loaded('sockets') && class_exists(EdgeTTS::class)) {
                $result = $this->synthesizeEdgeTTS($text, $voice);
                if (!isset($result['error'])) {
                    $this->saveToCache($text, $languageCode, $result);
                    return $result;
                }
            }
        } catch (\Throwable $e) {}

        // 2. TtsLibrary — local pre-generated audio (no outbound calls needed)
        $result = $this->synthesizeLibrary($text, $languageCode);
        if (!isset($result['error'])) {
            $this->saveToCache($text, $languageCode, $result);
            return $result;
        }

        // 3. HTTP fallbacks (external APIs)
        $result = $this->synthesizeHttp($text, $voice, $languageCode);
        if (!isset($result['error'])) {
            $this->saveToCache($text, $languageCode, $result);
        }

        return $result;
    }

    private function synthesizeEdgeTTS($text, $voice) {
        try {
            $tts = new EdgeTTS();
            $tts->synthesizeStream($text, $voice, [
                'rate' => '-10%',
                'volume' => '0%',
                'pitch' => '+0Hz',
            ]);

            $audioContent = $tts->toBase64();
            $boundaries = $tts->getWordBoundaries();

            if (empty($audioContent)) {
                return ['error' => true, 'message' => 'No audio generated'];
            }

            $timepoints = [];
            foreach ($boundaries as $b) {
                $timepoints[] = [
                    'markName' => $b['text'],
                    'timeSeconds' => round($b['offset'] / 10000000, 3),
                ];
            }

            return [
                'audioContent' => $audioContent,
                'timepoints' => $timepoints,
            ];
        } catch (\Throwable $e) {
            return ['error' => true, 'message' => 'Edge TTS error: ' . $e->getMessage()];
        }
    }

    private function synthesizeLibrary($text, $languageCode) {
        try {
            $lib = new \App\Services\TtsLibrary();
            return $lib->synthesize($text, $languageCode);
        } catch (\Throwable $e) {
            return ['error' => true, 'message' => 'Library TTS error: ' . $e->getMessage()];
        }
    }

    private function synthesizeHttp($text, $voice, $languageCode) {
        // 1. FreeTTS API — Microsoft Edge voices via plain HTTP (tries multiple endpoints)
        $result = $this->attemptFreeTts($text, $voice);
        if (!isset($result['error'])) {
            return $result;
        }

        // 2. Voice RSS TTS (supports Lao lo-la with proper Lao neural voice)
        $result = $this->attemptVoiceRss($text, $languageCode);
        if (!isset($result['error'])) {
            return $result;
        }

        // For Lao: Google TTS (tl=lo) has no real Lao voice — it uses Thai voice model,
        // producing Thai-accented speech. Skip Google TTS entirely for Lao.
        if ($languageCode === 'lo-LA') {
            return ['error' => true, 'message' => 'Lao voice unavailable'];
        }

        // 3. Google Translate TTS (reliable on shared hosting)
        $googleLang = $this->googleLangMap[$languageCode] ?? 'en';
        $result = $this->attemptTts($text, $googleLang);
        if (!isset($result['error'])) {
            return $result;
        }

        // 4. Google Translate TTS with fallback language
        if (isset($this->googleLangFallback[$languageCode])) {
            $fallbackLang = $this->googleLangFallback[$languageCode];
            if ($fallbackLang !== $googleLang) {
                $result = $this->attemptTts($text, $fallbackLang);
                if (!isset($result['error'])) {
                    return $result;
                }
            }
        }

        return ['fallback' => true];
    }

    private function attemptFreeTts($text, $voice) {
        $endpoints = [
            'https://freetts.org',
            'https://tts.monster',
        ];

        foreach ($endpoints as $baseUrl) {
            $chunks = $this->splitText($text, 170);
            $allAudio = '';

            $headers = [
                'Content-Type: application/json',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Origin: ' . $baseUrl,
                'Referer: ' . $baseUrl . '/',
            ];

            foreach ($chunks as $chunk) {
                $payload = json_encode([
                    'text' => $chunk, 'voice' => $voice,
                    'rate' => '+0%', 'pitch' => '+0Hz',
                ]);

                $ch = curl_init($baseUrl . '/api/tts');
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ]);
                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($code !== 200) continue;

                $data = json_decode($resp, true);
                if (!isset($data['file_id'])) continue;

                $audioUrl = $baseUrl . '/api/audio/' . $data['file_id'];
                $audio = @file_get_contents($audioUrl);
                if ($audio === false || strlen($audio) < 100) {
                    $audio = @file_get_contents($audioUrl, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
                }
                if ($audio === false || strlen($audio) < 100) {
                    $audio = $this->httpGet($audioUrl, 20);
                }
                if ($audio === null || strlen($audio) < 100) continue;

                $allAudio .= $audio;
            }

            if (!empty($allAudio)) {
                $allTimepoints = [];
                $totalChars = 0;
                foreach (preg_split('/\s+/u', $text) as $word) {
                    $allTimepoints[] = ['markName' => $word, 'timeSeconds' => round($totalChars / 4.5, 3)];
                    $totalChars += mb_strlen($word) + 1;
                }

                return ['audioContent' => base64_encode($allAudio), 'timepoints' => $allTimepoints];
            }
        }

        return ['error' => true, 'message' => 'No audio generated from FreeTTS endpoints'];
    }

    private function attemptVoiceRss($text, $languageCode) {
        $voiceRssLang = $this->voiceRssLangMap[$languageCode] ?? null;
        if (!$voiceRssLang) {
            return ['error' => true, 'message' => 'Voice RSS not supported for this language'];
        }

        $apiKey = getenv('VOICERSS_API_KEY');
        if (!$apiKey) {
            return ['error' => true, 'message' => 'Voice RSS API key not configured'];
        }

        $chunks = $this->splitText($text, 180);
        $allAudio = '';

        foreach ($chunks as $chunk) {
            $url = 'https://api.voicerss.org/?key=' . urlencode($apiKey)
                 . '&hl=' . urlencode($voiceRssLang)
                 . '&src=' . urlencode($chunk)
                 . '&c=MP3&f=44khz_16bit_stereo';

            $audio = $this->httpGet($url, 30);
            if ($audio === null || strlen($audio) < 100 || strpos($audio, 'Error!') === 0) {
                if (empty($allAudio)) {
                    return ['error' => true, 'message' => 'Voice RSS request failed'];
                }
                break;
            }
            $allAudio .= $audio;
        }

        if (empty($allAudio)) {
            return ['error' => true, 'message' => 'No audio generated'];
        }

        $allTimepoints = [];
        $totalChars = 0;
        foreach (preg_split('/\s+/u', $text) as $word) {
            $allTimepoints[] = ['markName' => $word, 'timeSeconds' => round($totalChars / 4.5, 3)];
            $totalChars += mb_strlen($word) + 1;
        }

        return ['audioContent' => base64_encode($allAudio), 'timepoints' => $allTimepoints];
    }

    private function attemptTts($text, $lang) {
        $chunks = $this->splitText($text, 170);
        $allAudio = '';
        $allTimepoints = [];
        $totalChars = 0;

        foreach ($chunks as $chunk) {
            $audio = $this->fetchGoogleTts($chunk, $lang);
            if ($audio === null || strlen($audio) < 100) {
                if (empty($allAudio)) {
                    return ['error' => true, 'message' => 'TTS HTTP request failed'];
                }
                break;
            }

            $allAudio .= $audio;

            $words = preg_split('/\s+/u', $chunk);
            $wordCount = count($words);
            $charCount = mb_strlen($chunk);
            $chunkDuration = $charCount / 4.5;
            $timePerWord = $wordCount > 0 ? $chunkDuration / $wordCount : 0.1;

            $currentTime = $totalChars / 4.5;
            foreach ($words as $word) {
                $allTimepoints[] = ['markName' => $word, 'timeSeconds' => round($currentTime, 3)];
                $currentTime += $timePerWord;
            }

            $totalChars += $charCount;
        }

        if (empty($allAudio)) {
            return ['error' => true, 'message' => 'No audio generated'];
        }

        return ['audioContent' => base64_encode($allAudio), 'timepoints' => $allTimepoints];
    }

    private function fetchGoogleTts($chunk, $lang) {
        $patterns = [
            function ($q) use ($lang) {
                return 'https://translate.googleapis.com/translate_tts?ie=UTF-8&q=' . $q . '&tl=' . $lang . '&client=tw-ob';
            },
            function ($q) use ($lang) {
                return 'https://translate.googleapis.com/translate_tts?ie=UTF-8&q=' . $q . '&tl=' . $lang . '&client=gtx';
            },
            function ($q) use ($lang) {
                return 'https://translate.google.com/translate_tts?ie=UTF-8&q=' . $q . '&tl=' . $lang . '&client=tw-ob';
            },
        ];
        $quoted = urlencode($chunk);
        foreach ($patterns as $fn) {
            $url = $fn($quoted);
            $audio = $this->httpGet($url, 30);
            if ($audio !== null && strlen($audio) >= 100) {
                return $audio;
            }
        }
        return null;
    }

    private function splitText($text, $maxLen) {
        $chunks = [];
        $remaining = $text;
        while (mb_strlen($remaining) > 0) {
            if (mb_strlen($remaining) <= $maxLen) {
                $chunks[] = $remaining;
                break;
            }
            $chunk = mb_substr($remaining, 0, $maxLen);
            $lastSpace = mb_strrpos($chunk, ' ');
            if ($lastSpace !== false && $lastSpace > 0) {
                $chunk = mb_substr($chunk, 0, $lastSpace);
            }
            $chunks[] = $chunk;
            $remaining = mb_substr($remaining, mb_strlen($chunk));
        }
        return $chunks;
    }

    private function httpGet($url, $timeout = 20) {
        if (function_exists('curl_version')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_REFERER => 'https://translate.google.com/',
                CURLOPT_HTTPHEADER => ['Accept-Language: lo,th,en;q=0.9'],
            ]);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode === 200) {
                return $result;
            }
        }

        if (ini_get('allow_url_fopen')) {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => min($timeout, 15),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'header' => "Referer: https://translate.google.com/\r\nAccept-Language: lo,th,en;q=0.9\r\n",
                ],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);
            $result = @file_get_contents($url, false, $ctx);
            if ($result !== false) {
                return $result;
            }
        }

        return null;
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
                return [
                    'audioContent' => $row['audio_content'],
                    'timepoints' => json_decode($row['timepoints'], true) ?? [],
                ];
            }
        } catch (\Throwable $e) {
            $this->cacheEnabled = false;
        }
        return null;
    }

    private function saveToCache($text, $lang, $result) {
        try {
            $db = \App\Core\Database::getInstance();
            $hash = md5($text . '|' . $lang);
            $audioContent = $result['audioContent'] ?? '';
            $timepoints = json_encode($result['timepoints'] ?? [], JSON_UNESCAPED_UNICODE);
            $stmt = $db->prepare(
                "INSERT INTO tts_cache (text_hash, text_content, language, audio_content, timepoints, expires_at) 
                 VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
                 ON DUPLICATE KEY UPDATE audio_content = VALUES(audio_content), timepoints = VALUES(timepoints), expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY), accessed_count = accessed_count + 1"
            );
            $stmt->execute([$hash, $text, $lang, $audioContent, $timepoints]);
        } catch (\Throwable $e) {
            $this->cacheEnabled = false;
        }
    }

    private function initCacheTable() {
        try {
            $db = \App\Core\Database::getInstance();
            $db->exec("
                CREATE TABLE IF NOT EXISTS tts_cache (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    text_hash VARCHAR(64) NOT NULL,
                    text_content TEXT NOT NULL,
                    language VARCHAR(10) NOT NULL,
                    audio_content LONGTEXT NOT NULL,
                    timepoints TEXT DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    accessed_count INT DEFAULT 1,
                    expires_at TIMESTAMP NULL DEFAULT NULL,
                    UNIQUE KEY idx_text_hash_lang (text_hash, language),
                    INDEX idx_expires (expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Throwable $e) {
            $this->cacheEnabled = false;
        }
    }
}
