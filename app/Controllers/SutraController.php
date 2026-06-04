<?php
namespace App\Controllers;
  
use App\Models\Sutra;
  
class SutraController {
    public function index() {
        $sutras = Sutra::getAll();

        $categoriesFromData = [];
        foreach ($sutras as $item) {
            $category = $item['ໝວດທັມ'] ?? '';
            if ($category !== '' && !in_array($category, $categoriesFromData)) {
                $categoriesFromData[] = $category;
            }
        }
 
        $imagesDir = __DIR__ . '/../../public/images/sutra/';
        $availableCategories = [];
        if (is_dir($imagesDir)) {
            $files = scandir($imagesDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'jpg') {
                    $availableCategories[] = pathinfo($file, PATHINFO_FILENAME);
                }
            }
        }

        $categories = array_values(array_intersect($categoriesFromData, $availableCategories));

        if (empty($categories)) {
            $categories = $availableCategories;
        }

        $canonicalUrl = canonicalUrl();
        $siteUrl = getSiteUrl();

        $jsonLd = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => 'ຄຳສອນພຸດທະ',
                'url' => $siteUrl,
                'description' => 'ເວັບໄຊທ໌ສຳລັບຮຽນຮູ້ພຣະສູດ ແລະ ຄຳສອນຂອງພຣະພຸດທະເຈົ້າ',
                'inLanguage' => 'lo',
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => absoluteUrl('/api/search') . '?keyword={search_term}',
                    ],
                    'query-input' => 'required name=search_term',
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => 'ພຣະສູດ - ຄຳສອນພຸດທະ',
                'description' => 'ລວມພຣະສູດ ແລະ ຄຳສອນຂອງພຣະພຸດທະເຈົ້າ',
                'url' => $canonicalUrl,
                'inLanguage' => 'lo',
                'isPartOf' => [
                    '@type' => 'WebSite',
                    'name' => 'ຄຳສອນພຸດທະ',
                    'url' => $siteUrl,
                ],
            ],
        ];

        return view('pages.sutra.index', [
            'categories' => $categories,
            'seo' => [
                'title' => 'ຄຳສອນພຸດທະ - ຮຽນຮູ້ພຣະສູດ ແລະ ທັມມະ',
                'description' => 'ເວັບໄຊທ໌ສຳລັບຮຽນຮູ້ພຣະສູດ ພຣະທັມ ຄຳສອນຂອງພຣະພຸດທະເຈົ້າ ພ້ອມທັງປຶ້ມ, ວິດີໂອ ແລະ ປະຕິທິນກິດຈະກຳ',
                'json_ld' => $jsonLd,
            ]
        ]);
    }

    public function category($category) {
        $sutras = Sutra::getAll();
        
        $filteredSutras = array_values(array_filter($sutras, function($item) use ($category) {
            return ($item['ໝວດທັມ'] ?? '') === $category;
        }));

        $canonicalUrl = canonicalUrl();
        $count = count($filteredSutras);

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $category . ' - ຄຳສອນພຸດທະ',
            'description' => 'ພຣະສູດໃນໝວດ ' . $category . ' ຈຳນວນ ' . $count . ' ພຣະສູດ',
            'url' => $canonicalUrl,
            'inLanguage' => 'lo',
            'about' => $category,
        ];

        return view('pages.sutra.category', [
            'category' => $category,
            'sutras' => $filteredSutras,
            'seo' => [
                'title' => $category . ' - ຄຳສອນພຸດທະ',
                'description' => 'ພຣະສູດໃນໝວດ ' . $category . ' ຈຳນວນ ' . $count . ' ພຣະສູດ',
                'keywords' => $category . ', ພຣະສູດ, ຄຳສອນພຸດທະ, ທັມ',
                'json_ld' => $jsonLd,
            ]
        ]);
    }

    public function show($id) {
        $sutras = Sutra::getAll();
        $sutra = null;
        
        foreach ($sutras as $item) {
            if ($item['ID'] == $id) {
                $sutra = $item;
                break;
            }
        }

        if (!$sutra) {
            $sutras = Sutra::getAll(true);
            foreach ($sutras as $item) {
                if ($item['ID'] == $id) {
                    $sutra = $item;
                    break;
                }
            }
        }

        if (!$sutra) {
            http_response_code(404);
            $sutra = [
                'ID' => $id,
                'ຊື່ພຣະສູດ' => 'ກຳລັງໂຫລດ...',
                'ພຣະສູດ' => 'ກຳລັງໂຫລດ...',
                'ໝວດທັມ' => '',
                'ສຽງ' => '',
                'ຮູບ' => ''
            ];
        }

        $prevID = null;
        $nextID = null;
        
        if (!empty($sutra['ໝວດທັມ'])) {
            $category = $sutra['ໝວດທັມ'];
            $categorySutras = array_values(array_filter($sutras, function($item) use ($category) {
                return ($item['ໝວດທັມ'] ?? '') === $category;
            }));

            foreach ($categorySutras as $index => $item) {
                if ($item['ID'] == $id) {
                    $prevID = $categorySutras[$index - 1]['ID'] ?? null;
                    $nextID = $categorySutras[$index + 1]['ID'] ?? null;
                    break;
                }
            }
        }

        $description = strip_tags($sutra['ພຣະສູດ'] ?? '');
        $description = mb_strlen($description) > 160 ? mb_substr($description, 0, strrpos(mb_substr($description, 0, 160), ' ') ?: 157) . '...' : $description;

        $canonicalUrl = canonicalUrl();
        $sutraTitle = $sutra['ຊື່ພຣະສູດ'] ?? 'ພຣະສູດ';
        $category = $sutra['ໝວດທັມ'] ?? '';

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $sutraTitle,
            'description' => $description,
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
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'ພຣະສູດ', 'item' => absoluteUrl('/sutra')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $sutraTitle],
                ],
            ],
        ];

        if ($category) {
            $jsonLd['about'] = $category;
            $jsonLd['keywords'] = $category . ', ພຣະສູດ, ຄຳສອນພຸດທະ, ທັມ';
            $jsonLd['articleSection'] = $category;
        } else {
            $jsonLd['keywords'] = 'ພຣະສູດ, ຄຳສອນພຸດທະ, ທັມ';
        }

        if (!empty($sutra['ຮູບ'])) {
            $jsonLd['image'] = $sutra['ຮູບ'];
        }

        $jsonLd['datePublished'] = $sutra['datePublished'] ?? date('Y-m-d');
        $jsonLd['author'] = [
            '@type' => 'Organization',
            'name' => 'ຄຳສອນພຸດທະ',
        ];

        return view('pages.sutra.show', [
            'sutra' => $sutra,
            'prevID' => $prevID,
            'nextID' => $nextID,
            'seo' => [
                'title' => $sutraTitle . ' - ຄຳສອນພຸດທະ',
                'description' => $description,
                'keywords' => ($category ? $category . ', ' : '') . 'ພຣະສູດ, ຄຳສອນພຸດທະ, ທັມ',
                'image' => !empty($sutra['ຮູບ']) ? $sutra['ຮູບ'] : absoluteUrl('assets/images/logo_shared.png'),
                'json_ld' => $jsonLd,
            ]
        ]);
    }
}

  
  