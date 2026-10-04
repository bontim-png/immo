<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
$auth->requireLogin();

function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

$base = rtrim((string)($config['app']['base_url'] ?? '/immobilier'), '/');
$userId = $auth->id();
$officeId = $auth->officeId();
$role = (string)($auth->role() ?? '');

$userStmt = $db->prepare(
    'SELECT u.first_name, u.last_name, u.email, u.avatar_path, u.preferred_language_id,
            r.code AS role_code, r.name_key AS role_name_key,
            o.id AS office_id, o.name AS office_name, o.default_language_id,
            l.code AS preferred_language_code
     FROM users u
     INNER JOIN roles r ON r.id = u.role_id
     LEFT JOIN offices o ON o.id = u.office_id AND o.deleted_at IS NULL
     LEFT JOIN i18n_languages l ON l.id = u.preferred_language_id AND l.is_active = 1
     WHERE u.id = :id AND u.deleted_at IS NULL LIMIT 1'
);
$userStmt->execute(['id' => $userId]);
$user = $userStmt->fetch() ?: [];

$officeName = trim((string)($user['office_name'] ?? ''));
if ($officeName === '') $officeName = $auth->isSuperAdmin() ? __t('common.platform','Platform') : __t('common.office','Office');

$allowedLanguages = ['fr', 'en', 'nl'];
$lang = strtolower((string)($user['preferred_language_code'] ?? 'en'));
if (!in_array($lang, $allowedLanguages, true)) $lang = 'en';

$labels = [
    'en' => [
        'overview'=>'Overview','dashboard'=>'Dashboard','welcome'=>'Welcome back','platform_desc'=>'Platform overview and administration.',
        'office_desc'=>'Your office portfolio at a glance.','properties'=>'Properties','employees'=>'Employees','offices'=>'Offices','users'=>'Users',
        'published'=>'published','drafts'=>'drafts','active_offices'=>'Active offices','active_users'=>'Active platform users','in_office'=>'In your office',
        'all_offices'=>'All offices','portfolio'=>'Portfolio','recent'=>'Recently updated properties','view_all'=>'View all','access'=>'Access',
        'account'=>'Your account','role'=>'Role','office'=>'Office','email'=>'Email','modules'=>'Available modules','sign_out'=>'Sign out',
        'new_property'=>'New property','no_properties'=>'No properties yet','no_properties_text'=>'Properties within your access scope will appear here.',
        'language'=>'Language'
    ],
    'fr' => [
        'overview'=>'Vue d’ensemble','dashboard'=>'Tableau de bord','welcome'=>'Bon retour','platform_desc'=>'Vue d’ensemble de la plateforme et administration.',
        'office_desc'=>'Vue d’ensemble du portefeuille de votre agence.','properties'=>'Biens','employees'=>'Employés','offices'=>'Agences','users'=>'Utilisateurs',
        'published'=>'publiés','drafts'=>'brouillons','active_offices'=>'Agences actives','active_users'=>'Utilisateurs actifs','in_office'=>'Dans votre agence',
        'all_offices'=>'Toutes les agences','portfolio'=>'Portefeuille','recent'=>'Biens récemment modifiés','view_all'=>'Voir tout','access'=>'Accès',
        'account'=>'Votre compte','role'=>'Rôle','office'=>'Agence','email'=>'E-mail','modules'=>'Modules disponibles','sign_out'=>'Déconnexion',
        'new_property'=>'Nouveau bien','no_properties'=>'Aucun bien','no_properties_text'=>'Les biens accessibles depuis votre compte apparaîtront ici.',
        'language'=>'Langue'
    ],
    'nl' => [
        'overview'=>'Overzicht','dashboard'=>'Dashboard','welcome'=>'Welkom terug','platform_desc'=>'Platformoverzicht en administratie.',
        'office_desc'=>'Een overzicht van de portefeuille van jouw kantoor.','properties'=>'Properties','employees'=>'Medewerkers','offices'=>'Kantoren','users'=>'Gebruikers',
        'published'=>'gepubliceerd','drafts'=>'concepten','active_offices'=>'Actieve kantoren','active_users'=>'Actieve platformgebruikers','in_office'=>'In jouw kantoor',
        'all_offices'=>'Alle kantoren','portfolio'=>'Portefeuille','recent'=>'Recent bijgewerkte properties','view_all'=>'Alles bekijken','access'=>'Toegang',
        'account'=>'Jouw account','role'=>'Rol','office'=>'Kantoor','email'=>'E-mail','modules'=>'Beschikbare modules','sign_out'=>'Uitloggen',
        'new_property'=>'Nieuwe property','no_properties'=>'Nog geen properties','no_properties_text'=>'Properties binnen jouw toegangsgebied verschijnen hier.',
        'language'=>'Taal'
    ],
][$lang];

$scope = $auth->isSuperAdmin() ? 'system' : 'office';
$propertyWhere = 'p.deleted_at IS NULL';
$propertyParams = [];
if ($scope === 'office') {
    if ($officeId === null) $propertyWhere .= ' AND 1 = 0';
    else { $propertyWhere .= ' AND p.office_id = :office_id'; $propertyParams['office_id'] = $officeId; }
}
function countQuery(PDO $db, string $sql, array $params = []): int {
    $stmt = $db->prepare($sql); $stmt->execute($params); return (int)$stmt->fetchColumn();
}
$propertyCount = countQuery($db, "SELECT COUNT(*) FROM properties p WHERE {$propertyWhere}", $propertyParams);
$publishedCount = countQuery($db, "SELECT COUNT(*) FROM properties p WHERE {$propertyWhere} AND p.is_published = 1", $propertyParams);
$draftCount = countQuery($db, "SELECT COUNT(*) FROM properties p WHERE {$propertyWhere} AND p.status = 'draft'", $propertyParams);
$employeeCount = 0;
if ($auth->can('users.view')) {
    $employeeCount = $scope === 'system'
        ? countQuery($db, 'SELECT COUNT(*) FROM users WHERE deleted_at IS NULL AND is_active = 1')
        : ($officeId !== null ? countQuery($db, 'SELECT COUNT(*) FROM users WHERE office_id = :office_id AND deleted_at IS NULL AND is_active = 1', ['office_id'=>$officeId]) : 0);
}
$officeCount = $auth->can('offices.view') ? countQuery($db, 'SELECT COUNT(*) FROM offices WHERE deleted_at IS NULL AND is_active = 1') : 0;
$recentStmt = $db->prepare("SELECT p.id,p.reference,p.title,p.city,p.price,p.status,p.is_published,p.updated_at FROM properties p WHERE {$propertyWhere} ORDER BY p.updated_at DESC,p.id DESC LIMIT 6");
$recentStmt->execute($propertyParams); $recentProperties = $recentStmt->fetchAll();
$roleLabels=['super_admin'=>__t('roles.super_admin','Super Admin'),'office_admin'=>__t('roles.office_admin','Office Admin'),'office_employee'=>__t('roles.office_employee','Office Employee')];
$roleLabel=$roleLabels[$role] ?? ucwords(str_replace('_',' ',$role ?: 'User'));
$displayName=trim((string)($user['first_name']??'').' '.(string)($user['last_name']??''));
if($displayName==='') $displayName=(string)($user['email']??'User');
$initials=strtoupper(substr((string)($user['first_name']??'U'),0,1).substr((string)($user['last_name']??''),0,1));
?>
<!doctype html><html lang="<?=e($lang)?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?=e($officeName)?> — Prrepl</title><link rel="stylesheet" href="<?=e($base)?>/assets/css/dashboard.css"></head>
<body><div class="app-shell">
<?php require __DIR__ . '/../admin/_sidebar.php'; ?>
<main class="main">
<section class="welcome"><div><span class="eyebrow"><?=e($labels['welcome'])?></span><h2><?=e($displayName)?></h2><p><?=e($scope==='system'?$labels['platform_desc']:$labels['office_desc'])?></p></div><div class="welcome-actions"><?php if($auth->can('properties.view')): ?><a class="button-primary" href="<?=e($base)?>/admin/property-edit.php">+ <?=e($labels['new_property'])?></a><?php endif; ?><div class="scope-badge"><?=e($scope==='system'?$labels['all_offices']:$officeName)?></div></div></section>
<section class="stats"><div class="stat"><span><?=e($labels['properties'])?></span><strong><?=$propertyCount?></strong><small><?=$publishedCount?> <?=e($labels['published'])?> · <?=$draftCount?> <?=e($labels['drafts'])?></small></div><?php if($auth->can('users.view')):?><div class="stat"><span><?=e($labels['employees'])?></span><strong><?=$employeeCount?></strong><small><?=e($scope==='system'?'Across all offices':$labels['in_office'])?></small></div><?php endif;?><?php if($auth->can('offices.view')):?><div class="stat"><span><?=e($labels['offices'])?></span><strong><?=$officeCount?></strong><small><?=e($scope==='system'?$labels['active_offices']:'Available to you')?></small></div><?php endif;?><?php if($auth->can('users.view')):?><div class="stat"><span><?=e($labels['users'])?></span><strong><?=$employeeCount?></strong><small><?=e($labels['active_users'])?></small></div><?php endif;?></section>
<section class="content-grid"><div class="panel"><div class="panel-head"><div><span class="eyebrow"><?=e($labels['portfolio'])?></span><h3><?=e($labels['recent'])?></h3></div><?php if($auth->can('properties.view')):?><a href="<?=e($base)?>/views/properties.php"><?=e($labels['view_all'])?> →</a><?php endif;?></div><?php if(!$recentProperties):?><div class="empty"><strong><?=e($labels['no_properties'])?></strong><span><?=e($labels['no_properties_text'])?></span></div><?php else:?><div class="property-list"><?php foreach($recentProperties as $property):?><a class="property-row" href="<?=e($base)?>/admin/property-edit.php?id=<?= (int)$property['id']?>"><div class="property-icon">⌂</div><div class="property-main"><strong><?=e((string)($property['title']?:$property['reference']))?></strong><span><?=e((string)$property['reference'])?><?=$property['city']?' · '.e((string)$property['city']):''?></span></div><div class="property-price"><?= $property['price']!==null?e(number_format((float)$property['price'],0,',',' ')).' €':'' ?></div><div class="status <?=$property['is_published']?'published':'draft'?>"><?=$property['is_published']?e(__t('properties.published','Published')):e(__t('properties.status.'.(string)$property['status'],ucfirst((string)$property['status'])))?></div></a><?php endforeach;?></div><?php endif;?></div>
<div class="panel access-panel"><div class="panel-head"><div><span class="eyebrow"><?=e($labels['access'])?></span><h3><?=e($labels['account'])?></h3></div></div><div class="account-line"><span><?=e($labels['role'])?></span><strong><?=e($roleLabel)?></strong></div><div class="account-line"><span><?=e($labels['office'])?></span><strong><?=e($officeName)?></strong></div><div class="account-line"><span><?=e($labels['email'])?></span><strong><?=e((string)($user['email']??''))?></strong></div><div class="permissions"><span class="eyebrow"><?=e($labels['modules'])?></span><?php if($auth->can('properties.view')):?><span><?=e($labels['properties'])?></span><?php endif;?><?php if($auth->can('users.view')):?><span><?=e($labels['employees'])?></span><?php endif;?><?php if($auth->can('offices.view')):?><span><?=e($labels['offices'])?></span><?php endif;?></div></div></section>
</main></div></body></html>
