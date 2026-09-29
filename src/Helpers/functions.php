<?php
// Global helper functions

/**
 * Get configuration value using dot notation
 */
function config(string $key, $default = null)
{
    $keys = explode('.', $key);
    $config = require __DIR__ . '/../config/config.php';
    
    foreach ($keys as $k) {
        if (!isset($config[$k])) {
            return $default;
        }
        $config = $config[$k];
    }
    
    return $config;
}

/**
 * Get translation string
 */
function trans(string $key, array $params = []): string
{
    static $translations = [];
    
    $language = $_SESSION['language'] ?? config('app.default_language', 'fr');
    
    if (!isset($translations[$language])) {
        $file = __DIR__ . '/../../lang/' . $language . '.php';
        if (file_exists($file)) {
            $translations[$language] = require $file;
        } else {
            $translations[$language] = [];
        }
    }
    
    $translation = $translations[$language][$key] ?? $key;
    
    // Replace placeholders {key} with values from params
    foreach ($params as $placeholder => $value) {
        $translation = str_replace('{' . $placeholder . '}', $value, $translation);
    }
    
    return $translation;
}

/**
 * Get locale string from language code
 */
function getLocaleForLanguage(string $language): string
{
    $map = [
        'fr' => 'fr_FR',
        'en' => 'en_US',
        'nl' => 'nl_NL',
    ];
    return $map[$language] ?? $map[config('app.default_language', 'fr')] ?? 'en_US';
}

/**
 * Map format string to IntlDateFormatter date type
 */
function getIntlDateType(string $format): int
{
    $map = [
        'short' => IntlDateFormatter::SHORT,
        'medium' => IntlDateFormatter::MEDIUM,
        'long' => IntlDateFormatter::LONG,
        'full' => IntlDateFormatter::FULL,
    ];
    return $map[$format] ?? IntlDateFormatter::MEDIUM;
}

/**
 * Map format string to IntlDateFormatter time type
 */
function getIntlTimeType(string $format): int
{
    return IntlDateFormatter::NONE; // No time by default
}

/**
 * Format price according to locale
 */
function format_price(float $price, string $currency = 'EUR', string $language = null): string
{
    $language = $language ?? ($_SESSION['language'] ?? config('app.default_language', 'fr'));
    $locale = getLocaleForLanguage($language);
    
    $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
    return $formatter->formatCurrency($price, $currency);
}

/**
 * Format number according to locale
 */
function format_number(float $number, int $decimals = 0, string $language = null): string
{
    $language = $language ?? ($_SESSION['language'] ?? config('app.default_language', 'fr'));
    $locale = getLocaleForLanguage($language);
    
    $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
    $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);
    return $formatter->format($number);
}

/**
 * Format date according to locale
 */
function format_date(string $date, string $format = 'medium', string $language = null): string
{
    $language = $language ?? ($_SESSION['language'] ?? config('app.default_language', 'fr'));
    $locale = getLocaleForLanguage($language);
    
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }
    
    $intlFormatter = new IntlDateFormatter(
        $locale,
        getIntlDateType($format),
        getIntlTimeType($format),
        date_default_timezone_get(),
        IntlDateFormatter::GREGORIAN
    );
    
    return $intlFormatter->format($timestamp);
}

/**
 * Sanitize input
 */
function sanitize(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Generate a CSRF token
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validate_csrf(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect to URL
 */
function redirect(string $url, int $statusCode = 302): void
{
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Check if current request is AJAX
 */
function is_ajax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get current URL
 */
function current_url(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return $protocol . '://' . $host . $uri;
}

/**
 * Get base URL
 */
function base_url(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $basePath = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    return $protocol . '://' . $host . $basePath;
}

/**
 * Get asset URL
 */
function asset(string $path): string
{
    return base_url() . '/public/' . ltrim($path, '/');
}

/**
 * Generate CSRF input field
 */
function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Dump and die (for debugging)
 */
function dd($value): void
{
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
    exit;
}

/**
 * Generate URL for a named route
 */
function route(string $name, array $params = []): string
{
    static $router = null;
    if ($router === null) {
        $router = new \App\Router\Router();
        require __DIR__ . '/../Router/routes.php';
    }
    return $router->urlFor($name, $params);
}
