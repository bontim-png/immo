<?php
$title = trans('agent') . ': ' . sanitize($agent['first_name'] . ' ' . $agent['last_name']);
$activeNav = 'agents';
$isAdmin = $isAdmin ?? (($user['role'] ?? '') === 'admin');
$userName = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
$userRole = trans($user['role'] ?? 'agent');
$userOfficeId = $user['office_id'] ?? null;

require __DIR__ . '/../layouts/app.php';
require __DIR__ . '/show-content.php';
