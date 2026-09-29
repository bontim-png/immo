<?php
/**
 * Show the exact error from index.php
 */
// Show ALL errors
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('html_errors', '0');

header('Content-Type: text/plain');

$indexPath = __DIR__ . '/index.php';
echo "Loading index.php from: $indexPath\n\n";

// Set up error handler to catch everything
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "ERROR: [$errno] $errstr\n";
    echo "File: $errfile\n";
    echo "Line: $errline\n";
    exit(1);
});

set_exception_handler(function($e) {
    echo "EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
});

// Try to include index.php
try {
    include $indexPath;
} catch (Throwable $e) {
    echo "FATAL ERROR: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n\nIf you see this, index.php loaded without fatal errors!\n";
