<?php

return [
    'default' => env('APP_LOCALE', 'fa'),

    'public_fallback' => null,

    'locales' => [
        'fa' => [
            'name' => 'Persian',
            'native_name' => 'فارسی',
            'direction' => 'rtl',
            'required_for_publication' => true,
        ],
        'en' => [
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'required_for_publication' => true,
        ],
        'ar' => [
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'direction' => 'rtl',
            'required_for_publication' => true,
        ],
    ],
];
