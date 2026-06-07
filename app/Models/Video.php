<?php
namespace App\Models;

class Video {
    public static function getAll($refresh = false) {
        $cacheFile = __DIR__ . '/../../storage/cache/video_api.json';
        $cacheTime = 86400; // 24 hours

        if (!$refresh && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
            $json = file_get_contents($cacheFile);
        } else {
            $url = $_ENV['VIDEO_API_URL'] ?? '';
            if (!$url) {
                if (file_exists($cacheFile)) {
                    $json = file_get_contents($cacheFile);
                } else {
                    return [];
                }
            } else {
                $json = @file_get_contents($url);
                if ($json) {
                    if (!is_dir(dirname($cacheFile))) mkdir(dirname($cacheFile), 0777, true);
                    file_put_contents($cacheFile, $json);
                } elseif (file_exists($cacheFile)) {
                    $json = file_get_contents($cacheFile);
                } else {
                    return [];
                }
            }
        }

        $data = json_decode($json, true);
        if (!isset($data['values'])) return [];

        $headers = array_shift($data['values']);
        $rows = $data['values'];

        $transformed = [];
        foreach ($rows as $row) {
            $rowObject = [];
            foreach ($headers as $index => $header) {
                $rowObject[$header] = $row[$index] ?? "";
            }
            if (!empty(array_filter($rowObject))) {
                $transformed[] = $rowObject;
            }
        }

        return $transformed;
    }
}
