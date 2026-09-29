<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'IMMO',
        'env' => 'production',
        'debug' => false,
        'base_url' => '',
        'default_locale' => 'fr',
        'supported_locales' => ['fr', 'en', 'nl'],
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'immo',
        'user' => 'immo_user',
        'password' => 'CHANGE_ME',
        'charset' => 'utf8mb4',
    ],
    'uploads' => [
        'property_max_bytes' => 12 * 1024 * 1024,
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
    ],
];
