<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

use App\Csrf;

if ($auth->check()) {
    header('Location: ' . ($config['app']['base_url'] ?? '/immobilier') . '/views/dashboard.php');
    exit;
}

$lang = strtolower(trim((string)($_GET['lang'] ?? $_POST['lang'] ?? $i18n->getLanguage())));
$lang = in_array($lang, ['fr', 'en', 'nl'], true) ? $lang : 'fr';
$i18n->setLanguage($lang);

$errors = [];
$email = trim((string)($_POST['email'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verify((string)($_POST['_csrf'] ?? ''))) {
        $errors[] = __t('auth.session_expired', 'Your session expired. Please try again.');
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = __t('auth.email_invalid', 'Please enter a valid email address.');
    }

    $password = (string)($_POST['password'] ?? '');
    if ($password === '') {
        $errors[] = __t('auth.password_required', 'Please enter your password.');
    }

    if (!$errors && !$auth->attempt($email, $password)) {
        $errors[] = __t('auth.invalid_credentials', 'Invalid email or password.');
    }

    if (!$errors) {
        header('Location: ' . ($config['app']['base_url'] ?? '/immobilier') . '/views/dashboard.php');
        exit;
    }
}

$base = rtrim((string)($config['app']['base_url'] ?? '/immobilier'), '/');
?>
<!doctype html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(__t('auth.sign_in', 'Sign in')) ?> — Prrepl</title>
<link rel="stylesheet" href="<?= e($base) ?>/assets/css/login.css?v=40">
</head>
<body>
<div class="login-shell">
    <section class="brand-panel">
        <div class="brand-row">
            <div class="brand-mark">P</div>
            <div class="brand">PRREPL</div>
        </div>
        <div class="brand-copy">
            <span><?= e(__t('auth.property_management', 'PROPERTY MANAGEMENT')) ?></span>
            <h1><?= e(__t('auth.brand_title', 'One workspace.')) ?><br><?= e(__t('auth.brand_title_2', 'Every property.')) ?></h1>
            <p><?= e(__t('auth.brand_subtitle', 'A single workspace for modern real-estate teams.')) ?></p>
        </div>
        <div class="brand-footer">PRREPL</div>
    </section>

    <main class="login-panel">
        <div class="language" aria-label="Language">
            <a class="<?= $lang === 'fr' ? 'active' : '' ?>" href="?lang=fr" aria-label="Français">🇫🇷</a>
            <a class="<?= $lang === 'en' ? 'active' : '' ?>" href="?lang=en" aria-label="English">🇬🇧</a>
            <a class="<?= $lang === 'nl' ? 'active' : '' ?>" href="?lang=nl" aria-label="Nederlands">🇳🇱</a>
        </div>

        <div class="login-card">
            <div class="eyebrow">PRREPL</div>
            <h2><?= e(__t('auth.welcome_back', 'Welcome back')) ?></h2>
            <p class="subtitle"><?= e(__t('auth.subtitle', 'Sign in to your Prrepl workspace.')) ?></p>

            <?php if ($errors): ?>
                <div class="alert" role="alert">
                    <?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e($base) ?>/login.php" novalidate>
                <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                <input type="hidden" name="lang" value="<?= e($lang) ?>">

                <label>
                    <?= e(__t('auth.email', 'Email address')) ?>
                    <input type="email" name="email" value="<?= e($email) ?>" autocomplete="username" required autofocus>
                </label>

                <label>
                    <span class="label-line">
                        <span><?= e(__t('auth.password', 'Password')) ?></span>
                        <a class="forgot" href="<?= e($base) ?>/forgot-password.php?lang=<?= e($lang) ?>"><?= e(__t('auth.forgot_password', 'Forgot password?')) ?></a>
                    </span>
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>

                <button type="submit">
                    <span><?= e(__t('auth.sign_in', 'Sign in')) ?></span>
                    <span class="button-arrow">→</span>
                </button>
            </form>
        </div>

        <div class="footer">Prrepl · <?= date('Y') ?></div>
    </main>
</div>
</body>
</html>
