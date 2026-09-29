<?php
http_response_code(404);

// Initialize basic environment for error pages
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Helpers/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['language'] ?? 'fr') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - <?= trans('not_found') ?> | IMMO</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        .error-container {
            text-align: center;
            max-width: 500px;
            padding: 2rem;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #2563eb;
            line-height: 1;
            margin-bottom: 1rem;
        }
        .error-title {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .error-message {
            color: #64748b;
            margin-bottom: 2rem;
        }
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.5;
            border-radius: 0.375rem;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 150ms ease-in-out;
            text-decoration: none;
        }
        .btn-primary {
            background-color: #2563eb;
            color: white;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        .btn-secondary {
            background-color: white;
            color: #475569;
            border-color: #e2e8f0;
        }
        .btn-secondary:hover {
            background-color: #f1f5f9;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <h1 class="error-title"><?= trans('not_found') ?></h1>
        <p class="error-message"><?= trans('page_not_found') ?></p>
        <div class="error-actions">
            <a href="/immobilier/public/" class="btn btn-primary">
                &#127968; <?= trans('go_home') ?>
            </a>
            <a href="/immobilier/public/properties" class="btn btn-secondary">
                <?= trans('browse_properties') ?>
            </a>
        </div>
    </div>
</body>
</html>
