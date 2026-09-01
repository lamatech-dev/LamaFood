<?php

return [
    'blocks' => [
        'hero' => ['structure' => ['mediaId' => 'integer?', 'variant' => 'string?', 'alignment' => 'string?', 'ctaTarget' => 'string?'], 'content' => ['eyebrow' => 'string?', 'title' => 'string', 'body' => 'string?', 'ctaLabel' => 'string?']],
        'about' => ['structure' => ['mediaIds' => 'integer[]?', 'variant' => 'string?'], 'content' => ['heading' => 'string', 'body' => 'string']],
        'gallery' => ['structure' => ['mediaIds' => 'integer[]', 'layout' => 'string?'], 'content' => ['heading' => 'string?', 'caption' => 'string?']],
        'menu_preview' => ['structure' => ['categoryIds' => 'integer[]?', 'itemLimit' => 'integer?', 'variant' => 'string?'], 'content' => ['heading' => 'string', 'intro' => 'string?', 'ctaLabel' => 'string']],
        'location' => ['structure' => ['lat' => 'numeric', 'lng' => 'numeric', 'mapUrl' => 'url?', 'variant' => 'string?'], 'content' => ['heading' => 'string', 'address' => 'string', 'directionsLabel' => 'string?']],
        'contact' => ['structure' => ['phone' => 'string?', 'instagramUrl' => 'url?', 'variant' => 'string?'], 'content' => ['heading' => 'string', 'body' => 'string?', 'phoneLabel' => 'string?', 'instagramLabel' => 'string?']],
        'cta' => ['structure' => ['target' => 'string', 'variant' => 'string?', 'style' => 'string?'], 'content' => ['heading' => 'string', 'body' => 'string?', 'label' => 'string']],
        'footer' => ['structure' => ['logoMediaId' => 'integer?', 'socialUrls' => 'url[]?'], 'content' => ['tagline' => 'string?', 'copyrightText' => 'string?']],
    ],
];
