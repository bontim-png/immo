<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/I18n.php';
require __DIR__ . '/../app/Core/Router.php';
require __DIR__ . '/../app/Core/View.php';
require __DIR__ . '/../app/Models/Property.php';
require __DIR__ . '/../app/Controllers/DashboardController.php';
require __DIR__ . '/../app/Controllers/PropertyController.php';

set_exception_handler(function (Throwable $e) use ($config): void {
    error_log((string) $e);
    http_response_code(500);
    if (!empty($config['app']['debug'])) {
        echo '<pre>' . htmlspecialchars((string) $e) . '</pre>';
        return;
    }
    View::render('500');
});

$locale = $_GET['lang'] ?? ($_COOKIE['immo_locale'] ?? $config['app']['default_locale']);
if (isset($_GET['lang']) && in_array($_GET['lang'], $config['app']['supported_locales'], true)) {
    setcookie('immo_locale', $_GET['lang'], ['expires'=>time()+31536000,'path'=>'/','samesite'=>'Lax']);
}
$i18n = new I18n($locale, $config['app']['supported_locales'], __DIR__ . '/../lang');
$db = (new Database($config['db']))->pdo();

$router = new Router();
$dashboard = new DashboardController($db, $i18n);
$property = new PropertyController($db, $i18n);
$router->get('/', fn() => $dashboard->index());
$router->get('/property', fn() => $property->show());

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/') ?: '/';
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
