<?php
namespace App\Models;

class Calendar {
    public static function getAll($refresh = false) {
        $cacheFile = __DIR__ . '/../../storage/cache/calendar_api.json';
        $cacheTime = 86400; // 24 hours

        if (!$refresh && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
            $json = file_get_contents($cacheFile);
        } else {
            $url = $_ENV['CALENDAR_API_URL'] ?? '';
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
        foreach ($rows as $index => $row) {
            $rowObject = [];
            foreach ($headers as $colIndex => $header) {
                $rowObject[$header] = $row[$colIndex] ?? "";
            }

            if (!isset($rowObject['ID']) || empty($rowObject['ID'])) {
                $rowObject['ID'] = (string)($index + 1);
            }

            $rowObject['startDateISO'] = self::convertToISO($rowObject['startDateTime'] ?? '');
            $rowObject['endDateISO'] = self::convertToISO($rowObject['endDateTime'] ?? '');

            if (!empty(array_filter($rowObject))) {
                $transformed[] = $rowObject;
            }
        }

        return $transformed;
    }

    private static function convertToISO($dateStr) {
        if (empty($dateStr)) return '';

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $dateStr, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $dateStr, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[1], $matches[2], $matches[3]);
        }

        return $dateStr;
    }

    public static function getById($id) {
        $events = self::getAll();
        foreach ($events as $event) {
            if ($event['ID'] == $id) {
                return $event;
            }
        }
        return null;
    }
}
