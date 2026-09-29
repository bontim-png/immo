<?php
return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'cleduslo_immo',
        'username' => 'cleduslo_immo_admin',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
    'app' => [
        'name' => 'IMMO SaaS',
        'env' => 'production',
        'debug' => false,
        'timezone' => 'Europe/Paris',
        'default_language' => 'fr',
        'supported_languages' => ['fr', 'en', 'nl'],
        'upload_max_size' => 12 * 1024 * 1024, // 12MB
        'upload_allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
    ],
];
