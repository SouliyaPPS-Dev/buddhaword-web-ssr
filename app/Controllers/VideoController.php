<?php
namespace App\Controllers;

use App\Models\Video;

class VideoController {
    public function index() {
        $videos = array_reverse(Video::getAll());
 
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'ວິດີໂອ - ຄຳສອນພຸດທະ',
            'description' => 'ລວມວິດີໂອທັມມະ ແລະ ຄຳສອນຂອງພຣະພຸດທະເຈົ້າ',
            'url' => canonicalUrl(),
            'inLanguage' => 'lo',
        ];

        return view('pages.video.index', [
            'videos' => $videos,
            'seo' => [
                'title' => 'ວິດີໂອ - ຄຳສອນພຸດທະ',
                'description' => 'ລວມວິດີໂອທັມມະ ແລະ ຄຳສອນຂອງພຣະພຸດທະເຈົ້າ',
                'keywords' => 'ວິດີໂອ, ທັມມະ, ຄຳສອນພຸດທະ, ພຸດທະສາສະໜາ',
                'json_ld' => $jsonLd,
            ]
        ]);
    }

    public function show($id) {
        $videos = Video::getAll();
        $video = null;
        $ytId = null;

        foreach ($videos as $item) {
            $itemId = $item['ID'] ?? '';
            $link = $item['link'] ?? '';
            preg_match('/(?:youtu\.be\/|youtube\.com\/(?:.*v=|.*\/|.*embed\/|.*shorts\/))([\w-]+)/', $link, $matches);
            $foundYtId = $matches[1] ?? null;
            if ($itemId === $id || $foundYtId === $id) {
                $video = $item;
                $ytId = $foundYtId;
                break;
            }
        }

        if (!$video) {
            header("HTTP/1.0 404 Not Found");
            echo "Video not found";
            return;
        }

        $otherVideos = array_filter($videos, function($item) use ($ytId) {
            $link = $item['link'] ?? '';
            preg_match('/(?:youtu\.be\/|youtube\.com\/(?:.*v=|.*\/|.*embed\/|.*shorts\/))([\w-]+)/', $link, $m);
            $found = $m[1] ?? null;
            return $found && $found !== $ytId;
        });

        $canonicalUrl = canonicalUrl();
        $videoTitle = $video['ຊື່ພຣະສູດ'] ?? 'Video';

        $thumbnailUrl = 'https://img.youtube.com/vi/' . $ytId . '/maxresdefault.jpg';

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $videoTitle,
            'description' => 'ວິດີໂອ: ' . $videoTitle,
            'url' => $canonicalUrl,
            'inLanguage' => 'lo',
            'embedUrl' => 'https://www.youtube.com/embed/' . $ytId,
            'thumbnailUrl' => $thumbnailUrl,
            'isPartOf' => [
                '@type' => 'Collection',
                'name' => 'ຄຳສອນພຸດທະ',
            ], 
            'breadcrumb' => [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'ໜ້າຫຼັກ', 'item' => absoluteUrl('')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'ວິດີໂອ', 'item' => absoluteUrl('/video')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $videoTitle],
                ],
            ],
        ];

        $videoDescription = strip_tags($video['description'] ?? $video['detail'] ?? '');
        $videoDescription = !empty($videoDescription)
            ? (mb_strlen($videoDescription) > 160 ? mb_substr($videoDescription, 0, 157) . '...' : $videoDescription)
            : 'ວິດີໂອ: ' . $videoTitle;

        return view('pages.video.show', [
            'video' => $video,
            'ytId' => $ytId,
            'otherVideos' => array_values($otherVideos),
            'seo' => [
                'title' => $videoTitle . ' - ຄຳສອນພຸດທະ',
                'description' => $videoDescription,
                'keywords' => ($video['ໝວດທັມ'] ?? '') . ', ວິດີໂອ, ທັມມະ, ຄຳສອນພຸດທະ',
                'image' => $thumbnailUrl,
                'json_ld' => $jsonLd,
                'og_type' => 'video.other',
                'video_published_time' => $video['datePublished'] ?? date('Y-m-d'),
            ]
        ]);
    }
}

 