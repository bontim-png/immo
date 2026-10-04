<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
$auth->logout();
header('Location: ' . rtrim((string)($config['app']['base_url'] ?? '/immobilier'), '/') . '/login.php');
exit;
