<?php
namespace App\Controllers;

class PrivacyController {
    public function index() {
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => 'Privacy Policy - ຄຳສອນພຸດທະ',
            'description' => 'Privacy Policy for ຄຳສອນພຸດທະ - information about data collection, usage, and your rights.',
            'url' => canonicalUrl(),
            'inLanguage' => 'en',
        ];

        return view('pages.privacy', [
            'seo' => [
                'title' => 'Privacy Policy - ຄຳສອນພຸດທະ',
                'description' => 'Privacy Policy for ຄຳສອນພຸດທະ - how we handle your data and protect your privacy.',
                'keywords' => 'privacy policy, data protection, buddhaword, privacy',
                'json_ld' => $jsonLd,
            ]
        ]);
    }
}
 