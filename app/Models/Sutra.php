<?php
namespace App\Models;

class Sutra {
    public static function getAll($refresh = false) {
        $sutraData = self::fetchSutraApi($refresh);
        $buddhaNatureData = self::fetchBuddhaNatureJson();
        
        return array_merge($sutraData, $buddhaNatureData);
    }

    private static function fetchSutraApi($refresh = false) {
        $cacheFile = __DIR__ . '/../../storage/cache/sutra_api.json';
        $cacheTime = 86400; // 24 hours

        if (!$refresh && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
            $json = file_get_contents($cacheFile);
        } else {
            $url = $_ENV['SUTRA_API_URL'];
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
                } elseif ($refresh) {
                    throw new \Exception("ບໍ່ສາມາດເຊື່ອມຕໍ່ກັບ API ໄດ້");
                } elseif (file_exists($cacheFile)) {
                    $json = file_get_contents($cacheFile);
                } else {
                    return [];
                }
            }
        }

        $data = json_decode($json, true);

        if (!isset($data['values']) || empty($data['values'])) {
            if ($refresh) throw new \Exception("ບໍ່ມີຂໍ້ມູນຈາກ API");
            return [];
        }

        $headers = array_shift($data['values']);
        $rows = $data['values'];

        $transformed = [];
        foreach ($rows as $row) {
            $rowObject = [];
            foreach ($headers as $index => $header) {
                $rowObject[$header] = trim($row[$index] ?? "");
            }
            if (!isset($rowObject['ສຽງ'])) {
                $rowObject['ສຽງ'] = "";
            }
            
            // Filter out empty rows
            if (!empty(array_filter($rowObject))) {
                $transformed[] = $rowObject;
            }
        }

        return $transformed;
    }

    private static function fetchBuddhaNatureJson() {
        $filePath = __DIR__ . '/../../public/assets/buddha-nature.json';
        if (!file_exists($filePath)) return [];

        $json = file_get_contents($filePath);
        $data = json_decode($json, true);

        $transformed = [];
        foreach ($data as $item) {
            $categoryName = "";
            switch ($item['category']) {
                case "627515988b61fc33c0d0ea97":
                    $categoryName = "ທໍາໃນເບື້ອງຕົ້ນ";
                    break;
                case "627515918b61fc33c0d0ea94":
                    $categoryName = "ທໍາໃນທ່າມກາງ";
                    break;
                case "627515888b61fc33c0d0ea91":
                    $categoryName = "ທໍາໃນທີສຸດ";
                    break;
                default:
                    $categoryName = $item['category'];
                    break;
            }

            $transformed[] = [
                'ID' => $item['_id'],
                'ຊື່ພຣະສູດ' => $item['title'],
                'ພຣະສູດ' => $item['content'],
                'ຮູບ' => $item['thumbnail'] ?? "",
                'ໝວດທັມ' => $categoryName,
                'ສຽງ' => "",
            ];
        }

        return $transformed;
    }
}
 