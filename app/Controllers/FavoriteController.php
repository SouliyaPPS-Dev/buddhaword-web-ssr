<?php
namespace App\Controllers;

class FavoriteController {
    public function index() {
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => 'ຖືກໃຈ - ຄຳສອນພຸດທະ',
            'description' => 'ລາຍການທີ່ທ່ານຖືກໃຈ',
            'url' => canonicalUrl(),
            'inLanguage' => 'lo',
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'ຄຳສອນພຸດທະ',
            ],
        ];

        return view('pages.favorites', [
            'seo' => [
                'title' => 'ຖືກໃຈ - ຄຳສອນພຸດທະ',
                'description' => 'ລາຍການທີ່ທ່ານຖືກໃຈ',
                'json_ld' => $jsonLd,
            ]
        ]);
    }
}
 