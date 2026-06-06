<?php
namespace App\Controllers;

use App\Models\Sutra;
use App\Models\Book;
use App\Models\Video;
use App\Models\Calendar;
use App\Models\PdfBook;

class SitemapController {
    public function generate() {
        header('Content-Type: application/xml; charset=utf-8');

        $siteUrl = rtrim(getSiteUrl(), '/');
        $now = date('Y-m-d\TH:i:s.v\Z', time());

        $urls = [];

        $staticPages = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => '/sutra', 'priority' => '1.0', 'changefreq' => 'daily'],
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

        try {
            $sutras = Sutra::getAll();
            $categories = [];
            foreach ($sutras as $sutra) {
                $id = $sutra['ID'] ?? '';
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
        }

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
        }

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
        }

        try {
            $events = Calendar::getAll();
            foreach ($events as $event) {
                $id = $event['ID'] ?? '';
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
        }

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
        }

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

        echo $xml;
    }
}
