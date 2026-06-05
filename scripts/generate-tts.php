#!/usr/bin/env php
<?php
/**
 * Batch TTS Audio Pre-generation
 * 
 * Pre-generates Lao/Thai/English TTS audio using edge-tts (Microsoft Edge Neural TTS)
 * Generated files are stored in storage/tts/ and served by TtsLibrary service.
 *
 * Usage:
 *   php scripts/generate-tts.php                      # scan DB content
 *   php scripts/generate-tts.php --text="ສະບາຍດີ" --lang=lo-LA   # single text
 *   php scripts/generate-tts.php --all                 # scan all sources
 *   php scripts/generate-tts.php --texts=file.json     # from JSON file
 *   php scripts/generate-tts.php --stats               # show library stats
 */

require __DIR__ . '/../vendor/autoload.php';

// Bootstrap autoloading + env
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

$service = new \App\Services\TtsLibrary();
$usage = [];

// Parse args
$args = [];
foreach ($argv as $i => $a) {
    if ($i === 0) continue;
    if (strpos($a, '--') === 0) {
        $parts = explode('=', substr($a, 2), 2);
        $args[$parts[0]] = $parts[1] ?? true;
    }
}

if (isset($args['stats'])) {
    echo "TTS Library Stats:\n";
    echo "  Files: " . $service->countFiles() . "\n";
    echo "  Size: " . round($service->totalSize() / 1024 / 1024, 2) . " MB\n";
    echo "  Storage: " . $service->getStorageDir() . "\n";
    exit(0);
}

// --text mode
if (isset($args['text'])) {
    $lang = $args['lang'] ?? 'lo-LA';
    echo "Generating: [{$lang}] {$args['text']}\n";
    $result = $service->generateWithPython($args['text'], $lang);
    if (isset($result['error'])) {
        echo "  FAILED: {$result['message']}\n";
        exit(1);
    }
    echo "  OK: " . strlen(base64_decode($result['audioContent'])) . " bytes\n";
    exit(0);
}

// --texts from JSON file
if (isset($args['texts'])) {
    $data = json_decode(file_get_contents($args['texts']), true);
    if (!$data) {
        echo "Invalid JSON file\n";
        exit(1);
    }
    echo "Generating " . count($data) . " items...\n";
    $results = $service->generateBatch($data, function($i, $total, $status) {
        echo "\r  [$i/$total] $status    ";
    });
    echo "\n";
    return;
}

// Scan database
echo "Scanning database content...\n";
$items = [];

try {
    $db = \App\Core\Database::getInstance();
    
    // Books
    $books = $db->query("SELECT title, description FROM books WHERE title IS NOT NULL");
    foreach ($books as $b) {
        if (trim($b['title'])) $items[] = ['text' => trim($b['title']), 'lang' => 'lo-LA'];
        if (trim($b['description'] ?? '')) $items[] = ['text' => trim($b['description']), 'lang' => 'lo-LA'];
    }
    echo "  Books: " . count($items) . " items so far\n";

    // Sutras 
    $sutras = $db->query("SELECT title, content FROM sutras WHERE content IS NOT NULL LIMIT 500");
    $sutraItems = 0;
    foreach ($sutras as $s) {
        if (trim($s['title'])) $items[] = ['text' => trim($s['title']), 'lang' => 'lo-LA'];
        $content = trim($s['content'] ?? '');
        if ($content) {
            $paras = preg_split('/\n\s*\n/', $content, -1, PREG_SPLIT_NO_EMPTY);
            foreach (array_slice($paras, 0, 20) as $p) {
                $p = trim($p);
                if (mb_strlen($p) > 20 && mb_strlen($p) <= 5000) {
                    $items[] = ['text' => $p, 'lang' => 'lo-LA'];
                    $sutraItems++;
                    if ($sutraItems >= 200) break 2;
                }
            }
        }
    }
    echo "  Sutras: " . count($items) . " items so far\n";

    // Remove duplicates
    $seen = [];
    $unique = [];
    foreach ($items as $it) {
        $key = md5($it['text'] . '|' . $it['lang']);
        if (!isset($seen[$key])) {
            $seen[$key] = true;
            $unique[] = $it;
        }
    }
    $items = $unique;
    echo "  Unique: " . count($items) . " items\n";
    
} catch (\Throwable $e) {
    echo "  DB scan failed: {$e->getMessage()}\n";
    echo "  Falling back to --text mode. Use: php scripts/generate-tts.php --text=\"your text\" --lang=lo-LA\n";
    exit(1);
}

if (empty($items)) {
    echo "No content found to generate.\n";
    exit(0);
}

echo "\nStarting batch generation...\n";
$start = microtime(true);
$results = $service->generateBatch($items, function($i, $total, $status) {
    echo "\r  [$i/$total] $status    ";
});
$elapsed = round(microtime(true) - $start, 2);

$generated = count(array_filter($results, fn($r) => $r['status'] === 'generated'));
$skipped = count(array_filter($results, fn($r) => $r['status'] === 'skipped'));
$failed = count(array_filter($results, fn($r) => $r['status'] === 'failed'));

echo "\n\nDone in {$elapsed}s:\n";
echo "  Generated: {$generated}\n";
echo "  Skipped (exists): {$skipped}\n";
echo "  Failed: {$failed}\n";

echo "\n---\n";
echo "Library now has " . $service->countFiles() . " audio files\n";
echo "Total size: " . round($service->totalSize() / 1024 / 1024, 2) . " MB\n";
