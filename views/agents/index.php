<?php
$title = trans('agents');
$activeNav = 'agents';
$isAdmin = $isAdmin ?? false;
$userName = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
$userRole = trans($user['role'] ?? 'agent');
$userOfficeId = $user['office_id'] ?? null;

$content = 'agents/index-content.php';
require __DIR__ . '/../layouts/app.php';
