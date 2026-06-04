<?php
namespace App\Controllers;

class AboutController {
    public function index() {
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'AboutPage',
            'name' => 'ກ່ຽວກັບ - ຄຳສອນພຸດທະ',
            'description' => 'ຂໍ້ມູນກ່ຽວກັບ ຄຳສອນພຸດທະ ແລະ ຊ່ອງທາງການຕິດຕໍ',
            'url' => canonicalUrl(),
            'inLanguage' => 'lo',
        ];

        return view('pages.about', [
            'seo' => [
                'title' => 'ກ່ຽວກັບ - ຄຳສອນພຸດທະ',
                'description' => 'ຂໍ້ມູນກ່ຽວກັບ ຄຳສອນພຸດທະ ແລະ ຊ່ອງທາງການຕິດຕໍ',
                'keywords' => 'ກ່ຽວກັບ, ຕິດຕໍ່, ຄຳສອນພຸດທະ',
                'json_ld' => $jsonLd,
            ]
        ]);
    }
}
  