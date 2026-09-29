<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['language'] ?? 'fr') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= sanitize($title ?? trans('app.name')) ?> | <?= trans('app.name') ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg') ?>">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= csrf_token() ?>">
</head>
<body class="bg-gray-50">
    <!-- Toast Notifications -->
    <?php if (isset($_SESSION['toast'])): ?>
        <?php foreach ($_SESSION['toast'] as $type => $message): ?>
            <div class="toast toast-<?= $type ?>" data-toast>
                <span><?= sanitize($message) ?></span>
                <button type="button" class="toast-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endforeach; ?>
        <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>

    <!-- Language Switcher (Top Right) -->
    <div class="language-switcher">
        <form method="POST" action="<?= route('language') ?>" class="language-form">
            <?= csrf_input() ?>
            <select name="language" onchange="this.form.submit()" class="language-select">
                <option value="fr" <?= ($_SESSION['language'] ?? '') === 'fr' ? 'selected' : '' ?>>FR</option>
                <option value="en" <?= ($_SESSION['language'] ?? '') === 'en' ? 'selected' : '' ?>>EN</option>
                <option value="nl" <?= ($_SESSION['language'] ?? '') === 'nl' ? 'selected' : '' ?>>NL</option>
            </select>
        </form>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <?php include $content; ?>
    </main>

    <!-- JavaScript -->
    <script src="<?= asset('js/app.js') ?>"></script>
    
    <!-- Dynamic Scripts -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
