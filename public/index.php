<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$route = (string)($_GET['route'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($route === 'login') { require __DIR__ . '/../actions/login.php'; exit; }
    if ($route === 'forgot-password') { require __DIR__ . '/../actions/forgot-password.php'; exit; }
    if ($route === 'reset-password') { require __DIR__ . '/../actions/reset-password.php'; exit; }
}

$router = new App\Router($auth, $config);
$router->dispatch($route);
