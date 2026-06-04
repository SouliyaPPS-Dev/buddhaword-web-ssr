<?php
namespace App\Controllers;

use App\Models\Product;
  
class HomeController {
    public function index() {
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'ຄຳສອນພຸດທະ',
            'url' => rtrim(canonicalUrl(), '/'),
            'description' => 'ເວັບໄຊທ໌ສຳລັບຮຽນຮູ້ຄຳສອນຂອງພຣະພຸດທະເຈົ້າ',
            'inLanguage' => 'lo',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => absoluteUrl('/api/search') . '?keyword={search_term}',
                'query-input' => 'required name=search_term',
            ],
        ];

        return view('pages.home', [
            'seo' => [
                'title' => 'ຍິນດີຕ້ອນຮັບສູ່ ຄຳສອນພຸດທະ',
                'description' => 'ເວັບໄຊທ໌ສຳລັບຮຽນຮູ້ຄຳສອນຂອງພຣະພຸດທະເຈົ້າ ແລະ ເລືອກຊື້ເຄື່ອງໄໝລາວ',
                'keywords' => 'ຄຳສອນພຸດທະ, ພຸດທະສາສະໜາ, ທັມມະ, ພຣະສູດ',
                'json_ld' => $jsonLd,
            ]
        ]);
    }
}

 