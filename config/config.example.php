<?php
return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'immo',
        'username' => 'root',
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
        'env' => 'development',
        'debug' => true,
        'timezone' => 'Europe/Paris',
        'default_language' => 'fr',
        'supported_languages' => ['fr', 'en', 'nl'],
        'upload_max_size' => 12 * 1024 * 1024, // 12MB
        'upload_allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
        'session_lifetime' => 3600, // 1 hour
    ],
    'auth' => [
        'session_name' => 'immo_session',
        'cookie_lifetime' => 86400, // 24 hours
        'cookie_secure' => false, // Set to true in production with HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ],
];
