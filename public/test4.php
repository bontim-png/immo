<?php
/**
 * IMMO - Minimal Entry Point Test
 * Test if the basic entry point works
 */
header('Content-Type: text/plain');

echo "Test 4: Minimal Entry Point\n";
echo "============================\n\n";

// Step 1: Load config
$configPath = __DIR__ . '/../config/config.php';
echo "1. Loading config...\n";
if (!file_exists($configPath)) {
    echo "   FAIL: Config not found\n";
    exit;
}
$config = require $configPath;
echo "   PASS\n\n";

// Step 2: Error reporting
echo "2. Setting error reporting...\n";
error_reporting(E_ALL);
ini_set('display_errors', '1');
echo "   PASS\n\n";

// Step 3: Timezone
echo "3. Setting timezone...\n";
date_default_timezone_set($config['app']['timezone'] ?? 'UTC');
echo "   PASS\n\n";

// Step 4: Session
echo "4. Starting session...\n";
session_name($config['auth']['session_name'] ?? 'immo_session');
session_set_cookie_params([
    'lifetime' => $config['auth']['cookie_lifetime'] ?? 86400,
    'secure' => $config['auth']['cookie_secure'] ?? false,
    'httponly' => $config['auth']['cookie_httponly'] ?? true,
    'samesite' => $config['auth']['cookie_samesite'] ?? 'Lax',
]);
session_start();
echo "   PASS\n\n";

// Step 5: Autoloader
echo "5. Setting up autoloader...\n";
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
echo "   PASS\n\n";

// Step 6: Load helpers
echo "6. Loading helpers...\n";
$helpersPath = __DIR__ . '/../src/Helpers/functions.php';
if (!file_exists($helpersPath)) {
    echo "   FAIL: Helpers not found at $helpersPath\n";
    exit;
}
require $helpersPath;
echo "   PASS\n\n";

// Step 7: Test config() function
echo "7. Testing config() function...\n";
if (!function_exists('config')) {
    echo "   FAIL: config() function not defined\n";
    exit;
}
try {
    $debug = config('app.debug');
    echo "   PASS (debug = " . ($debug ? 'true' : 'false') . ")\n\n";
} catch (Exception $e) {
    echo "   FAIL: " . $e->getMessage() . "\n";
    exit;
}

// Step 8: Database connection
echo "8. Testing database connection...\n";
use App\Database\Connection;
use App\Database\Database;

try {
    Connection::setConfig($config['database']);
    Database::init();
    echo "   PASS\n\n";
} catch (Exception $e) {
    echo "   FAIL: " . $e->getMessage() . "\n";
    exit;
}

// Step 9: Router
echo "9. Testing router...\n";
use App\Router\Router;

try {
    $router = new Router();
    echo "   PASS\n\n";
} catch (Exception $e) {
    echo "   FAIL: " . $e->getMessage() . "\n";
    exit;
}

// Step 10: Routes
echo "10. Loading routes...\n";
$routesPath = __DIR__ . '/../src/Router/routes.php';
if (!file_exists($routesPath)) {
    echo "   FAIL: Routes not found\n";
    exit;
}
require $routesPath;
echo "   PASS\n\n";

echo "============================\n";
echo "ALL TESTS PASSED!\n";
echo "\nThe application should work.\n";
echo "Try: /immobilier/public/login\n";
