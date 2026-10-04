<?php
declare(strict_types=1);

use App\Csrf;
use App\OfficeService;

if (!Csrf::verify((string)($_POST['_csrf'] ?? ''))) {
    http_response_code(419);
    exit('Invalid security token.');
}

$service = new OfficeService($db);
$action = (string)($_POST['action'] ?? '');

try {
    switch ($action) {
        case 'create':
            $id = $service->create($_POST);
            header('Location: /immobilier/admin/offices?created=' . $id);
            exit;

        case 'update':
            $service->update((int)($_POST['id'] ?? 0), $_POST);
            header('Location: /immobilier/admin/offices?updated=1');
            exit;

        case 'toggle':
            $service->setActive(
                (int)($_POST['id'] ?? 0),
                (int)($_POST['active'] ?? 0) === 1
            );
            header('Location: /immobilier/admin/offices');
            exit;

        default:
            http_response_code(400);
            exit('Invalid action.');
    }
} catch (Throwable $e) {
    http_response_code(422);
    exit(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
