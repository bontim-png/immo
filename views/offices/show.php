<?php
$title = trans('office') . ': ' . sanitize($office['name']);
$activeNav = 'offices';
$isAdmin = $isAdmin ?? (($user['role'] ?? '') === 'admin');
$userName = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
$userRole = trans($user['role'] ?? 'agent');
$userOfficeId = $user['office_id'] ?? null;

require __DIR__ . '/../layouts/app.php';
require __DIR__ . '/show-content.php';
