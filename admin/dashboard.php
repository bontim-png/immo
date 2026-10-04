<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$auth->requireLogin();

$stmt = $db->prepare('SELECT first_name, last_name, email FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1');
$stmt->execute(['id' => $auth->id()]);
$user = $stmt->fetch() ?: [];

$office = null;
if ($auth->officeId() !== null) {
    $stmt = $db->prepare('SELECT name FROM offices WHERE id = :id AND deleted_at IS NULL LIMIT 1');
    $stmt->execute(['id' => $auth->officeId()]);
    $office = $stmt->fetch();
}
$base = rtrim((string)($config['app']['base_url'] ?? '/immobilier'), '/');
function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard — Immo</title></head>
<body>
<main>
<h1>Immo</h1>
<p>Welcome, <?= e((string)($user['first_name'] ?? '')) ?> <?= e((string)($user['last_name'] ?? '')) ?>.</p>
<p>Role: <?= e((string)$auth->role()) ?></p>
<?php if ($office): ?><p>Office: <?= e((string)$office['name']) ?></p><?php endif; ?>
<p><a href="<?= e($base) ?>/logout.php">Log out</a></p>
</main>
</body></html>
