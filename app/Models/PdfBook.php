<?php
namespace App\Models;

class PdfBook {
    private static function assetsDir() {
        return __DIR__ . '/../../public/assets';
    }   

    public static function getBooks() {
        $path = self::assetsDir() . '/books.json';
        if (!file_exists($path)) return [];
        $books = json_decode(file_get_contents($path), true);
        foreach ($books as &$book) {
            $slug = $book['slug'];
            $infoPath = self::assetsDir() . '/' . $slug . '/book.json';
            if (file_exists($infoPath)) {
                $meta = json_decode(file_get_contents($infoPath), true);
                $book['title'] = $meta['title'] ?? $slug;
            } else {
                $book['title'] = !empty($book['file']) ? self::titleFromFile($book['file']) : $slug;
            }
        }
        return $books;
    } 

    private static function getDataDir($slug) {
        $dir = self::assetsDir() . '/' . $slug;
        if (!is_dir($dir)) return null;
        return $dir;
    }

    public static function getInfo($slug) {
        $dir = self::getDataDir($slug);
        if (!$dir) return null;
        $path = $dir . '/book.json';
        if (!file_exists($path)) return null;
        $info = json_decode(file_get_contents($path), true);

        return $info;
    } 
 
    private static function fixLaoText($text) {
        $text = preg_replace('/(?<=[\x{E81}-\x{EAE}]) (?=[\x{EB9}\x{EB8}])/u', '', $text);
        $text = preg_replace('/(?<=[\x{E81}-\x{EAE}]) (?=[\x{EC8}\x{EC9}])/u', "\x{EB9}", $text);
        $text = preg_replace('/\x{EAA} \x{E95}/u', "", $text);
        $text = preg_replace('/\x{E9E} \x{EA1}/u', "\x{E9E}\x{EB9}\x{EA1}", $text);
        $text = str_replace("\x{E84}\x{EB2}\x{E99}\x{EB2}", "\x{E84}\x{EB3}\x{E99}\x{EB3}", $text);
        return $text;
    }

    public static function getAll($slug) {
        $dir = self::getDataDir($slug);
        if (!$dir) return [];
        $path = $dir . '/pages.json';
        if (!file_exists($path)) return [];
        $pages = json_decode(file_get_contents($path), true);
        foreach ($pages as &$page) {
            $page['text'] = self::fixLaoText($page['text']);
        }
        return $pages;
    }

    public static function getPage($slug, $pageNum) {
        $all = self::getAll($slug);
        foreach ($all as $p) {
            if ($p['page'] == $pageNum) return $p;
        }
        return null;
    }

    public static function search($slug, $query) {
        $all = self::getAll($slug);
        if (empty($query)) return [];

        $q = mb_strtolower(trim($query));
        $results = [];

        foreach ($all as $page) {
            $text = mb_strtolower($page['text']);
            $pos = mb_strpos($text, $q);
            if ($pos !== false) {
                $snippet = self::getSnippet($page['text'], $pos, 120);
                $results[] = [
                    'page' => $page['page'],
                    'snippet' => $snippet,
                    'matches' => mb_substr_count($text, $q),
                ];
            }
            if (count($results) >= 100) break;
        }

        return $results;
    }

    private static function getSnippet($text, $pos, $length = 120) {
        $textLen = mb_strlen($text);
        $start = max(0, $pos - intval($length / 2));
        $end = min($textLen, $start + $length);
        if ($start > 0) {
            $snippet = '...' . mb_substr($text, $start, $end - $start);
        } else {
            $snippet = mb_substr($text, $start, $end - $start);
        }
        if ($end < $textLen) $snippet .= '...';
        return $snippet;
    }

    private static function titleFromFile($file) {
        $basename = basename($file);
        $basename = preg_replace('/\.(pdf|docx|doc)$/iu', '', $basename);
        $basename = trim($basename);
        return $basename;
    }
}
