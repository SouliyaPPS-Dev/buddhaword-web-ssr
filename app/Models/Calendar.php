<?php
namespace App\Models;

class Calendar {
    public static function getAll() {
        $url = $_ENV['CALENDAR_API_URL'];
        $json = @file_get_contents($url);
        if ($json === false) return [];
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
            
            // Ensure ID exists, use index if not provided
            if (!isset($rowObject['ID']) || empty($rowObject['ID'])) {
                $rowObject['ID'] = (string)($index + 1);
            }

            // Normalize dates for logic
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
        
        // Handle DD/MM/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $dateStr, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        }
        
        // Handle YYYY-MM-DD
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
