<?php
// Entry point - all requests go through here

// Error reporting - enable for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('html_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Load config FIRST - before anything else
$config = [];
$configPath = __DIR__ . '/../config/config.php';
if (file_exists($configPath)) {
    $config = require $configPath;
} else {
    die('Configuration file not found at: ' . $configPath);
}

// Set timezone
$timezone = $config['app']['timezone'] ?? 'UTC';
date_default_timezone_set($timezone);

// Start session
$sessionName = $config['auth']['session_name'] ?? 'immo_session';
session_name($sessionName);
session_set_cookie_params([
    'lifetime' => $config['auth']['cookie_lifetime'] ?? 86400,
    'path' => '/immobilier/public/',
    'domain' => '',
    'secure' => $config['auth']['cookie_secure'] ?? false,
    'httponly' => $config['auth']['cookie_httponly'] ?? true,
    'samesite' => $config['auth']['cookie_samesite'] ?? 'Lax',
]);
session_start();

// Set language from session or default
$supportedLanguages = $config['app']['supported_languages'] ?? ['fr', 'en', 'nl'];
$defaultLanguage = $config['app']['default_language'] ?? 'fr';
if (!isset($_SESSION['language']) || !in_array($_SESSION['language'], $supportedLanguages)) {
    $_SESSION['language'] = $defaultLanguage;
}

// Define base URL constant
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? '';
$basePath = dirname($_SERVER['SCRIPT_NAME'] ?? '/immobilier/public/');
define('BASE_URL', $protocol . '://' . $host . $basePath);

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

// Load helpers - functions.php uses global $config
require __DIR__ . '/../src/Helpers/functions.php';

// Initialize database
use App\Database\Connection;
use App\Database\Database;

try {
    $dbConfig = $config['database'];
    Connection::setConfig($dbConfig);
    Database::init();
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    
    if (!($config['app']['debug'] ?? false)) {
        http_response_code(503);
        include __DIR__ . '/errors/503.php';
        exit;
    }
    
    die('Database connection error: ' . $e->getMessage());
}

// Initialize router
use App\Router\Router;
use App\Router\Route;

$router = new Router();

// Register routes
$routesPath = __DIR__ . '/../src/Router/routes.php';
if (file_exists($routesPath)) {
    require $routesPath;
} else {
    die('Routes file not found at: ' . $routesPath);
}

// Handle request
try {
    $router->dispatch();
} catch (Exception $e) {
    error_log('Application error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    
    if (!($config['app']['debug'] ?? false)) {
        http_response_code(500);
        include __DIR__ . '/errors/500.php';
        exit;
    }
    
    echo '<h1>500 Internal Server Error</h1>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}
