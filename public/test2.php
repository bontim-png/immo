<?php
/**
 * IMMO - Config Test
 * Test if config file loads
 */
header('Content-Type: text/plain');

echo "Test 2: Config File\n";
echo "==================\n\n";

$configPath = __DIR__ . '/../config/config.php';
echo "Config path: $configPath\n";
echo "File exists: " . (file_exists($configPath) ? 'YES' : 'NO') . "\n";

if (file_exists($configPath)) {
    echo "File readable: " . (is_readable($configPath) ? 'YES' : 'NO') . "\n";
    
    try {
        $config = require $configPath;
        echo "Config loaded: YES\n";
        echo "Config is array: " . (is_array($config) ? 'YES' : 'NO') . "\n";
        
        if (is_array($config)) {
            echo "\nDatabase config:\n";
            echo "  Host: " . ($config['database']['host'] ?? 'N/A') . "\n";
            echo "  DB Name: " . ($config['database']['dbname'] ?? 'N/A') . "\n";
            echo "  Username: " . ($config['database']['username'] ?? 'N/A') . "\n";
            echo "  Password: " . (empty($config['database']['password']) ? 'EMPTY' : 'SET') . "\n";
            
            echo "\nAuth config:\n";
            echo "  Session name: " . ($config['auth']['session_name'] ?? 'N/A') . "\n";
        }
    } catch (Exception $e) {
        echo "ERROR loading config: " . $e->getMessage() . "\n";
    }
} else {
    echo "\nERROR: Config file not found!\n";
    echo "Expected at: $configPath\n";
}
