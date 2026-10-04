<?php
declare(strict_types=1);

namespace App;

final class Router
{
    public function __construct(
        private Auth $auth,
        private array $config
    ) {}

    public function dispatch(string $route): void
    {
        $route = trim($route, '/');

        switch ($route) {
            case '':
                if ($this->auth->check()) {
                    require __DIR__ . '/../views/dashboard.php';
                } else {
                    header('Location: ' . $this->config['app']['base_url'] . '/login');
                }
                return;

            case 'forgot-password':
                if ($this->auth->check()) { header('Location: ' . $this->config['app']['base_url'] . '/'); return; }
                require __DIR__ . '/../views/auth/forgot-password.php';
                return;

            case 'reset-password':
                if ($this->auth->check()) { header('Location: ' . $this->config['app']['base_url'] . '/'); return; }
                require __DIR__ . '/../views/auth/reset-password.php';
                return;

            case 'login':
                if ($this->auth->check()) {
                    header('Location: ' . $this->config['app']['base_url'] . '/');
                    return;
                }
                require __DIR__ . '/../views/auth/login.php';
                return;

            case 'logout':
                $this->auth->logout();
                header('Location: ' . $this->config['app']['base_url'] . '/login');
                return;

            default:
                $this->auth->requireLogin();

                if ($route === 'dashboard') {
                    require __DIR__ . '/../views/dashboard.php';
                    return;
                }

                http_response_code(404);
                require __DIR__ . '/../views/errors/404.php';
        }
    }
}
