<?php
/**
 * Sitemap Generator — run: php sitemap-generate.php
 * Generates public/sitemap.xml from all data sources (sutras, books, videos, calendar)
 */

// Bootstrap manually (without dispatching the router)
session_start();

if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Models\Sutra;
use App\Models\Book;
use App\Models\Video;
use App\Models\Calendar;
use App\Models\PdfBook;

// Detect site URL
$siteUrl = rtrim($_ENV['SITE_URL'] ?? 'https://buddhaword.net', '/');

// ISO date now
$now = date('Y-m-d\TH:i:s.v\Z', time());

// Collect all URLs
$urls = [];

// Static pages
$staticPages = [
    ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/book', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => '/video', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => '/calendar', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => '/favorites', 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['loc' => '/about', 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['loc' => '/privacy', 'priority' => '0.3', 'changefreq' => 'monthly'],
    ['loc' => '/search-books', 'priority' => '0.7', 'changefreq' => 'weekly'],
];
foreach ($staticPages as $page) {
    $urls[] = [
        'loc' => $siteUrl . $page['loc'],
        'lastmod' => $now,
        'changefreq' => $page['changefreq'],
        'priority' => $page['priority'],
    ];
}

// Sutras
$categories = [];
try {
    $sutras = Sutra::getAll();
    foreach ($sutras as $sutra) {
        $id = $sutra['ID'] ?? '';
        $title = $sutra['ຊື່ພຣະສູດ'] ?? '';
        $category = $sutra['ໝວດທັມ'] ?? '';
        if ($id) {
            $urls[] = [
                'loc' => $siteUrl . '/sutra/details/' . $id,
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ];
        }
        if ($category && !in_array($category, $categories)) {
            $categories[] = $category;
        }
    }
    foreach ($categories as $cat) {
        $urls[] = [
            'loc' => $siteUrl . '/sutra/' . rawurlencode($cat),
            'lastmod' => $now,
            'changefreq' => 'weekly',
            'priority' => '0.7',
        ];
    }
} catch (\Exception $e) {
    fwrite(STDERR, "Error fetching sutras: " . $e->getMessage() . "\n");
}

// Books
try {
    $books = Book::getAll();
    foreach ($books as $book) {
        $id = $book['ID'] ?? '';
        if ($id) {
            $urls[] = [
                'loc' => $siteUrl . '/book/view/' . $id,
                'lastmod' => $now,
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        }
    }
} catch (\Exception $e) {
    fwrite(STDERR, "Error fetching books: " . $e->getMessage() . "\n");
}

// Videos
try {
    $videos = Video::getAll();
    foreach ($videos as $video) {
        $id = $video['ID'] ?? '';
        if ($id) {
            $urls[] = [
                'loc' => $siteUrl . '/video/view/' . $id,
                'lastmod' => $now,
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }
    }
} catch (\Exception $e) {
    fwrite(STDERR, "Error fetching videos: " . $e->getMessage() . "\n");
}

// Calendar events
try {
    $events = Calendar::getAll();
    foreach ($events as $event) {
        $id = $event['ID'] ?? '';
        $title = $event['title'] ?? $event['Title'] ?? '';
        if ($id) {
            $urls[] = [
                'loc' => $siteUrl . '/calendar/view/' . $id,
                'lastmod' => $now,
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }
    }
} catch (\Exception $e) {
    fwrite(STDERR, "Error fetching calendar events: " . $e->getMessage() . "\n");
}

// PDF Books
try {
    $pdfBooks = PdfBook::getBooks();
    foreach ($pdfBooks as $pdfBook) {
        $slug = $pdfBook['slug'] ?? '';
        if ($slug) {
            $urls[] = [
                'loc' => $siteUrl . '/search-books/' . $slug,
                'lastmod' => $now,
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }
    }
} catch (\Exception $e) {
    fwrite(STDERR, "Error fetching PDF books: " . $e->getMessage() . "\n");
}

// Generate XML
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($urls as $url) {
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') . '</loc>' . "\n";
    $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
    $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
    $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
    $xml .= '  </url>' . "\n";
}

$xml .= '</urlset>' . "\n";

// Write sitemap
$sitemapPath = __DIR__ . '/public/sitemap.xml';
file_put_contents($sitemapPath, $xml);

$count = count($urls);
echo "Sitemap generated: {$sitemapPath}\n";
echo "Total URLs: {$count}\n";
  