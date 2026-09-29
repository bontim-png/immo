<?php
$title = trans('new_office');
$activeNav = 'offices';
$isAdmin = true;
$userName = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
$userRole = trans($user['role'] ?? 'admin');
$userOfficeId = $user['office_id'] ?? null;

$content = 'offices/create-content.php';
require __DIR__ . '/../layouts/app.php';
