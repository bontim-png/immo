<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Csrf;

$auth->requireLogin();

$roleCode = (string)($auth->role() ?? '');
$isSuperAdmin = $roleCode === 'super_admin';
if (!$isSuperAdmin && !$auth->can('users.create') && !$auth->can('users.update')) {
    http_response_code(403);
    exit(__t('common.access_denied', 'Access denied.'));
}

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function uuidv4(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

$base = rtrim((string)($config['app']['base_url'] ?? '/immobilier'), '/');
$mode = (string)($_GET['mode'] ?? 'list');
if (!in_array($mode, ['list', 'create', 'edit'], true)) {
    $mode = 'list';
}

$requestedId = (int)($_GET['id'] ?? 0);
$message = '';
$error = '';
$csrf = Csrf::token();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Csrf::verify((string)($_POST['_csrf'] ?? ''))) {
            throw new RuntimeException(__t('common.session_expired', 'Your session expired. Please try again.'));
        }

        $action = (string)($_POST['action'] ?? '');
        $userId = (int)($_POST['user_id'] ?? 0);

        if ($action === 'delete') {
            if ($userId < 1 || $userId === (int)$auth->id()) {
                throw new RuntimeException(__t('employees.error.self_delete', 'You cannot delete your own account.'));
            }

            $stmt = $db->prepare('SELECT id, office_id FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1');
            $stmt->execute(['id' => $userId]);
            $target = $stmt->fetch();
            if (!$target) {
                throw new RuntimeException(__t('employees.error.not_found', 'Employee not found.'));
            }
            if (!$isSuperAdmin && (int)$target['office_id'] !== (int)$auth->officeId()) {
                throw new RuntimeException(__t('employees.error.office_scope', 'You can only manage employees in your own office.'));
            }

            $db->beginTransaction();
            $db->prepare('UPDATE users SET deleted_at = NOW(), is_active = 0, updated_at = NOW() WHERE id = :id')->execute(['id' => $userId]);
            $db->prepare('UPDATE agents SET deleted_at = NOW(), is_active = 0, updated_at = NOW() WHERE user_id = :id')->execute(['id' => $userId]);
            $db->commit();
            header('Location: ' . $base . '/admin/employees.php?deleted=1');
            exit;
        }

        if ($action === 'status') {
            if ($userId < 1 || $userId === (int)$auth->id()) {
                throw new RuntimeException(__t('employees.error.self_status', 'You cannot deactivate your own account.'));
            }
            $newStatus = (int)($_POST['is_active'] ?? 0) === 1 ? 1 : 0;
            $stmt = $db->prepare('SELECT id, office_id FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1');
            $stmt->execute(['id' => $userId]);
            $target = $stmt->fetch();
            if (!$target) {
                throw new RuntimeException(__t('employees.error.not_found', 'Employee not found.'));
            }
            if (!$isSuperAdmin && (int)$target['office_id'] !== (int)$auth->officeId()) {
                throw new RuntimeException(__t('employees.error.office_scope', 'You can only manage employees in your own office.'));
            }
            $db->prepare('UPDATE users SET is_active = :active, updated_at = NOW() WHERE id = :id')->execute(['active' => $newStatus, 'id' => $userId]);
            $db->prepare('UPDATE agents SET is_active = :active, updated_at = NOW() WHERE user_id = :id AND deleted_at IS NULL')->execute(['active' => $newStatus, 'id' => $userId]);
            header('Location: ' . $base . '/admin/employees.php?updated=1');
            exit;
        }

        if ($action === 'save') {
            $modePost = (string)($_POST['mode'] ?? 'create');
            $officeId = (int)($_POST['office_id'] ?? 0);
            $newRole = trim((string)($_POST['role_code'] ?? ''));
            $firstName = trim((string)($_POST['first_name'] ?? ''));
            $lastName = trim((string)($_POST['last_name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? '')) ?: null;
            $mobile = trim((string)($_POST['mobile'] ?? '')) ?: null;
            $jobTitle = trim((string)($_POST['job_title'] ?? '')) ?: null;
            $bio = trim((string)($_POST['bio'] ?? '')) ?: null;
            $languageCode = strtolower(trim((string)($_POST['language_code'] ?? 'en')));
            $password = (string)($_POST['password'] ?? '');
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $allowedRoles = ['office_admin', 'office_staff', 'agent'];
            if (!in_array($newRole, $allowedRoles, true)) {
                throw new RuntimeException(__t('employees.error.invalid_role', 'Invalid role selected.'));
            }
            if ($officeId < 1 || $firstName === '' || $lastName === '') {
                throw new RuntimeException(__t('employees.error.required', 'Office, first name and last name are required.'));
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException(__t('employees.error.email', 'A valid email address is required.'));
            }
            if (!$isSuperAdmin && $officeId !== (int)$auth->officeId()) {
                throw new RuntimeException(__t('employees.error.office_scope', 'You can only manage employees in your own office.'));
            }
            if ($modePost === 'create' && strlen($password) < 12) {
                throw new RuntimeException(__t('employees.error.password', 'Password must contain at least 12 characters.'));
            }
            if ($modePost === 'edit' && $password !== '' && strlen($password) < 12) {
                throw new RuntimeException(__t('employees.error.password', 'Password must contain at least 12 characters.'));
            }

            $stmt = $db->prepare('SELECT id FROM offices WHERE id = :id AND is_active = 1 AND deleted_at IS NULL LIMIT 1');
            $stmt->execute(['id' => $officeId]);
            if (!$stmt->fetchColumn()) {
                throw new RuntimeException(__t('employees.error.office', 'Selected office does not exist or is inactive.'));
            }

            $stmt = $db->prepare('SELECT id FROM i18n_languages WHERE code = :code AND is_active = 1 LIMIT 1');
            $stmt->execute(['code' => $languageCode]);
            $languageId = $stmt->fetchColumn();
            if (!$languageId) {
                $languageId = $db->query('SELECT id FROM i18n_languages WHERE is_default = 1 AND is_active = 1 LIMIT 1')->fetchColumn();
            }

            $db->beginTransaction();

            if ($modePost === 'create') {
                $stmt = $db->prepare('SELECT id FROM users WHERE LOWER(email) = LOWER(:email) AND deleted_at IS NULL LIMIT 1');
                $stmt->execute(['email' => $email]);
                if ($stmt->fetchColumn()) {
                    throw new RuntimeException(__t('employees.error.duplicate', 'A user with this email already exists.'));
                }

                $stmt = $db->prepare('SELECT id FROM roles WHERE code = :code LIMIT 1');
                $stmt->execute(['code' => $newRole]);
                $roleId = (int)$stmt->fetchColumn();
                if (!$roleId) {
                    throw new RuntimeException(__t('employees.error.role_missing', 'The selected role is not configured.'));
                }

                $stmt = $db->prepare('INSERT INTO users (public_id, office_id, role_id, first_name, last_name, email, password_hash, phone, preferred_language_id, is_active) VALUES (:public_id,:office_id,:role_id,:first_name,:last_name,:email,:password_hash,:phone,:language_id,:is_active)');
                $stmt->execute([
                    'public_id' => uuidv4(), 'office_id' => $officeId, 'role_id' => $roleId,
                    'first_name' => $firstName, 'last_name' => $lastName, 'email' => $email,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'phone' => $phone,
                    'language_id' => $languageId ?: null, 'is_active' => $isActive,
                ]);
                $newUserId = (int)$db->lastInsertId();

                if ($newRole === 'agent') {
                    $stmt = $db->prepare('INSERT INTO agents (public_id, office_id, user_id, first_name, last_name, email, phone, mobile, job_title, bio, is_active) VALUES (:public_id,:office_id,:user_id,:first_name,:last_name,:email,:phone,:mobile,:job_title,:bio,:is_active)');
                    $stmt->execute([
                        'public_id' => uuidv4(), 'office_id' => $officeId, 'user_id' => $newUserId,
                        'first_name' => $firstName, 'last_name' => $lastName, 'email' => $email,
                        'phone' => $phone, 'mobile' => $mobile, 'job_title' => $jobTitle, 'bio' => $bio,
                        'is_active' => $isActive,
                    ]);
                }
                $db->commit();
                header('Location: ' . $base . '/admin/employees.php?created=1');
                exit;
            }

            $stmt = $db->prepare('SELECT u.*, r.code AS role_code FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE u.id = :id AND u.deleted_at IS NULL LIMIT 1');
            $stmt->execute(['id' => $userId]);
            $existing = $stmt->fetch();
            if (!$existing) {
                throw new RuntimeException(__t('employees.error.not_found', 'Employee not found.'));
            }
            if (!$isSuperAdmin && (int)$existing['office_id'] !== (int)$auth->officeId()) {
                throw new RuntimeException(__t('employees.error.office_scope', 'You can only manage employees in your own office.'));
            }

            $stmt = $db->prepare('SELECT id FROM users WHERE LOWER(email) = LOWER(:email) AND id <> :id AND deleted_at IS NULL LIMIT 1');
            $stmt->execute(['email' => $email, 'id' => $userId]);
            if ($stmt->fetchColumn()) {
                throw new RuntimeException(__t('employees.error.duplicate', 'A user with this email already exists.'));
            }

            $stmt = $db->prepare('SELECT id FROM roles WHERE code = :code LIMIT 1');
            $stmt->execute(['code' => $newRole]);
            $roleId = (int)$stmt->fetchColumn();
            if (!$roleId) {
                throw new RuntimeException(__t('employees.error.role_missing', 'The selected role is not configured.'));
            }

            $sql = 'UPDATE users SET office_id=:office_id, role_id=:role_id, first_name=:first_name, last_name=:last_name, email=:email, phone=:phone, preferred_language_id=:language_id, is_active=:is_active, updated_at=NOW()';
            $params = [
                'office_id' => $officeId, 'role_id' => $roleId, 'first_name' => $firstName, 'last_name' => $lastName,
                'email' => $email, 'phone' => $phone, 'language_id' => $languageId ?: null, 'is_active' => $isActive, 'id' => $userId,
            ];
            if ($password !== '') {
                $sql .= ', password_hash=:password_hash';
                $params['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $sql .= ' WHERE id=:id';
            $db->prepare($sql)->execute($params);

            $agentStmt = $db->prepare('SELECT id FROM agents WHERE user_id = :id AND deleted_at IS NULL LIMIT 1');
            $agentStmt->execute(['id' => $userId]);
            $agentId = (int)$agentStmt->fetchColumn();
            if ($newRole === 'agent') {
                if ($agentId) {
                    $db->prepare('UPDATE agents SET office_id=:office_id, first_name=:first_name, last_name=:last_name, email=:email, phone=:phone, mobile=:mobile, job_title=:job_title, bio=:bio, is_active=:is_active, updated_at=NOW() WHERE id=:id')->execute([
                        'office_id'=>$officeId,'first_name'=>$firstName,'last_name'=>$lastName,'email'=>$email,'phone'=>$phone,'mobile'=>$mobile,'job_title'=>$jobTitle,'bio'=>$bio,'is_active'=>$isActive,'id'=>$agentId,
                    ]);
                } else {
                    $db->prepare('INSERT INTO agents (public_id, office_id, user_id, first_name, last_name, email, phone, mobile, job_title, bio, is_active) VALUES (:public_id,:office_id,:user_id,:first_name,:last_name,:email,:phone,:mobile,:job_title,:bio,:is_active)')->execute([
                        'public_id'=>uuidv4(),'office_id'=>$officeId,'user_id'=>$userId,'first_name'=>$firstName,'last_name'=>$lastName,'email'=>$email,'phone'=>$phone,'mobile'=>$mobile,'job_title'=>$jobTitle,'bio'=>$bio,'is_active'=>$isActive,
                    ]);
                }
            } elseif ($agentId) {
                $db->prepare('UPDATE agents SET is_active=0, updated_at=NOW() WHERE id=:id')->execute(['id'=>$agentId]);
            }

            $db->commit();
            header('Location: ' . $base . '/admin/employees.php?updated=1');
            exit;
        }
    }
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $error = $e->getMessage();
}

$selectedOfficeId = $isSuperAdmin ? (int)($_GET['office_id'] ?? 0) : (int)($auth->officeId() ?? 0);
$selectedOfficeName = '';

if ($isSuperAdmin) {
    if ($selectedOfficeId > 0) {
        $officeStmt = $db->prepare('SELECT id, name FROM offices WHERE id=:id AND is_active=1 AND deleted_at IS NULL LIMIT 1');
        $officeStmt->execute(['id'=>$selectedOfficeId]);
        $selectedOffice = $officeStmt->fetch();
        if (!$selectedOffice) {
            $selectedOfficeId = 0;
        } else {
            $selectedOfficeName = (string)$selectedOffice['name'];
        }
    }
    $offices = $db->query('SELECT id, name FROM offices WHERE is_active=1 AND deleted_at IS NULL ORDER BY name')->fetchAll();

    if ($selectedOfficeId > 0) {
        $employeesStmt = $db->prepare('SELECT u.id,u.first_name,u.last_name,u.email,u.is_active,u.last_login_at,u.updated_at,r.code role_code,r.name_key role_name_key,o.name office_name FROM users u INNER JOIN roles r ON r.id=u.role_id INNER JOIN offices o ON o.id=u.office_id WHERE u.office_id=:office_id AND u.deleted_at IS NULL ORDER BY u.last_name,u.first_name');
        $employeesStmt->execute(['office_id'=>$selectedOfficeId]);
    } else {
        $employeesStmt = $db->query('SELECT u.id,u.first_name,u.last_name,u.email,u.is_active,u.last_login_at,u.updated_at,r.code role_code,r.name_key role_name_key,o.name office_name FROM users u INNER JOIN roles r ON r.id=u.role_id LEFT JOIN offices o ON o.id=u.office_id WHERE u.deleted_at IS NULL ORDER BY u.last_name,u.first_name');
    }
} else {
    $officeStmt = $db->prepare('SELECT id,name FROM offices WHERE id=:id AND is_active=1 AND deleted_at IS NULL LIMIT 1');
    $officeStmt->execute(['id'=>$selectedOfficeId]);
    $selectedOffice = $officeStmt->fetch();
    $selectedOfficeName = (string)($selectedOffice['name'] ?? '');
    $offices = $selectedOffice ? [$selectedOffice] : [];
    $employeesStmt = $db->prepare('SELECT u.id,u.first_name,u.last_name,u.email,u.is_active,u.last_login_at,u.updated_at,r.code role_code,r.name_key role_name_key,o.name office_name FROM users u INNER JOIN roles r ON r.id=u.role_id INNER JOIN offices o ON o.id=u.office_id WHERE u.office_id=:office_id AND u.deleted_at IS NULL ORDER BY u.last_name,u.first_name');
    $employeesStmt->execute(['office_id'=>$selectedOfficeId]);
}
$employees = $employeesStmt->fetchAll();

$currentUserStmt = $db->prepare('SELECT u.first_name,u.last_name,o.name AS office_name FROM users u LEFT JOIN offices o ON o.id=u.office_id WHERE u.id=:id LIMIT 1');
$currentUserStmt->execute(['id'=>(int)$auth->id()]);
$currentUser = $currentUserStmt->fetch() ?: ['first_name'=>'','last_name'=>'','office_name'=>''];

$languages = [];
foreach ($i18n->getLanguages() as $langRow) {
    $languages[(string)$langRow['code']] = $langRow;
}

$editUser = null;
$editAgent = null;
if ($mode === 'edit') {
    if ($requestedId < 1) {
        $error = __t('employees.error.not_found', 'Employee not found.');
        $mode = 'list';
    } else {
        $stmt = $db->prepare('SELECT u.id,u.office_id,u.role_id,u.first_name,u.last_name,u.email,u.phone,u.preferred_language_id,u.is_active,r.code role_code FROM users u INNER JOIN roles r ON r.id=u.role_id WHERE u.id=:id AND u.deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id'=>$requestedId]);
        $editUser = $stmt->fetch();
        if (!$editUser || (!$isSuperAdmin && (int)$editUser['office_id'] !== (int)$auth->officeId())) {
            $error = __t('employees.error.not_found', 'Employee not found.');
            $mode = 'list';
        } else {
            $stmt = $db->prepare('SELECT mobile,job_title,bio FROM agents WHERE user_id=:id AND deleted_at IS NULL LIMIT 1');
            $stmt->execute(['id'=>$requestedId]);
            $editAgent = $stmt->fetch() ?: ['mobile'=>'','job_title'=>'','bio'=>''];
        }
    }
}

$initials = strtoupper(substr((string)$currentUser['first_name'],0,1) . substr((string)$currentUser['last_name'],0,1));
if ($initials === '') $initials = 'PR';
?><!doctype html>
<html lang="<?=e($i18n->getLanguage())?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=e(__t('employees.title','Employees'))?> · Prrepl</title>
<link rel="stylesheet" href="<?=e($base)?>/assets/css/employees.css?v=41">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/_sidebar.php'; ?>

    <main class="main">

        <div class="page-content">
        <?php if ($error): ?><div class="immo-alert error"><span class="alert-icon">!</span><div><strong><?=e(__t('common.error','Something went wrong'))?></strong><p><?=e($error)?></p></div></div><?php endif; ?>
        <?php if (isset($_GET['created'])): ?><div class="immo-alert success"><span class="alert-icon">✓</span><div><strong><?=e(__t('common.saved','Saved'))?></strong><p><?=e(__t('employees.created','Employee created successfully.'))?></p></div></div><?php endif; ?>
        <?php if (isset($_GET['updated'])): ?><div class="immo-alert success"><span class="alert-icon">✓</span><div><strong><?=e(__t('common.saved','Saved'))?></strong><p><?=e(__t('employees.updated','Employee updated successfully.'))?></p></div></div><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><div class="immo-alert success"><span class="alert-icon">✓</span><div><strong><?=e(__t('common.saved','Saved'))?></strong><p><?=e(__t('employees.deleted','Employee deleted successfully.'))?></p></div></div><?php endif; ?>

        <?php if ($mode === 'list'): ?>
        <section class="page-heading">
            <div><div class="eyebrow"><?=e(__t('employees.kicker','PLATFORM'))?></div><h2><?=e(__t('employees.manage_title','Manage your team'))?></h2><p><?=e(__t('employees.subtitle','Manage users, roles and access for your offices.'))?></p></div>
            <?php if ($auth->can('users.create') || $isSuperAdmin): ?><a class="button-primary" href="?mode=create<?= $selectedOfficeId > 0 ? '&amp;office_id='.(int)$selectedOfficeId : '' ?>">+ <?=e(__t('employees.create','New employee'))?></a><?php endif; ?>
        </section>

        <section class="stats">
            <div class="stat"><span><?=e(__t('employees.total','Total employees'))?></span><strong><?=count($employees)?></strong><small><?=e($isSuperAdmin ? __t('employees.all_offices','Across all offices') : __t('employees.office_team','In your office'))?></small></div>
            <div class="stat"><span><?=e(__t('common.active','Active'))?></span><strong><?=count(array_filter($employees, fn($u)=>(int)$u['is_active']===1))?></strong><small><?=e(__t('employees.active_accounts','Active user accounts'))?></small></div>
            <div class="stat"><span><?=e(__t('employees.roles','Roles'))?></span><strong><?=count(array_unique(array_map(fn($u)=>(string)$u['role_code'],$employees)))?></strong><small><?=e(__t('employees.roles_used','Roles in use'))?></small></div>
            <div class="stat"><span><?=e(__t('employees.workspace','Workspace'))?></span><strong><?=e($isSuperAdmin ? __t('common.platform','Platform') : ($currentUser['office_name'] ?? '—'))?></strong><small><?=e($isSuperAdmin ? __t('employees.platform_scope','Platform scope') : __t('employees.office_scope_label','Office workspace'))?></small></div>
        </section>

        <section class="table-card directory-card">
            <div class="panel-head directory-head"><div><div class="eyebrow"><?=e(__t('employees.directory_kicker','DIRECTORY'))?></div><h3><?=e(__t('employees.directory','Employees'))?></h3></div><span class="directory-count"><?=count($employees)?></span></div>
            <div class="table-scroll"><table class="employee-table"><thead><tr><th><?=e(__t('common.name','Name'))?></th><th><?=e(__t('common.email','Email'))?></th><th><?=e(__t('common.role','Role'))?></th><th><?=e(__t('common.office','Office'))?></th><th><?=e(__t('common.status','Status'))?></th><th class="actions-head"><?=e(__t('common.actions','Action'))?></th></tr></thead><tbody>
            <?php foreach ($employees as $u): $uInitials=strtoupper(substr((string)$u['first_name'],0,1).substr((string)$u['last_name'],0,1)); ?>
            <tr>
                <td><div class="person"><span class="person-avatar"><?=e($uInitials)?></span><strong><?=e($u['first_name'].' '.$u['last_name'])?></strong></div></td>
                <td class="email-cell"><?=e($u['email'])?></td>
                <td><span class="role-badge"><?=e(__t($u['role_name_key'], ucfirst(str_replace('_',' ',$u['role_code']))))?></span></td>
                <td><?=e($u['office_name'] ?? '—')?></td>
                <td><span class="status-badge <?= (int)$u['is_active']===1?'active':'inactive' ?>"><i></i><?=e((int)$u['is_active']===1?__t('common.active','Active'):__t('common.inactive','Inactive'))?></span></td>
                <td class="actions-cell"><a class="table-edit" href="?mode=edit&amp;id=<?= (int)$u['id'] ?><?= $selectedOfficeId > 0 ? '&amp;office_id='.(int)$selectedOfficeId : '' ?>"><?=e(__t('common.edit','Edit'))?></a></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$employees): ?><tr><td colspan="6" class="empty-state"><strong><?=e(__t('employees.empty','No employees yet'))?></strong><span><?=e(__t('employees.empty_hint','Create your first employee to get started.'))?></span></td></tr><?php endif; ?>
            </tbody></table></div>
        </section>

        <?php else: ?>
        <?php
            $displayName = $mode==='edit' ? trim(($editUser['first_name'] ?? '').' '.($editUser['last_name'] ?? '')) : __t('employees.new_employee','New employee');
            $editInitials = $mode==='edit' ? strtoupper(substr((string)($editUser['first_name'] ?? ''),0,1).substr((string)($editUser['last_name'] ?? ''),0,1)) : '+';
        ?>
        <section class="employee-hero">
            <div class="employee-hero-avatar"><?=e($editInitials ?: 'P')?></div>
            <div class="employee-hero-main">
                <div class="breadcrumb"><a href="?mode=list<?= $selectedOfficeId > 0 ? '&amp;office_id='.(int)$selectedOfficeId : '' ?>">← <?=e(__t('employees.title','Employees'))?></a><span>·</span><span><?=e($mode==='edit'?__t('common.edit','Edit'):__t('common.create','Create'))?></span></div>
                <h2><?=e($displayName)?></h2>
                <p><?=e($mode==='edit'?($editUser['email'] ?? ''):__t('employees.create_subtitle','Create a new user and choose their access role.'))?></p>
            </div>
            <div class="employee-hero-actions">
                <?php if ($mode==='edit'): ?><span class="status-badge hero-status <?= (int)$editUser['is_active']===1?'active':'inactive' ?>"><i></i><?=e((int)$editUser['is_active']===1?__t('common.active','Active'):__t('common.inactive','Inactive'))?></span><?php endif; ?>
                <a class="secondary-button" href="?mode=list"><?=e(__t('common.cancel','Cancel'))?></a>
                <button form="employeeForm" class="button-save" type="submit"><?=e($mode==='edit'?__t('employees.save','Save changes'):__t('employees.create','Create employee'))?></button>
            </div>
        </section>

        <div class="completion-bar"><strong><?=e($mode==='edit'?__t('employees.edit_title','Edit employee'):__t('employees.create_title','Create employee'))?></strong><span><?=e($mode==='edit'?__t('employees.edit_hint','Update the employee details and access settings below.'):__t('employees.create_hint','Complete the employee profile and access settings below.'))?></span></div>

        <form class="editor-form" id="employeeForm" method="post">
            <input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="save"><input type="hidden" name="mode" value="<?=e($mode)?>"><input type="hidden" name="user_id" value="<?= (int)($editUser['id'] ?? 0) ?>">
            <div class="editor-grid">
                <section class="ux-card">
                    <div class="ux-card-head"><div><div class="eyebrow"><?=e(__t('employees.account_kicker','ACCOUNT'))?></div><h3><?=e(__t('employees.account_details','Account details'))?></h3></div><span class="ux-card-icon">P</span></div>
                    <div class="field-grid">
                        <div><label><?=e(__t('common.first_name','First name'))?> <b>*</b></label><input name="first_name" required value="<?=e($editUser['first_name'] ?? '')?>"></div>
                        <div><label><?=e(__t('common.last_name','Last name'))?> <b>*</b></label><input name="last_name" required value="<?=e($editUser['last_name'] ?? '')?>"></div>
                        <div><label><?=e(__t('common.email','Email'))?> <b>*</b></label><input type="email" name="email" required value="<?=e($editUser['email'] ?? '')?>"></div>
                        <div><label><?=e(__t('common.phone','Phone'))?></label><input name="phone" value="<?=e($editUser['phone'] ?? '')?>"></div>
                        <div><label><?=e(__t('employees.mobile','Mobile'))?></label><input name="mobile" value="<?=e($editAgent['mobile'] ?? '')?>"></div>
                        <div><label><?=e(__t('employees.job_title','Job title'))?></label><input name="job_title" value="<?=e($editAgent['job_title'] ?? '')?>"></div>
                        <div class="field-span-2"><label><?=e(__t('employees.bio','Bio'))?></label><textarea name="bio" rows="6"><?=e($editAgent['bio'] ?? '')?></textarea></div>
                    </div>
                </section>

                <section class="ux-card">
                    <div class="ux-card-head"><div><div class="eyebrow"><?=e(__t('employees.access_kicker','ACCESS'))?></div><h3><?=e(__t('employees.access_settings','Access settings'))?></h3></div><span class="ux-card-icon">⌘</span></div>
                    <div class="field-grid">
                        <div class="field-span-2"><label><?=e(__t('common.office','Office'))?> <b>*</b></label><select name="office_id" required <?=!$isSuperAdmin?'disabled':''?>><?php foreach($offices as $o): ?><option value="<?= (int)$o['id'] ?>" <?=((int)($editUser['office_id'] ?? $selectedOfficeId)===(int)$o['id']?'selected':'')?>><?=e($o['name'])?></option><?php endforeach; ?></select><?php if(!$isSuperAdmin): ?><input type="hidden" name="office_id" value="<?= (int)($auth->officeId() ?? 0) ?>"><?php endif; ?></div>
                        <div class="field-span-2"><label><?=e(__t('employees.role','Role'))?> <b>*</b></label><select name="role_code" required><option value="agent" <?=($editUser['role_code'] ?? '')==='agent'?'selected':''?>><?=e(__t('roles.agent','Agent'))?></option><option value="office_staff" <?=($editUser['role_code'] ?? '')==='office_staff'?'selected':''?>><?=e(__t('roles.office_staff','Office Staff'))?></option><option value="office_admin" <?=($editUser['role_code'] ?? '')==='office_admin'?'selected':''?>><?=e(__t('roles.office_admin','Office Admin'))?></option></select></div>
                        <div><label><?=e(__t('employees.language','Language'))?></label><select name="language_code"><?php foreach($languages as $code=>$langRow): ?><option value="<?=e($code)?>" <?=((int)($editUser['preferred_language_id'] ?? 0)===(int)$langRow['id']?'selected':'')?>><?=e($langRow['native_name'] ?? strtoupper($code))?></option><?php endforeach; ?></select></div>
                        <div><label><?=e(__t('employees.password','Password'))?> <?= $mode==='create'?'<b>*</b>':'' ?></label><input type="password" name="password" minlength="12" <?= $mode==='create'?'required':'' ?> autocomplete="new-password" placeholder="<?=e($mode==='edit'?__t('employees.password_hint','Leave blank to keep current password'):__t('employees.password_new','Minimum 12 characters'))?>"></div>
                    </div>
                    <div class="access-note"><?=e(__t('employees.role_help','Employee access is controlled by the selected role.'))?></div>
                </section>
            </div>

            <section class="ux-card status-card">
                <div class="status-row"><div><div class="eyebrow"><?=e(__t('employees.status_kicker','STATUS'))?></div><h3><?=e(__t('employees.account_status','Account status'))?></h3><p><?=e(__t('employees.status_help','Inactive users cannot sign in to Prrepl.'))?></p></div><label class="switch"><input type="checkbox" name="is_active" value="1" <?=((int)($editUser['is_active'] ?? 1)===1?'checked':'')?>><span></span><strong><?=e(__t('common.active','Active'))?></strong></label></div>
            </section>

            <?php if ($mode==='edit' && (int)$editUser['id'] !== (int)$auth->id()): ?>
            <section class="danger-zone"><div><div class="eyebrow danger-kicker"><?=e(__t('common.danger_zone','DANGER ZONE'))?></div><h3><?=e(__t('employees.danger_title','Account actions'))?></h3><p><?=e(__t('employees.danger_text','Deactivate access temporarily or permanently remove this employee from the workspace.'))?></p></div><div class="danger-actions"><button type="button" class="danger-outline" data-action-modal="status"><?=e((int)$editUser['is_active']===1?__t('employees.deactivate','Deactivate'):__t('employees.activate','Activate'))?></button><button type="button" class="danger-solid" data-action-modal="delete"><?=e(__t('employees.delete','Delete'))?></button></div></section>
            <?php endif; ?>
        </form>
        <?php endif; ?>
        </div>
    </main>
</div>

<?php if ($mode==='edit' && $editUser && (int)$editUser['id'] !== (int)$auth->id()): ?>
<div class="modal" id="actionModal" hidden><div class="modal-backdrop" data-close-modal></div><div class="modal-card" role="dialog" aria-modal="true"><div class="modal-icon" id="modalIcon">!</div><div class="modal-kicker">PRREPL</div><h3 id="modalTitle"></h3><p id="modalText"></p><div class="modal-actions"><button type="button" class="secondary-button" data-close-modal><?=e(__t('common.cancel','Cancel'))?></button><form method="post" id="modalForm"><input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" id="modalAction"><input type="hidden" name="user_id" value="<?= (int)$editUser['id'] ?>"><input type="hidden" name="is_active" id="modalStatus"><button class="modal-confirm" id="modalConfirm" type="submit"></button></form></div></div></div>
<script>
(function(){
 const modal=document.getElementById('actionModal'), title=document.getElementById('modalTitle'), text=document.getElementById('modalText'), icon=document.getElementById('modalIcon'), action=document.getElementById('modalAction'), status=document.getElementById('modalStatus'), confirm=document.getElementById('modalConfirm');
 function close(){modal.hidden=true;document.body.classList.remove('modal-open')}
 function open(type){const del=type==='delete'; const active=<?= (int)$editUser['is_active'] ?>===1; modal.hidden=false; document.body.classList.add('modal-open'); icon.textContent=del?'×':'!'; icon.className='modal-icon '+(del?'delete':'status'); title.textContent=del?<?=json_encode(__t('employees.delete_title','Delete employee?'))?>:(active?<?=json_encode(__t('employees.deactivate_title','Deactivate employee?'))?>:<?=json_encode(__t('employees.activate_title','Activate employee?'))?>); text.textContent=del?<?=json_encode(__t('employees.delete_text','This employee will be removed from the active workspace.'))?>:(active?<?=json_encode(__t('employees.deactivate_text','This employee will no longer be able to sign in.'))?>:<?=json_encode(__t('employees.activate_text','This employee will be able to sign in again.'))?>); action.value=del?'delete':'status'; status.value=active?'0':'1'; confirm.textContent=del?<?=json_encode(__t('employees.delete','Delete'))?>:(active?<?=json_encode(__t('employees.deactivate','Deactivate'))?>:<?=json_encode(__t('employees.activate','Activate'))?>); confirm.className='modal-confirm '+(del?'delete':'status'); }
 document.querySelectorAll('[data-action-modal]').forEach(b=>b.addEventListener('click',()=>open(b.dataset.actionModal)));
 document.querySelectorAll('[data-close-modal]').forEach(b=>b.addEventListener('click',close));
 document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!modal.hidden)close()});
})();
</script>
<?php endif; ?>
</body></html>
