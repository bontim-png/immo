<?php
declare(strict_types=1);

use App\Database;
use App\I18n;

$configFile = __DIR__ . '/../config/config.php';

if (!is_file($configFile)) {
    http_response_code(500);
    exit('Application configuration is missing.');
}

$config = require $configFile;

date_default_timezone_set($config['app']['timezone'] ?? 'Europe/Paris');

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

$db = Database::connection($config);
$auth = new App\Auth($db, $config);
$i18n = new I18n($db, $config);

// Backwards-compatible global translation helper used throughout Prrepl.
if (!function_exists('__t')) {
    function __t(string $key, ?string $fallback = null, array $replace = []): string
    {
        $translator = $GLOBALS['i18n'] ?? null;
        if ($translator instanceof \App\I18n) {
            return $translator->translate($key, $fallback, $replace);
        }

        return $fallback ?? $key;
    }
}

// Safe HTML helper for legacy/current PHP views that use e().
if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
