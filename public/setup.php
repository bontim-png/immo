<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$count = (int)$db->query(
    'SELECT COUNT(*) FROM users'
)->fetchColumn();

if ($count > 0) {
    http_response_code(403);
    exit('Initial setup is already completed.');
}

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $officeName = trim((string)($_POST['office_name'] ?? ''));
    $firstName = trim((string)($_POST['first_name'] ?? ''));
    $lastName = trim((string)($_POST['last_name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($officeName === '' || $firstName === '' || $lastName === '') {
        $error = 'Office name, first name and last name are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'A valid email address is required.';
    } elseif (strlen($password) < 12) {
        $error = 'Password must contain at least 12 characters.';
    } else {
        try {
            $db->beginTransaction();

            $languageId = (int)$db->query(
                "SELECT id FROM i18n_languages WHERE code = 'fr' LIMIT 1"
            )->fetchColumn();

            $roleId = (int)$db->query(
                "SELECT id FROM roles WHERE code = 'office_admin' LIMIT 1"
            )->fetchColumn();

            if (!$languageId || !$roleId) {
                throw new RuntimeException('Required seed data is missing.');
            }

            $officeId = insertOffice($db, $officeName, $languageId);

            $publicId = generateUuidV4();

            $stmt = $db->prepare(
                'INSERT INTO users
                 (public_id, office_id, role_id, first_name, last_name, email, password_hash, preferred_language_id)
                 VALUES
                 (:public_id, :office_id, :role_id, :first_name, :last_name, :email, :password_hash, :language_id)'
            );

            $stmt->execute([
                'public_id' => $publicId,
                'office_id' => $officeId,
                'role_id' => $roleId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'language_id' => $languageId,
            ]);

            $db->commit();

            $message = 'Setup completed. You can now log in.';
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $error = 'Setup failed. Check the server error log.';
        }
    }
}

function insertOffice(\PDO $db, string $name, int $languageId): int
{
    $stmt = $db->prepare(
        'INSERT INTO offices
         (public_id, name, default_language_id)
         VALUES (:public_id, :name, :language_id)'
    );

    $stmt->execute([
        'public_id' => generateUuidV4(),
        'name' => $name,
        'language_id' => $languageId,
    ]);

    return (int)$db->lastInsertId();
}

function generateUuidV4(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Initial setup - Immo</title>
</head>
<body>
    <main>
        <h1>Immo initial setup</h1>

        <?php if ($message): ?>
            <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
            <p><a href="/immobilier/login">Go to login</a></p>
        <?php else: ?>

            <?php if ($error): ?>
                <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form method="post">
                <p>
                    <label>
                        Office name<br>
                        <input name="office_name" required>
                    </label>
                </p>

                <p>
                    <label>
                        First name<br>
                        <input name="first_name" required>
                    </label>
                </p>

                <p>
                    <label>
                        Last name<br>
                        <input name="last_name" required>
                    </label>
                </p>

                <p>
                    <label>
                        Email<br>
                        <input type="email" name="email" required autocomplete="username">
                    </label>
                </p>

                <p>
                    <label>
                        Password<br>
                        <input type="password" name="password" minlength="12" required autocomplete="new-password">
                    </label>
                </p>

                <button type="submit">Create administrator</button>
            </form>

        <?php endif; ?>
    </main>
</body>
</html>
