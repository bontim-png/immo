<?php
$title = trans('new_property');
$activeNav = 'properties';
$isAdmin = isset($user) && ($user['role'] ?? '') === 'admin';
$userName = isset($user) ? (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) : '';
$userRole = isset($user) ? trans($user['role'] ?? 'agent') : trans('user');
$userOfficeId = isset($user) ? ($user['office_id'] ?? null) : null;

require __DIR__ . '/../layouts/app.php';
require __DIR__ . '/create-content.php';
