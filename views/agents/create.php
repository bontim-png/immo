<?php
$title = trans('new_agent');
$activeNav = 'agents';
$isAdmin = $isAdmin ?? false;
$userName = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
$userRole = trans($user['role'] ?? 'agent');
$userOfficeId = $user['office_id'] ?? null;

$content = 'agents/create-content.php';
require __DIR__ . '/../layouts/app.php';
