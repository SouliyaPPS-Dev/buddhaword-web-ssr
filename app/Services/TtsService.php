<?php
namespace App\Services;

use Afaya\EdgeTTS\Service\EdgeTTS;

class TtsService {
    private $voiceMap = [
        'lo-LA' => 'lo-LA-KeomanyNeural',  // no male voice available for Lao
        'th-TH' => 'th-TH-NiwatNeural',
        'en-US' => 'en-US-GuyNeural',
    ];

    private $maxChars = 2000;

    public function synthesize($text, $languageCode = 'lo-LA') {
        $voice = $this->voiceMap[$languageCode] ?? 'en-US-AriaNeural';

        if (mb_strlen($text) > $this->maxChars) {
            $text = mb_substr($text, 0, $this->maxChars);
            $last = mb_strrpos($text, '.');
            if ($last > mb_strrpos($text, '?')) $last = mb_strrpos($text, '?');
            if ($last > mb_strrpos($text, '!')) $last = mb_strrpos($text, '!');
            if ($last > 0) $text = mb_substr($text, 0, $last + 1);
        }

        // Try EdgeTTS (WebSocket, requires ext-sockets) first
        if (extension_loaded('sockets') && class_exists(EdgeTTS::class)) {
            $result = $this->synthesizeEdgeTTS($text, $voice);
            if (!isset($result['error'])) {
                return $result;
            }
        }

        return $this->synthesizeHttp($text, $voice);
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

    private function synthesizeHttp($text, $voice) {
        // 1. FreeTTS API — Microsoft Edge voices via plain HTTP
        $result = $this->attemptFreeTts($text, $voice);
        if (!isset($result['error'])) {
            return $result;
        }

        // 2. StreamElements TTS (HTTP GET, free, supports Edge voices including Lao)
        $result = $this->attemptStreamElements($text, $voice);
        if (!isset($result['error'])) {
            return $result;
        }

        return ['fallback' => true];
    }

    private function attemptFreeTts($text, $voice) {
        $chunks = $this->splitText($text, 170);
        $allAudio = '';

        $headers = [
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Origin: https://freetts.org',
            'Referer: https://freetts.org/',
        ];

        foreach ($chunks as $chunk) {
            $payload = json_encode([
                'text' => $chunk, 'voice' => $voice,
                'rate' => '+0%', 'pitch' => '+0Hz',
            ]);

            $ch = curl_init('https://freetts.org/api/tts');
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

            $audioUrl = 'https://freetts.org/api/audio/' . $data['file_id'];
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

    private function attemptStreamElements($text, $voice) {
        $chunks = $this->splitText($text, 150);
        $allAudio = '';

        foreach ($chunks as $chunk) {
            $url = 'https://api.streamelements.com/kappa/v2/speech?voice='
                 . urlencode($voice)
                 . '&text=' . urlencode($chunk);

            $audio = $this->httpGet($url, 20);
            if ($audio === null || strlen($audio) < 100) {
                if (!empty($allAudio)) break;
                return ['error' => true, 'message' => 'StreamElements request failed'];
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
}
