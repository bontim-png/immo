<?php
declare(strict_types=1);

namespace App;

use PDO;

final class Auth
{
    public function __construct(
        private PDO $db,
        private array $config
    ) {
        $this->startSession();
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $basePath = rtrim((string)($this->config['app']['base_url'] ?? '/immobilier'), '/');
        $cookiePath = $basePath !== '' ? $basePath . '/' : '/';

        // Keep the existing session name for compatibility with sessions created
        // by the earlier login implementation.
        session_name('IMMO_SESSION');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => $cookiePath,
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public function attempt(string $email, string $password): bool
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.code AS role_code
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE LOWER(u.email) = LOWER(:email)
               AND u.is_active = 1
               AND u.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['email' => trim($email)]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, (string)$user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['office_id'] = $user['office_id'] !== null ? (int)$user['office_id'] : null;
        $_SESSION['role_id'] = (int)$user['role_id'];
        $_SESSION['role_code'] = (string)$user['role_code'];

        $update = $this->db->prepare(
            'UPDATE users SET last_login_at = NOW() WHERE id = :id'
        );
        $update->execute(['id' => (int)$user['id']]);

        return true;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'] ?? '/',
                $params['domain'] ?? '',
                (bool)($params['secure'] ?? false),
                (bool)($params['httponly'] ?? true)
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public function id(): ?int
    {
        return $this->check() ? (int)$_SESSION['user_id'] : null;
    }

    public function officeId(): ?int
    {
        return $this->check() && array_key_exists('office_id', $_SESSION) && $_SESSION['office_id'] !== null
            ? (int)$_SESSION['office_id']
            : null;
    }

    public function role(): ?string
    {
        return isset($_SESSION['role_code']) ? (string)$_SESSION['role_code'] : null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role() === 'super_admin';
    }

    public function requireLogin(): void
    {
        if ($this->check()) {
            return;
        }

        $base = rtrim((string)($this->config['app']['base_url'] ?? '/immobilier'), '/');
        header('Location: ' . $base . '/login.php');
        exit;
    }

    public function requireSuperAdmin(): void
    {
        $this->requireLogin();

        if (!$this->isSuperAdmin()) {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    public function can(string $permission): bool
    {
        if (!$this->check()) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        $stmt = $this->db->prepare(
            'SELECT 1
             FROM role_permissions rp
             INNER JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = :role_id
               AND p.code = :permission
             LIMIT 1'
        );
        $stmt->execute([
            'role_id' => (int)($_SESSION['role_id'] ?? 0),
            'permission' => $permission,
        ]);

        return (bool)$stmt->fetchColumn();
    }
}
