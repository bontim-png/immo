<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$route = trim((string)($_GET['route'] ?? ''), '/');

if ($route === 'health') {
    echo json_encode([
        'success' => true,
        'app' => 'Immo',
        'status' => 'ok',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(404);

echo json_encode([
    'success' => false,
    'error' => [
        'code' => 'NOT_FOUND',
        'message' => 'API endpoint not found.',
    ],
], JSON_UNESCAPED_UNICODE);
