<?php
namespace App\Models;

class Video {
    public static function getAll() {
        $url = $_ENV['VIDEO_API_URL'];
        $json = @file_get_contents($url);
        if ($json === false) return [];
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
