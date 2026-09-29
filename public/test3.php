<?php
/**
 * IMMO - Database Test
 * Test database connection
 */
header('Content-Type: text/plain');

echo "Test 3: Database Connection\n";
echo "===========================\n\n";

$configPath = __DIR__ . '/../config/config.php';

if (!file_exists($configPath)) {
    echo "ERROR: Config file not found at $configPath\n";
    exit;
}

$config = require $configPath;

if (!isset($config['database'])) {
    echo "ERROR: Database config not found in config.php\n";
    exit;
}

$dbConfig = $config['database'];

echo "Attempting to connect...\n";
echo "Host: " . ($dbConfig['host'] ?? 'N/A') . "\n";
echo "Database: " . ($dbConfig['dbname'] ?? 'N/A') . "\n";
echo "Username: " . ($dbConfig['username'] ?? 'N/A') . "\n";

try {
    $dsn = 'mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['dbname'] . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options'] ?? []);
    
    echo "\n✓ Connected to database!\n\n";
    
    // Test query
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found: " . implode(', ', $tables) . "\n\n";
    
    // Check agents
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM agents");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Agents in database: $count\n\n";
    
    // Check admin
    $stmt = $pdo->prepare("SELECT email, first_name FROM agents WHERE email = ?");
    $stmt->execute(['admin@demo-immo.com']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        echo "Admin user found: " . $admin['email'] . "\n";
    } else {
        echo "WARNING: Admin user NOT found!\n";
    }
    
} catch (PDOException $e) {
    echo "\n✗ Database connection FAILED!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nCheck:\n";
    echo "  1. Database credentials in config/config.php\n";
    echo "  2. Database server is running\n";
    echo "  3. User has permissions\n";
    echo "  4. Database name exists\n";
}
