<?php
$title = trans('edit_agent');
$activeNav = 'agents';
$isAdmin = $isAdmin ?? (($user['role'] ?? '') === 'admin');
$userName = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
$userRole = trans($user['role'] ?? 'agent');
$userOfficeId = $user['office_id'] ?? null;

$content = 'agents/edit-content.php';
require __DIR__ . '/../layouts/app.php';
