<?php
namespace App\Controllers;

use App\Models\Sutra;
use App\Models\Book;
use App\Models\Video;
use App\Models\Calendar;
use App\Models\PdfBook;
   
class SearchController {
    private const MAX_PER_TYPE = 30;
    private const MAX_TOTAL = 100;

    public function search() {
        $query = $_GET['q'] ?? '';
        $q = mb_strtolower(trim($query));

        if (mb_strlen($q) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            return;
        }

        $results = [];
        $countByType = [];

        // 1. Search Sutras
        $sutras = Sutra::getAll();
        foreach ($sutras as $item) {
            if (($countByType['sutra'] ?? 0) >= self::MAX_PER_TYPE) break;
            if ($this->match($q, $item['ຊື່ພຣະສູດ'] ?? '') || $this->match($q, $item['ພຣະສູດ'] ?? '')) {
                $results[] = [
                    'type' => 'sutra',
                    'title' => $item['ຊື່ພຣະສູດ'] ?? '',
                    'detail' => mb_substr(strip_tags($item['ພຣະສູດ'] ?? ''), 0, 150) . '...',
                    'url' => url('/sutra/details/' . ($item['ID'] ?? '')),
                    'category' => $item['ໝວດທັມ'] ?? ''
                ];
                $countByType['sutra'] = ($countByType['sutra'] ?? 0) + 1;
            }
        }

        // 2. Search PDF/DOCX Book Text (early for diversity)
        $pdfBooks = PdfBook::getBooks();
        foreach ($pdfBooks as $book) {
            if (($countByType['book-page'] ?? 0) >= self::MAX_PER_TYPE) break;
            $slug = $book['slug'];
            $pageResults = PdfBook::search($slug, $q);
            foreach ($pageResults as $pr) {
                if (($countByType['book-page'] ?? 0) >= self::MAX_PER_TYPE) break;
                $results[] = [
                    'type' => 'book-page',
                    'title' => $book['title'] . ' - ໜ້າ ' . $pr['page'],
                    'detail' => $pr['snippet'],
                    'url' => url('/search-books/' . $slug . '/page/' . $pr['page']),
                    'category' => $book['title'],
                    'matches' => $pr['matches']
                ];
                $countByType['book-page'] = ($countByType['book-page'] ?? 0) + 1;
            }
        }

        // 3. Search Books
        $books = Book::getAll();
        foreach ($books as $item) {
            if (($countByType['book'] ?? 0) >= self::MAX_PER_TYPE) break;
            if ($this->match($q, $item['ຊື່'] ?? '') || $this->match($q, $item['ໝວດຟາຍ'] ?? '') || $this->match($q, $item['ໝວດທັມ'] ?? '')) {
                $results[] = [
                    'type' => 'book',
                    'title' => $item['ຊື່'] ?? '',
                    'detail' => ($item['ໝວດຟາຍ'] ?? '') . ' | ' . ($item['ໝວດທັມ'] ?? ''),
                    'url' => url('/book/view/' . ($item['ID'] ?? '')),
                    'category' => 'ປື້ມ'
                ];
                $countByType['book'] = ($countByType['book'] ?? 0) + 1;
            }
        }

        // 4. Search Videos
        $videos = Video::getAll();
        foreach ($videos as $item) {
            if (($countByType['video'] ?? 0) >= self::MAX_PER_TYPE) break;
            if ($this->match($q, $item['ຊື່ພຣະສູດ'] ?? '') || $this->match($q, $item['ພຣະສູດ'] ?? '') || $this->match($q, $item['ໝວດທັມ'] ?? '')) {
                $results[] = [
                    'type' => 'video',
                    'title' => $item['ຊື່ພຣະສູດ'] ?? '',
                    'detail' => mb_substr(strip_tags($item['ພຣະສູດ'] ?? ''), 0, 150) . '...',
                    'url' => url('/video'),
                    'category' => 'Video'
                ];
                $countByType['video'] = ($countByType['video'] ?? 0) + 1;
            }
        }

        // 5. Search Calendar
        $events = Calendar::getAll();
        foreach ($events as $item) {
            if (($countByType['calendar'] ?? 0) >= self::MAX_PER_TYPE) break;
            if ($this->match($q, $item['title'] ?? '') || $this->match($q, $item['details'] ?? '') || $this->match($q, $item['startDateTime'] ?? '') || $this->match($q, $item['endDateTime'] ?? '')) {
                $results[] = [
                    'type' => 'calendar',
                    'title' => $item['title'] ?? '',
                    'detail' => ($item['startDateTime'] ?? '') . ' - ' . mb_substr(strip_tags($item['details'] ?? ''), 0, 100) . '...',
                    'url' => url('/calendar/view/' . ($item['ID'] ?? '')),
                    'category' => 'ປະຕິທິນ'
                ];
                $countByType['calendar'] = ($countByType['calendar'] ?? 0) + 1;
            }
        }

        header('Content-Type: application/json');
        echo json_encode(array_slice($results, 0, self::MAX_TOTAL), JSON_UNESCAPED_UNICODE);
    }

    private function match($query, $text) {
        if (empty($text)) return false;
        return mb_strpos(mb_strtolower($text), $query) !== false;
    }
}
