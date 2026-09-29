<?php
$title = trans('edit_property');
$activeNav = 'properties';
$isAdmin = $isAdmin ?? false;
$userName = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
$userRole = trans($user['role'] ?? 'agent');
$userOfficeId = $user['office_id'] ?? null;

require __DIR__ . '/../layouts/app.php';
require __DIR__ . '/edit-content.php';
