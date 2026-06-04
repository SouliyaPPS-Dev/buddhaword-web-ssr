<?php
namespace App\Controllers;

use App\Models\Book;

class BookController {
    public function index() {
        $books = Book::getAll();

        $dharmaCategories = array_values(array_unique(array_filter(array_map(fn($b) => $b['ໝວດຟາຍ'] ?? '', $books))));
        sort($dharmaCategories);

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'ປື້ມ - ຄຳສອນພຸດທະ',
            'description' => 'ລວມປື້ມທັມມະ ແລະ ຄຳສອນຂອງພຣະພຸດທະເຈົ້າ',
            'url' => canonicalUrl(),
            'inLanguage' => 'lo',
        ];

        return view('pages.book.index', [
            'books' => $books,
            'dharmaCategories' => $dharmaCategories,
            'seo' => [
                'title' => 'ປື້ມ - ຄຳສອນພຸດທະ',
                'description' => 'ລວມປື້ມທັມມະ ແລະ ຄຳສອນຂອງພຣະພຸດທະເຈົ້າ',
                'keywords' => 'ປື້ມ, ທັມມະ, ຄຳສອນພຸດທະ, ພຸດທະສາສະໜາ',
                'json_ld' => $jsonLd,
            ]
        ]);
    }

    public function show($id) {
        $books = Book::getAll();
        $book = null;
        foreach ($books as $item) {
            if ($item['ID'] == $id) {
                $book = $item;
                break;
            }
        }

        if (!$book) {
            header("HTTP/1.0 404 Not Found");
            echo "Book not found";
            return;
        }

        $linkBook = $book['link'] ?? '';
        $pdfEmbedLink = str_replace('/view?usp=sharing', '/preview', $linkBook);
        $pdfDownloadUrl = '';
        if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $linkBook, $matches)) {
            $fileId = $matches[1];
            $pdfDownloadUrl = "https://drive.google.com/uc?export=download&id={$fileId}";
        }
 
        $canonicalUrl = canonicalUrl();
        $bookTitle = $book['ຊື່'] ?? 'ປື້ມ';

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Book',
            'name' => $bookTitle,
            'url' => $canonicalUrl,
            'inLanguage' => 'lo',
            'isPartOf' => [
                '@type' => 'Collection',
                'name' => 'ຄຳສອນພຸດທະ', 
            ],
            'breadcrumb' => [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'ໜ້າຫຼັກ', 'item' => absoluteUrl('')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'ປື້ມ', 'item' => absoluteUrl('/book')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $bookTitle],
                ],
            ],
        ];

        if (!empty($book['imageURL'])) {
            $jsonLd['image'] = $book['imageURL'];
        }

        return view('pages.book.show', [
            'book' => $book,
            'pdfEmbedLink' => $pdfEmbedLink,
            'pdfDownloadUrl' => $pdfDownloadUrl,
            'seo' => [
                'title' => $bookTitle . ' - ຄຳສອນພຸດທະ',
                'description' => 'ປຶ້ມ: ' . $bookTitle,
                'keywords' => ($book['ໝວດທັມ'] ?? '') . ', ປື້ມ, ທັມມະ, ຄຳສອນພຸດທະ',
                'image' => $book['imageURL'] ?? absoluteUrl('assets/images/logo_shared.png'),
                'json_ld' => $jsonLd,
            ]
        ]);
    }
}

   