<?php
/**
 * IMMO SaaS - Diagnostic Tool
 * Access: https://prrepl.com/immobilier/public/diagnostic.php
 * 
 * This script tests each component of the application step by step
 * to identify what's not working.
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMMO Diagnostic Tool</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f8fafc;
            padding: 2rem;
            line-height: 1.6;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #1e293b; margin-bottom: 1.5rem; font-size: 1.875rem; }
        h2 { color: #334155; margin: 1.5rem 0 1rem; font-size: 1.25rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem; }
        .test-result {
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid transparent;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .test-result.pass { border-left-color: #10b981; background: #f0fdf4; }
        .test-result.fail { border-left-color: #ef4444; background: #fef2f2; }
        .test-result.warn { border-left-color: #f59e0b; background: #fef9c3; }
        .test-name { font-weight: 600; color: #1e293b; margin-bottom: 0.25rem; }
        .test-details { color: #64748b; font-size: 0.875rem; }
        .test-value { font-family: monospace; background: #e2e8f0; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.875rem; }
        .section { margin-bottom: 2rem; }
        .actions {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .actions h2 { border-bottom: none; margin-bottom: 1rem; }
        a {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            text-decoration: none;
            margin-right: 0.75rem;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            transition: background 0.2s;
        }
        a:hover { background: #1d4ed8; }
        a.secondary { background: #f1f5f9; color: #475569; }
        a.secondary:hover { background: #e2e8f0; }
        pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 0.375rem;
            overflow-x: auto;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        .php-version { font-family: monospace; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏥 IMMO SaaS Diagnostic Tool</h1>
        <p class="php-version">PHP Version: <?= PHP_VERSION ?></p>

<?php
// ============================================================
// TEST 1: Basic PHP Configuration
// ============================================================
$results = [];

echo '<div class="section">\n';
echo '<h2>⚙️ Step 1: Basic PHP Configuration</h2>\n';

// Test 1.1: Error display
$test = [
    'name' => 'Error Display Setting',
    'value' => ini_get('display_errors'),
    'expected' => '1 (for debugging) or 0 (for production)',
    'status' => 'pass'
];
if ($test['value'] === '1') {
    $test['message'] = 'Errors are shown - good for debugging';
} elseif ($test['value'] === '0') {
    $test['message'] = 'Errors are hidden - good for production';
} else {
    $test['message'] = 'Unexpected value';
}
$results[] = $test;
renderTest($test);

// Test 1.2: Error logging
$test = [
    'name' => 'Error Logging',
    'value' => ini_get('log_errors'),
    'expected' => '1',
    'status' => ini_get('log_errors') === '1' ? 'pass' : 'fail'
];
$test['message'] = $test['status'] === 'pass' ? 'Error logging is enabled' : 'Error logging is disabled';
$results[] = $test;
renderTest($test);

// Test 1.3: Session status
$test = [
    'name' => 'Session Status',
    'value' => session_status() === PHP_SESSION_NONE ? 'Not started' : 'Active',
    'expected' => 'Not started (will start later)',
    'status' => 'pass'
];
$test['message'] = 'Sessions are available';
$results[] = $test;
renderTest($test);

// Test 1.4: PDO extension
$test = [
    'name' => 'PDO MySQL Extension',
    'value' => extension_loaded('pdo_mysql') ? 'Loaded' : 'NOT LOADED',
    'expected' => 'Loaded',
    'status' => extension_loaded('pdo_mysql') ? 'pass' : 'fail'
];
$test['message'] = $test['status'] === 'pass' ? 'PDO MySQL is available' : 'PDO MySQL is NOT installed - database will not work';
$results[] = $test;
renderTest($test);

// Test 1.5: Intl extension (for i18n)
$test = [
    'name' => 'Intl Extension',
    'value' => extension_loaded('intl') ? 'Loaded' : 'NOT LOADED',
    'expected' => 'Loaded',
    'status' => extension_loaded('intl') ? 'pass' : 'warn'
];
$test['message'] = $test['status'] === 'pass' ? 'Intl is available for translations' : 'Intl not available - translations may not work';
$results[] = $test;
renderTest($test);

echo '</div>\n';

// ============================================================
// TEST 2: File System
// ============================================================
echo '<div class="section">\n';
echo '<h2>📁 Step 2: File System Checks</h2>\n';

$basePath = __DIR__ . '/..';

// Test 2.1: Config file exists
$configPath = $basePath . '/config/config.php';
$test = [
    'name' => 'Config File Exists',
    'value' => file_exists($configPath) ? 'Yes' : 'No',
    'expected' => 'Yes',
    'status' => file_exists($configPath) ? 'pass' : 'fail'
];
$test['message'] = $test['status'] === 'pass' ? 'Config file found' : 'Config file NOT FOUND at: ' . $configPath;
$results[] = $test;
renderTest($test);

// Test 2.2: Config file is readable
if (file_exists($configPath)) {
    $test = [
        'name' => 'Config File Readable',
        'value' => is_readable($configPath) ? 'Yes' : 'No',
        'expected' => 'Yes',
        'status' => is_readable($configPath) ? 'pass' : 'fail'
    ];
    $test['message'] = $test['status'] === 'pass' ? 'Config file is readable' : 'Config file NOT readable - check permissions';
    $results[] = $test;
    renderTest($test);
    
    // Test 2.3: Config file loads
    try {
        $config = require $configPath;
        $test = [
            'name' => 'Config File Loads',
            'value' => is_array($config) ? 'Yes' : 'No',
            'expected' => 'Yes',
            'status' => is_array($config) ? 'pass' : 'fail'
        ];
        $test['message'] = $test['status'] === 'pass' ? 'Config loaded successfully' : 'Config did not return an array';
        $results[] = $test;
        renderTest($test);
        
        // Test 2.4: Database config exists
        $test = [
            'name' => 'Database Config',
            'value' => isset($config['database']) ? 'Yes' : 'No',
            'expected' => 'Yes',
            'status' => isset($config['database']) ? 'pass' : 'fail'
        ];
        $test['message'] = $test['status'] === 'pass' ? 'Database config exists' : 'Database config missing from config.php';
        $results[] = $test;
        renderTest($test);
        
        // Test 2.5: Auth config exists
        $test = [
            'name' => 'Auth Config',
            'value' => isset($config['auth']) ? 'Yes' : 'No',
            'expected' => 'Yes',
            'status' => isset($config['auth']) ? 'pass' : 'fail'
        ];
        $test['message'] = $test['status'] === 'pass' ? 'Auth config exists' : 'Auth config missing from config.php';
        $results[] = $test;
        renderTest($test);
    } catch (Exception $e) {
        $test = [
            'name' => 'Config File Loads',
            'value' => 'Error',
            'expected' => 'Yes',
            'status' => 'fail',
            'message' => 'Config file threw error: ' . $e->getMessage()
        ];
        $results[] = $test;
        renderTest($test);
    }
}

// Test 2.6: Source directory exists
$srcPath = $basePath . '/src';
$test = [
    'name' => 'Source Directory Exists',
    'value' => file_exists($srcPath) ? 'Yes' : 'No',
    'expected' => 'Yes',
    'status' => file_exists($srcPath) ? 'pass' : 'fail'
];
$test['message'] = $test['status'] === 'pass' ? 'Source directory found' : 'Source directory NOT FOUND';
$results[] = $test;
renderTest($test);

// Test 2.7: Helpers file exists
$helpersPath = $basePath . '/src/Helpers/functions.php';
$test = [
    'name' => 'Helpers File Exists',
    'value' => file_exists($helpersPath) ? 'Yes' : 'No',
    'expected' => 'Yes',
    'status' => file_exists($helpersPath) ? 'pass' : 'fail'
];
$test['message'] = $test['status'] === 'pass' ? 'Helpers file found' : 'Helpers file NOT FOUND';
$results[] = $test;
renderTest($test);

// Test 2.8: Views directory exists
$viewsPath = $basePath . '/views';
$test = [
    'name' => 'Views Directory Exists',
    'value' => file_exists($viewsPath) ? 'Yes' : 'No',
    'expected' => 'Yes',
    'status' => file_exists($viewsPath) ? 'pass' : 'fail'
];
$test['message'] = $test['status'] === 'pass' ? 'Views directory found' : 'Views directory NOT FOUND';
$results[] = $test;
renderTest($test);

echo '</div>\n';

// ============================================================
// TEST 3: Database Connection
// ============================================================
echo '<div class="section">\n';
echo '<h2>🗄️ Step 3: Database Connection</h2>\n';

if (isset($config) && isset($config['database'])) {
    $dbConfig = $config['database'];
    
    // Test 3.1: Database credentials
    $test = [
        'name' => 'Database Credentials',
        'value' => 'host=' . ($dbConfig['host'] ?? 'N/A') . ', dbname=' . ($dbConfig['dbname'] ?? 'N/A'),
        'expected' => 'Valid credentials',
        'status' => !empty($dbConfig['host']) && !empty($dbConfig['dbname']) ? 'pass' : 'fail'
    ];
    $test['message'] = $test['status'] === 'pass' ? 'Database credentials present' : 'Database credentials missing';
    $results[] = $test;
    renderTest($test);
    
    // Test 3.2: Try to connect
    try {
        $dsn = 'mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['dbname'] . ';charset=' . ($dbConfig['charset'] ?? 'utf8mb4');
        $pdo = new PDO(
            $dsn,
            $dbConfig['username'] ?? '',
            $dbConfig['password'] ?? '',
            $dbConfig['options'] ?? []
        );
        
        $test = [
            'name' => 'Database Connection',
            'value' => 'Connected',
            'expected' => 'Connected',
            'status' => 'pass'
        ];
        $test['message'] = 'Successfully connected to database';
        $results[] = $test;
        renderTest($test);
        
        // Test 3.3: Check tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $test = [
            'name' => 'Database Tables',
            'value' => implode(', ', $tables),
            'expected' => 'offices, agents, properties, property_photos',
            'status' => in_array('offices', $tables) && in_array('agents', $tables) ? 'pass' : 'fail'
        ];
        $test['message'] = count($tables) > 0 ? 'Found ' . count($tables) . ' tables' : 'No tables found';
        $results[] = $test;
        renderTest($test);
        
        // Test 3.4: Check agents table has data
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM agents");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            $test = [
                'name' => 'Agents in Database',
                'value' => $count,
                'expected' => '>= 2',
                'status' => $count >= 2 ? 'pass' : 'warn'
            ];
            $test['message'] = $count >= 2 ? 'Found ' . $count . ' agents (seed data present)' : 'Found ' . $count . ' agents (seed data may be missing)';
            $results[] = $test;
            renderTest($test);
            
            // Test 3.5: Check admin user exists
            $stmt = $pdo->prepare("SELECT email FROM agents WHERE email = ?");
            $stmt->execute(['admin@demo-immo.com']);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            $test = [
                'name' => 'Admin User Exists',
                'value' => $admin ? 'Yes (' . $admin['email'] . ')' : 'No',
                'expected' => 'Yes',
                'status' => $admin ? 'pass' : 'fail'
            ];
            $test['message'] = $admin ? 'Admin user found' : 'Admin user NOT found - cannot login';
            $results[] = $test;
            renderTest($test);
        } catch (Exception $e) {
            $test = [
                'name' => 'Query Agents Table',
                'value' => 'Error',
                'expected' => 'Success',
                'status' => 'fail',
                'message' => 'Query failed: ' . $e->getMessage()
            ];
            $results[] = $test;
            renderTest($test);
        }
        
    } catch (PDOException $e) {
        $test = [
            'name' => 'Database Connection',
            'value' => 'Failed',
            'expected' => 'Connected',
            'status' => 'fail',
            'message' => 'Connection error: ' . $e->getMessage()
        ];
        $results[] = $test;
        renderTest($test);
    }
} else {
    $test = [
        'name' => 'Database Configuration',
        'value' => 'Not available',
        'expected' => 'Available',
        'status' => 'fail',
        'message' => 'Cannot test database - config not loaded';
    ];
    $results[] = $test;
    renderTest($test);
}

echo '</div>\n';

// ============================================================
// TEST 4: Autoloader
// ============================================================
echo '<div class="section">\n';
echo '<h2>📦 Step 4: Autoloader Test</h2>\n';

// Test autoloader manually
try {
    spl_autoload_register(function ($class) use ($basePath) {
        $prefix = 'App\\';
        $baseDir = $basePath . '/src/';
        
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
    
    // Test 4.1: Load Router class
    if (class_exists('App\Router\Router')) {
        $test = [
            'name' => 'Router Class',
            'value' => 'Loaded',
            'expected' => 'Loaded',
            'status' => 'pass',
            'message' => 'Router class autoloaded successfully'
        ];
    } else {
        $routerPath = $basePath . '/src/Router/Router.php';
        if (file_exists($routerPath)) {
            require $routerPath;
            $test = [
                'name' => 'Router Class',
                'value' => 'Loaded (manual)',
                'expected' => 'Loaded',
                'status' => 'pass',
                'message' => 'Router class loaded manually'
            ];
        } else {
            $test = [
                'name' => 'Router Class',
                'value' => 'Not found',
                'expected' => 'Loaded',
                'status' => 'fail',
                'message' => 'Router class file not found'
            ];
        }
    }
    $results[] = $test;
    renderTest($test);
    
    // Test 4.2: Load Database class
    if (class_exists('App\Database\Database')) {
        $test = [
            'name' => 'Database Class',
            'value' => 'Loaded',
            'expected' => 'Loaded',
            'status' => 'pass',
            'message' => 'Database class autoloaded successfully'
        ];
    } else {
        $dbPath = $basePath . '/src/Database/Database.php';
        if (file_exists($dbPath)) {
            require $dbPath;
            $test = [
                'name' => 'Database Class',
                'value' => 'Loaded (manual)',
                'expected' => 'Loaded',
                'status' => 'pass',
                'message' => 'Database class loaded manually'
            ];
        } else {
            $test = [
                'name' => 'Database Class',
                'value' => 'Not found',
                'expected' => 'Loaded',
                'status' => 'fail',
                'message' => 'Database class file not found'
            ];
        }
    }
    $results[] = $test;
    renderTest($test);
    
} catch (Exception $e) {
    $test = [
        'name' => 'Autoloader',
        'value' => 'Error',
        'expected' => 'Working',
        'status' => 'fail',
        'message' => 'Autoloader error: ' . $e->getMessage()
    ];
    $results[] = $test;
    renderTest($test);
}

echo '</div>\n';

// ============================================================
// TEST 5: Helpers
// ============================================================
echo '<div class="section">\n';
echo '<h2>🛠️ Step 5: Helper Functions</h2>\n';

if (file_exists($basePath . '/src/Helpers/functions.php')) {
    require $basePath . '/src/Helpers/functions.php';
    
    // Test 5.1: config() function
    if (function_exists('config')) {
        try {
            $debug = config('app.debug');
            $test = [
                'name' => 'config() function',
                'value' => is_bool($debug) ? ($debug ? 'true' : 'false') : 'N/A',
                'expected' => 'Working',
                'status' => 'pass',
                'message' => 'config() function works'
            ];
        } catch (Exception $e) {
            $test = [
                'name' => 'config() function',
                'value' => 'Error',
                'expected' => 'Working',
                'status' => 'fail',
                'message' => 'config() error: ' . $e->getMessage()
            ];
        }
    } else {
        $test = [
            'name' => 'config() function',
            'value' => 'Not found',
            'expected' => 'Working',
            'status' => 'fail',
            'message' => 'config() function not defined'
        ];
    }
    $results[] = $test;
    renderTest($test);
    
    // Test 5.2: trans() function
    if (function_exists('trans')) {
        try {
            $value = trans('login');
            $test = [
                'name' => 'trans() function',
                'value' => $value,
                'expected' => 'String',
                'status' => is_string($value) ? 'pass' : 'fail',
                'message' => is_string($value) ? 'Translation function works' : 'Translation returned non-string'
            ];
        } catch (Exception $e) {
            $test = [
                'name' => 'trans() function',
                'value' => 'Error',
                'expected' => 'String',
                'status' => 'fail',
                'message' => 'trans() error: ' . $e->getMessage()
            ];
        }
    } else {
        $test = [
            'name' => 'trans() function',
            'value' => 'Not found',
            'expected' => 'String',
            'status' => 'fail',
            'message' => 'trans() function not defined'
        ];
    }
    $results[] = $test;
    renderTest($test);
    
    // Test 5.3: base_url() function
    if (function_exists('base_url')) {
        try {
            $url = base_url();
            $test = [
                'name' => 'base_url() function',
                'value' => $url,
                'expected' => 'URL',
                'status' => strpos($url, 'prrepl.com') !== false ? 'pass' : 'warn',
                'message' => 'Base URL: ' . $url
            ];
        } catch (Exception $e) {
            $test = [
                'name' => 'base_url() function',
                'value' => 'Error',
                'expected' => 'URL',
                'status' => 'fail',
                'message' => 'base_url() error: ' . $e->getMessage()
            ];
        }
    } else {
        $test = [
            'name' => 'base_url() function',
            'value' => 'Not found',
            'expected' => 'URL',
            'status' => 'fail',
            'message' => 'base_url() function not defined'
        ];
    }
    $results[] = $test;
    renderTest($test);
} else {
    $test = [
        'name' => 'Helper Functions',
        'value' => 'File not loaded',
        'expected' => 'Loaded',
        'status' => 'fail',
        'message' => 'Cannot test helpers - file not found'
    ];
    $results[] = $test;
    renderTest($test);
}

echo '</div>\n';

// ============================================================
// TEST 6: Full Application Test
// ============================================================
echo '<div class="section">\n';
echo '<h2>🎯 Step 6: Full Application Test</h2>\n';

// Test 6.1: Try to instantiate Router
if (class_exists('App\Router\Router')) {
    try {
        $router = new \App\Router\Router();
        $test = [
            'name' => 'Router Instantiation',
            'value' => 'Success',
            'expected' => 'Success',
            'status' => 'pass',
            'message' => 'Router instantiated successfully'
        ];
    } catch (Exception $e) {
        $test = [
            'name' => 'Router Instantiation',
            'value' => 'Error',
            'expected' => 'Success',
            'status' => 'fail',
            'message' => 'Router error: ' . $e->getMessage()
        ];
    }
} else {
    $test = [
        'name' => 'Router Instantiation',
        'value' => 'Class not found',
        'expected' => 'Success',
        'status' => 'fail',
        'message' => 'Router class not available'
    ];
}
$results[] = $test;
renderTest($test);

// Test 6.2: Try to load routes
$routesPath = $basePath . '/src/Router/routes.php';
$test = [
    'name' => 'Routes File',
    'value' => file_exists($routesPath) ? 'Exists' : 'Missing',
    'expected' => 'Exists',
    'status' => file_exists($routesPath) ? 'pass' : 'fail'
];
$test['message'] = $test['status'] === 'pass' ? 'Routes file found' : 'Routes file NOT FOUND';
$results[] = $test;
renderTest($test);

echo '</div>\n';

// ============================================================
// SUMMARY
// ============================================================
echo '<div class="section">\n';
echo '<h2>📊 Summary</h2>\n';

$passCount = count(array_filter($results, fn($r) => $r['status'] === 'pass'));
$failCount = count(array_filter($results, fn($r) => $r['status'] === 'fail'));
$warnCount = count(array_filter($results, fn($r) => $r['status'] === 'warn'));
$totalCount = count($results);

echo '<div class="test-result pass">\n';
echo '    <div class="test-name">Passed: ' . $passCount . '/' . $totalCount . '</div>\n';
echo '</div>\n';

echo '<div class="test-result fail">\n';
echo '    <div class="test-name">Failed: ' . $failCount . '/' . $totalCount . '</div>\n';
echo '</div>\n';

echo '<div class="test-result warn">\n';
echo '    <div class="test-name">Warnings: ' . $warnCount . '/' . $totalCount . '</div>\n';
echo '</div>\n';

echo '</div>\n';

// ============================================================
// ACTIONS
// ============================================================
echo '<div class="actions">\n';
echo '<h2>🔧 Quick Actions</h2>\n';
echo '<p style="margin-bottom: 1rem; color: #64748b;">Try these URLs to test the application:</p>\n';
echo '<a href="/immobilier/public/login">🔐 Login Page</a>\n';
echo '<a href="/immobilier/public/" class="secondary">🏠 Home</a>\n';
echo '<a href="/immobilier/public/dashboard" class="secondary">📊 Dashboard</a>\n';
echo '<a href="diagnostic.php" class="secondary">🔄 Refresh Diagnostic</a>\n';
echo '</div>\n';

// ============================================================
// HELPER FUNCTION
// ============================================================
function renderTest(array $test): void {
    $status = $test['status'];
    echo '<div class="test-result ' . $status . '">\n';
    echo '    <div class="test-name">' . htmlspecialchars($test['name']) . '</div>\n';
    if (isset($test['value'])) {
        echo '    <div class="test-details">Value: <span class="test-value">' . htmlspecialchars($test['value']) . '</span></div>\n';
    }
    if (isset($test['expected'])) {
        echo '    <div class="test-details">Expected: <span class="test-value">' . htmlspecialchars($test['expected']) . '</span></div>\n';
    }
    echo '    <div class="test-details">' . htmlspecialchars($test['message']) . '</div>\n';
    echo '</div>\n';
}

?>
</body>
</html>
