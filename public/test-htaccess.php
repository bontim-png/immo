<?php
/**
 * Test if .htaccess rewrite is working
 * 
 * If you can access this file directly at:
 * https://prrepl.com/immobilier/public/test-htaccess.php
 * 
 * Then .htaccess IS working (it's not blocking direct file access)
 * 
 * But if you get 404 when accessing /login, the rewrite rule isn't working.
 */

echo "If you see this, .htaccess file is being processed but rewrite rules may not be working.\n\n";
echo "The .htaccess should rewrite /login to index.php\n";
echo "But this file (test-htaccess.php) can still be accessed directly.\n\n";

echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
