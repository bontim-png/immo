<?php
declare(strict_types=1);

/**
 * Single shared navigation for all admin pages.
 * Uses the same classes and styling as Dashboard.
 */
$__navBase = rtrim((string)($base ?? ($config['app']['base_url'] ?? '/immobilier')), '/');
$__navUserId = (int)$auth->id();
$__navOffice = '';
$__navLang = 'en';

try {
    $stmt = $db->prepare(
        'SELECT u.preferred_language_id, o.name AS office_name, l.code AS language_code
         FROM users u
         LEFT JOIN offices o ON o.id=u.office_id AND o.deleted_at IS NULL
         LEFT JOIN i18n_languages l ON l.id=u.preferred_language_id AND l.is_active=1
         WHERE u.id=:id AND u.deleted_at IS NULL LIMIT 1'
    );
    $stmt->execute(['id' => $__navUserId]);
    $__navUser = $stmt->fetch() ?: [];
    $__navOffice = trim((string)($__navUser['office_name'] ?? ''));
    $__navLang = strtolower(trim((string)($__navUser['language_code'] ?? 'en')));
} catch (Throwable $e) {
    $__navUser = [];
}

if ($__navOffice === '') {
    $__navOffice = $auth->isSuperAdmin() ? 'Platform' : 'Office';
}
if (!in_array($__navLang, ['fr','en','nl'], true)) {
    $__navLang = 'en';
}

$__navLabels = [
    'en' => ['workspace'=>'WORKSPACE','navigation'=>'NAVIGATION','account'=>'ACCOUNT','dashboard'=>'Dashboard','properties'=>'Properties','employees'=>'Employees','offices'=>'Offices','settings'=>'Settings','sign_out'=>'Sign out'],
    'fr' => ['workspace'=>'ESPACE DE TRAVAIL','navigation'=>'NAVIGATION','account'=>'COMPTE','dashboard'=>'Tableau de bord','properties'=>'Biens','employees'=>'Employés','offices'=>'Agences','settings'=>'Paramètres','sign_out'=>'Déconnexion'],
    'nl' => ['workspace'=>'WERKRUIMTE','navigation'=>'NAVIGATIE','account'=>'ACCOUNT','dashboard'=>'Dashboard','properties'=>'Objecten','employees'=>'Medewerkers','offices'=>'Kantoren','settings'=>'Instellingen','sign_out'=>'Uitloggen'],
];
$__navT = $__navLabels[$__navLang];

$__navPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '';
$__navActive = static function(string $section) use ($__navPath): bool {
    return match ($section) {
        'dashboard' => str_ends_with($__navPath, '/views/dashboard.php') || str_ends_with($__navPath, '/admin/dashboard.php'),
        'properties' => str_ends_with($__navPath, '/views/properties.php') || str_ends_with($__navPath, '/admin/properties.php') || str_ends_with($__navPath, '/admin/property-edit.php'),
        'employees' => str_ends_with($__navPath, '/admin/employees.php'),
        'offices' => str_ends_with($__navPath, '/admin/offices.php') || str_ends_with($__navPath, '/views/admin/offices.php'),
        'settings' => str_ends_with($__navPath, '/admin/settings.php'),
        default => false,
    };
};
?>
<aside class="sidebar">
    <div class="brand">
        <span class="brand-mark">P</span>
        <span class="brand-office"><?=e($__navOffice)?></span>
    </div>

    <div class="workspace">
        <span class="workspace-label"><?=e($__navT['workspace'])?></span>
        <strong><?=e($__navOffice)?></strong>
    </div>

    <nav class="nav" aria-label="<?=e($__navT['navigation'])?>">
        <a class="nav-item <?=$__navActive('dashboard')?'active':''?>" href="<?=e($__navBase)?>/views/dashboard.php">
            <span>⌂</span><?=e($__navT['dashboard'])?>
        </a>
        <?php if ($auth->can('properties.view')): ?>
        <a class="nav-item <?=$__navActive('properties')?'active':''?>" href="<?=e($__navBase)?>/views/properties.php">
            <span>▣</span><?=e($__navT['properties'])?>
        </a>
        <?php endif; ?>
        <?php if ($auth->can('users.view')): ?>
        <a class="nav-item <?=$__navActive('employees')?'active':''?>" href="<?=e($__navBase)?>/admin/employees.php">
            <span>♙</span><?=e($__navT['employees'])?>
        </a>
        <?php endif; ?>
        <?php if ($auth->can('offices.view')): ?>
        <a class="nav-item <?=$__navActive('offices')?'active':''?>" href="<?=e($__navBase)?>/admin/offices.php">
            <span>⌂</span><?=e($__navT['offices'])?>
        </a>
        <?php endif; ?>

        <div class="nav-section"><?=e($__navT['account'])?></div>
        <a class="nav-item <?=$__navActive('settings')?'active':''?>" href="<?=e($__navBase)?>/admin/settings.php">
            <span>⚙</span><?=e($__navT['settings'])?>
        </a>
    </nav>

    <div class="sidebar-bottom">
        <a class="nav-item" href="<?=e($__navBase)?>/logout.php">
            <span>↪</span><?=e($__navT['sign_out'])?>
        </a>
    </div>
</aside>
