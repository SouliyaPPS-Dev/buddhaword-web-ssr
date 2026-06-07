<?php
namespace App\Controllers;

use App\Models\Calendar;

class CalendarController {
    public function index() {
        $events = Calendar::getAll();

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'ປະຕິທິນ - ຄຳສອນພຸດທະ',
            'description' => 'ປະຕິທິນກິດຈະກຳ ແລະ ວັນສຳຄັນຕ່າງໆ',
            'url' => canonicalUrl(),
            'inLanguage' => 'lo',
        ];

        return view('pages.calendar.index', [
            'events' => $events,
            'seo' => [
                'title' => 'ປະຕິທິນ - ຄຳສອນພຸດທະ',
                'description' => 'ປະຕິທິນກິດຈະກຳ ແລະ ວັນສຳຄັນຕ່າງໆ',
                'keywords' => 'ປະຕິທິນ, ກິດຈະກຳ, ວັນສຳຄັນ, ຄຳສອນພຸດທະ',
                'json_ld' => $jsonLd,
            ]
        ]);
    }

    public function show($id) {
        $event = Calendar::getById($id);

        if (!$event) {
            header("Location: " . url('/calendar'));
            exit;
        }

        $canonicalUrl = canonicalUrl();
        $eventTitle = $event['title'] ?? 'ປະຕິທິນ';
        $description = mb_substr(strip_tags($event['details'] ?? ''), 0, 160);

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $eventTitle,
            'description' => $description,
            'url' => $canonicalUrl,
            'inLanguage' => 'lo',
            'breadcrumb' => [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'ໜ້າຫຼັກ', 'item' => absoluteUrl('')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'ປະຕິທິນ', 'item' => absoluteUrl('/calendar')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $eventTitle],
                ],
            ],
        ];

        if (!empty($event['poster'])) {
            $jsonLd['image'] = $event['poster'];
        }

        if (!empty($event['startDateISO'])) {
            $jsonLd['startDate'] = $event['startDateISO'];
        }
        if (!empty($event['endDateISO'])) {
            $jsonLd['endDate'] = $event['endDateISO'];
        }

        return view('pages.calendar.show', [
            'event' => $event,
            'seo' => [
                'title' => $eventTitle . ' - ຄຳສອນພຸດທະ',
                'description' => $description,
                'image' => $event['poster'] ?? '',
                'keywords' => 'ປະຕິທິນ, ກິດຈະກຳ, ວັນສຳຄັນ, ຄຳສອນພຸດທະ',
                'json_ld' => $jsonLd,
                'og_type' => 'event',
            ]
        ]);
    }
}

 