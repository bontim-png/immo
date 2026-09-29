<?php
$title = trans('office') . ': ' . sanitize($office['name']);
$activeNav = 'offices';
$isAdmin = $isAdmin ?? ($office['id'] == $user['office_id'] || ($user['role'] ?? '') === 'admin');
$userName = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
$userRole = trans($user['role'] ?? 'agent');
$userOfficeId = $user['office_id'] ?? null;

$content = 'offices/show-content.php';
require __DIR__ . '/../layouts/app.php';
