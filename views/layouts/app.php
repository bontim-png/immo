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
    <!-- Sidebar -->
    <aside class="sidebar" data-sidebar>
        <div class="sidebar-header">
            <a href="<?= route('dashboard') ?>" class="logo">
                <span class="logo-icon">&#127968;</span>
                <span class="logo-text">IMMO</span>
            </a>
            <button class="sidebar-toggle" data-sidebar-toggle>&times;</button>
        </div>
        
        <nav class="sidebar-nav">
            <a href="<?= route('dashboard') ?>" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">&#128100;</span>
                <span class="nav-text"><?= trans('dashboard') ?></span>
            </a>
            
            <a href="<?= route('properties.index') ?>" class="nav-item <?= $activeNav === 'properties' ? 'active' : '' ?>">
                <span class="nav-icon">&#127968;</span>
                <span class="nav-text"><?= trans('properties') ?></span>
            </a>
            
            <?php if ($isAdmin ?? false): ?>
                <a href="<?= route('offices.index') ?>" class="nav-item <?= $activeNav === 'offices' ? 'active' : '' ?>">
                    <span class="nav-icon">&#127970;</span>
                    <span class="nav-text"><?= trans('offices') ?></span>
                </a>
                
                <a href="<?= route('agents.index') ?>" class="nav-item <?= $activeNav === 'agents' ? 'active' : '' ?>">
                    <span class="nav-icon">&#128101;</span>
                    <span class="nav-text"><?= trans('agents') ?></span>
                </a>
            <?php endif; ?>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><?= substr($userName ?? 'U', 0, 1) ?></div>
                <div class="user-details">
                    <span class="user-name"><?= sanitize($userName ?? trans('user')) ?></span>
                    <span class="user-role"><?= sanitize($userRole ?? trans('user')) ?></span>
                </div>
            </div>
            
            <form method="POST" action="<?= route('logout') ?>" class="logout-form">
                <?= csrf_input() ?>
                <button type="submit" class="logout-btn">
                    <span class="logout-icon">&#128682;</span>
                    <span class="logout-text"><?= trans('logout') ?></span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Overlay -->
    <div class="overlay" data-sidebar-overlay></div>

    <!-- Main Content -->
    <main class="main-content" data-main-content>
        <!-- Header -->
        <header class="header">
            <button class="mobile-menu-btn" data-sidebar-toggle>&#9776;</button>
            <h1 class="page-title"><?= sanitize($title ?? '') ?></h1>
            <div class="header-actions">
                <!-- Language Switcher -->
                <form method="POST" action="<?= route('language') ?>" class="language-form">
                    <?= csrf_input() ?>
                    <select name="language" onchange="this.form.submit()" class="language-select">
                        <option value="fr" <?= ($_SESSION['language'] ?? '') === 'fr' ? 'selected' : '' ?>>FR</option>
                        <option value="en" <?= ($_SESSION['language'] ?? '') === 'en' ? 'selected' : '' ?>>EN</option>
                        <option value="nl" <?= ($_SESSION['language'] ?? '') === 'nl' ? 'selected' : '' ?>>NL</option>
                    </select>
                </form>
                
                <?php if (isset($headerActions)): ?>
                    <?= $headerActions ?>
                <?php endif; ?>
            </div>
        </header>
        
        <!-- Content -->
        <div class="content">
            <?php include $content; ?>
        </div>
    </main>

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

    <!-- Confirmation Dialog -->
    <div class="modal" id="confirm-dialog" data-modal>
        <div class="modal-content">
            <h3 id="confirm-title"></h3>
            <p id="confirm-message"></p>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeConfirmDialog()"><?= trans('cancel') ?></button>
                <button class="btn btn-danger" id="confirm-btn"><?= trans('confirm') ?></button>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay" style="display: none;">
        <div class="loading-spinner"></div>
    </div>

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
