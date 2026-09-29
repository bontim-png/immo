<?php
// Entry point - all requests go through here

// Error reporting based on environment
$config = require __DIR__ . '/../config/config.php';

error_reporting(E_ALL);
ini_set('display_errors', ($config['app']['debug'] ?? false) ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Set timezone
date_default_timezone_set($config['app']['timezone'] ?? 'UTC');

// Start session
session_name($config['auth']['session_name'] ?? 'immo_session');
session_set_cookie_params([
    'lifetime' => $config['auth']['cookie_lifetime'] ?? 86400,
    'secure' => $config['auth']['cookie_secure'] ?? true,
    'httponly' => $config['auth']['cookie_httponly'] ?? true,
    'samesite' => $config['auth']['cookie_samesite'] ?? 'Lax',
]);
session_start();

// Set language from session or default
if (!isset($_SESSION['language']) || !in_array($_SESSION['language'], ($config['app']['supported_languages'] ?? ['fr', 'en', 'nl']))) {
    $_SESSION['language'] = $config['app']['default_language'] ?? 'fr';
}

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load helpers
require __DIR__ . '/../src/Helpers/functions.php';

// Initialize database
use App\Database\Database;
use App\Database\Connection;

try {
    $dbConfig = $config['database'];
    Connection::setConfig($dbConfig);
    Database::init();
} catch (PDOException $e) {
    // Log connection error
    error_log('Database connection failed: ' . $e->getMessage());
    
    // Show user-friendly error in production
    if (!($config['app']['debug'] ?? false)) {
        http_response_code(503);
        include __DIR__ . '/errors/503.php';
        exit;
    }
    
    // Show detailed error in development
    die('Database connection error: ' . $e->getMessage());
}

// Initialize router
use App\Router\Router;
use App\Router\Route;

$router = new Router();

// Register routes
require __DIR__ . '/../src/Router/routes.php';

// Handle request
try {
    $router->dispatch();
} catch (Exception $e) {
    // Log the error
    error_log('Application error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    
    // Show user-friendly error
    if (!($config['app']['debug'] ?? false)) {
        http_response_code(500);
        include __DIR__ . '/errors/500.php';
        exit;
    }
    
    // Show detailed error in development
    echo '<h1>500 Internal Server Error</h1>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}
