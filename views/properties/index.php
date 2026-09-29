<?php
$title = trans('properties');
$activeNav = 'properties';
$isAdmin = $isAdmin ?? false;
$userName = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
$userRole = trans($user['role'] ?? 'user');
$userOfficeId = $user['office_id'] ?? null;

$content = 'properties/index-content.php';
require __DIR__ . '/../layouts/app.php';
