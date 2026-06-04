<?php
session_start();

// Production detection (based on hostname, NOT .env setting)
$isProduction = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') === false;

if ($isProduction) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
            putenv(trim($name) . '=' . trim($value));
        }
    } 
}

// Composer autoloader (for smalot/pdfparser etc.)
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require $composerAutoload;
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load Helpers
require_once __DIR__ . '/../app/Helpers/view.php';

// Send security headers
if ($isProduction) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 1; mode=block');
}

// --- Serve static files from /public/ if they exist ---
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rawurldecode($uri);
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptPath === '/') $scriptPath = '';
if ($scriptPath && strpos($uri, $scriptPath) === 0) {
    $uri = substr($uri, strlen($scriptPath));
}
$uri = ($uri === '' || $uri === false) ? '/' : '/' . ltrim($uri, '/');
$publicFile = __DIR__ . $uri;
// Try WebP version if browser supports it
$acceptWebp = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
if ($acceptWebp && $uri !== '/') {
    $ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
    if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
        $webpFile = $publicFile . '.webp';
        if (file_exists($webpFile) && is_file($webpFile)) {
            $publicFile = $webpFile;
            $uri .= '.webp';
        }
    }
}
if ($uri !== '/' && file_exists($publicFile) && is_file($publicFile)) {
    $mimeTypes = [
        'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
        'css' => 'text/css', 'js' => 'application/javascript',
        'json' => 'application/json', 'ttf' => 'font/ttf',
        'woff' => 'font/woff', 'woff2' => 'font/woff2', 'webp' => 'image/webp',
        'webmanifest' => 'application/manifest+json', 'xml' => 'application/xml',
        'html' => 'text/html',
    ];
    $ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    if (in_array($ext, ['webp', 'png', 'jpg', 'jpeg', 'gif', 'css', 'js', 'ttf', 'woff', 'woff2'])) {
        header('Cache-Control: public, max-age=31536000, immutable');
    }
    $size = @filesize($publicFile);
    if ($size > 0) {
        header('Content-Length: ' . $size);
    }
    while (ob_get_level()) { ob_end_clean(); }
    readfile($publicFile);
    flush();
    return;
}
// macOS NFD normalization fallback for Lao filenames
if ($uri !== '/' && function_exists('normalizer_normalize')) {
    $nfd = normalizer_normalize($publicFile, \Normalizer::FORM_D);
    if ($nfd !== false && $nfd !== $publicFile && file_exists($nfd) && is_file($nfd)) {
        $ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
            'css' => 'text/css', 'js' => 'application/javascript',
            'json' => 'application/json', 'ttf' => 'font/ttf',
            'woff' => 'font/woff', 'woff2' => 'font/woff2',
            'webmanifest' => 'application/manifest+json', 'xml' => 'application/xml',
            'html' => 'text/html',
        ];
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        $size = @filesize($nfd);
        if ($size > 0) {
            header('Content-Length: ' . $size);
        }
        while (ob_get_level()) { ob_end_clean(); }
        readfile($nfd);
        flush();
        return;
    }
}
// -----------------------------------------------------------------------

// Load Routes
$routes = require_once __DIR__ . '/../routes/web.php';

// Router Logic
$router = new App\Core\Router($routes);
$router->dispatch();

     