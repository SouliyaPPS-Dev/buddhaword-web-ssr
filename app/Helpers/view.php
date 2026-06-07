<?php

function getSiteUrl() {
    $siteUrl = $_ENV['SITE_URL'] ?? '';
    $canonical = 'https://buddhaword.net';

    // If SITE_URL is set and it's a free.nf address, override to canonical
    if ($siteUrl && stripos($siteUrl, 'free.nf') !== false) {
        return $canonical;
    }

    // Use configured SITE_URL (skip localhost dummy)
    if ($siteUrl && $siteUrl !== 'http://localhost/buddhaword') {
        return rtrim($siteUrl, '/');
    }

    // Fallback: detect from request host
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Map known InfinityFree hosts to canonical domain
    if (stripos($host, 'free.nf') !== false || stripos($host, 'byet.com') !== false) {
        return $canonical;
    }

    return $scheme . '://' . $host;
}

function absoluteUrl($path = '/') {
    return getSiteUrl() . '/' . ltrim($path, '/');
}
 
function canonicalUrl() {
    $siteUrl = getSiteUrl();
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return $siteUrl . $uri;
}

function view($path, $data = []) {
    $isProduction = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') === false;

    $seo = array_merge([
        'title' => 'ຄຳສອນພຸດທະ - ຮຽນຮູ້ພຣະສູດ ແລະ ທັມມະ',
        'description' => 'ເວັບໄຊທ໌ສຳລັບຮຽນຮູ້ພຣະສູດ ພຣະທັມ ຄຳສອນຂອງພຣະພຸດທະເຈົ້າ ພ້ອມທັງປຶ້ມ, ວິດີໂອ ແລະ ປະຕິທິນກິດຈະກຳ',
        'keywords' => 'ຄຳສອນພຸດທະ, ພຸດທະສາສະໜາ, ພຣະສູດ, ທັມມະ, ທັມ, ຮຽນຮູ້ພຸດທະ',
        'image' => absoluteUrl('assets/images/logo_shared.png'),
        'canonical' => canonicalUrl(),
        'json_ld' => null,
        'robots' => 'index, follow, max-snippet:-1, max-image-preview:large',
    ], $data['seo'] ?? []);

    $data['seo'] = $seo;

    extract($data);
    $viewFile = __DIR__ . "/../../views/" . str_replace('.', '/', $path) . ".php";
    
    ob_start();
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        echo "View file not found: $viewFile";
    }
    $content = ob_get_clean();
    
    ob_start();
    require __DIR__ . "/../../views/layouts/main.php";
    $html = ob_get_clean();

    if ($isProduction) {
        $html = minifyHtml($html);
    }

    echo $html;
}

function url($path = '/') {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = dirname($scriptName);
    if ($basePath === DIRECTORY_SEPARATOR) $basePath = '';
    return $basePath . '/' . ltrim($path, '/');
}

function minifyHtml($html) {
    $html = preg_replace('/<!--(.|\s)*?-->/s', '', $html);
    $parts = preg_split('/(<script[^>]*>|<\/script>)/s', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    $depth = 0;
    foreach ($parts as $i => $part) {
        if (preg_match('/^<script[^>]*>/i', $part)) { $depth++; continue; }
        if ($part === '</script>') { $depth--; continue; }
        if ($depth > 0) continue;
        $min = preg_replace('/\s{2,}/', ' ', $part);
        $min = preg_replace('/> </', '><', $min);
        $min = str_replace(["\r\n", "\r", "\n", "\t"], '', $min);
        $parts[$i] = trim($min);
    }
    return implode('', $parts);
}

 
 
  
  