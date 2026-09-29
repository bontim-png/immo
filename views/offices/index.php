<?php
$title = trans('offices');
$activeNav = 'offices';
$isAdmin = $isAdmin ?? false;
$userName = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
$userRole = trans($user['role'] ?? 'agent');
$userOfficeId = $user['office_id'] ?? null;

$content = 'offices/index-content.php';
require __DIR__ . '/../layouts/app.php';
