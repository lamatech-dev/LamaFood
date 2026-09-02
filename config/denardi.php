<?php

return [
    'business_slug' => env('DENARDI_BUSINESS_SLUG', 'denardi'),
    'brand' => 'Denardi',
    'instagram_url' => env('DENARDI_INSTAGRAM_URL'),
    'map_url' => env('DENARDI_MAP_URL'),
    'phone' => env('DENARDI_PHONE'),
    'schema' => [
        'type' => env('DENARDI_SCHEMA_TYPE', 'CafeOrCoffeeShop'),
        'logo_url' => env('DENARDI_SCHEMA_LOGO_URL'),
        'image_url' => env('DENARDI_SCHEMA_IMAGE_URL'),
        'address' => env('DENARDI_SCHEMA_ADDRESS'),
        'latitude' => env('DENARDI_SCHEMA_LATITUDE'),
        'longitude' => env('DENARDI_SCHEMA_LONGITUDE'),
        'opening_hours' => array_values(array_filter(array_map('trim', explode(',', (string) env('DENARDI_SCHEMA_OPENING_HOURS', ''))))),
        'price_range' => env('DENARDI_SCHEMA_PRICE_RANGE'),
    ],
];
