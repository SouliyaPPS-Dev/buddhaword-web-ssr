<?php
namespace App\Controllers;

use App\Models\Sutra;
use App\Models\Book;
use App\Models\Video;
use App\Models\Calendar;

class ApiController {
    public function checkUpdate() {
        header('Content-Type: application/json');
        $cacheFile = __DIR__ . '/../../storage/cache/sutra_api.json';
        $version = file_exists($cacheFile) ? filemtime($cacheFile) : 0;
        echo json_encode(['version' => $version]);
    }

    public function syncSutras() {
        header('Content-Type: application/json');
        try {
            set_time_limit(60);
            $sutras = Sutra::getAll(true);
            if (empty($sutras)) {
                echo json_encode(['success' => false, 'message' => 'ບໍ່ພົບຂໍ້ມູນ'], JSON_UNESCAPED_UNICODE);
                return;
            }
            $cacheFile = __DIR__ . '/../../storage/cache/sutra_api.json';
            $version = file_exists($cacheFile) ? filemtime($cacheFile) : time();
            echo json_encode(['success' => true, 'version' => $version, 'data' => $sutras], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function syncBooks() {
        header('Content-Type: application/json');
        try {
            set_time_limit(30);
            $books = Book::getAll();
            echo json_encode(['success' => true, 'data' => $books], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function syncVideos() {
        header('Content-Type: application/json');
        try {
            set_time_limit(30);
            $videos = Video::getAll();
            echo json_encode(['success' => true, 'data' => $videos], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function syncCalendar() {
        header('Content-Type: application/json');
        try {
            set_time_limit(30);
            $events = Calendar::getAll();
            echo json_encode(['success' => true, 'data' => $events], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function searchVideos() {
        header('Content-Type: application/json');
        try {
            $search = $_GET['search'] ?? '';
            $category = $_GET['category'] ?? '';

            $videos = Video::getAll();
            $videos = array_reverse($videos);

            if ($search !== '') {
                $searchTerm = mb_strtolower(trim($search));
                $videos = array_filter($videos, function($v) use ($searchTerm) {
                    return mb_strpos(mb_strtolower($v['ຊື່ພຣະສູດ'] ?? ''), $searchTerm) !== false
                        || mb_strpos(mb_strtolower($v['ໝວດທັມ'] ?? ''), $searchTerm) !== false;
                });
            }

            if ($category !== '') {
                $categoryTerm = mb_strtolower(trim($category));
                $videos = array_filter($videos, function($v) use ($categoryTerm) {
                    return mb_strtolower(trim($v['ໝວດທັມ'] ?? '')) === $categoryTerm;
                });
            }

            $videos = array_values(array_map(function($v) {
                $v['_thumbnail'] = $this->getThumbnailUrl($v['link'] ?? '');
                preg_match('/(?:youtu\.be\/|youtube\.com\/(?:.*v=|.*\/|.*embed\/|.*shorts\/))([\w-]+)/', $v['link'] ?? '', $m);
                $v['_ytId'] = $m[1] ?? '';
                return $v;
            }, $videos));

            echo json_encode(['success' => true, 'data' => $videos], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    private function getThumbnailUrl($link) {
        if (strpos($link, 'youtube.com') !== false || strpos($link, 'youtu.be') !== false) {
            preg_match('/(?:youtu\.be\/|youtube\.com\/(?:.*v=|.*\/|.*embed\/|.*shorts\/))([\w-]+)/', $link, $matches);
            $videoId = $matches[1] ?? null;
            return $videoId ? "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg" : '';
        } elseif (strpos($link, 'drive.google.com') !== false) {
            preg_match('/(?:drive\.google\.com\/(?:.*\/d\/|file\/d\/))([a-zA-Z0-9_-]+)/', $link, $matches);
            $fileId = $matches[1] ?? null;
            return $fileId ? "https://lh3.googleusercontent.com/d/{$fileId}=s320?authuser=0" : '';
        }
        return '';
    }
}

      