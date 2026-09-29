<?php
/**
 * Direct test of index.php functionality
 * This bypasses all .htaccess rules
 */

echo "index-test.php loaded successfully!\n\n";

// Test if index.php exists
$indexPath = __DIR__ . '/index.php';
echo "index.php exists: " . (file_exists($indexPath) ? 'YES' : 'NO') . "\n";
echo "index.php path: $indexPath\n\n";

// Test if we can include it
if (file_exists($indexPath)) {
    echo "Attempting to include index.php...\n";
    try {
        include $indexPath;
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    } catch (Error $e) {
        echo "FATAL ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "index.php not found!\n";
}
