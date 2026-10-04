<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Csrf;

$auth->requireLogin();
$isSuperAdmin = ($auth->role() ?? '') === 'super_admin';
if (!$isSuperAdmin) {
    http_response_code(403);
    exit(__t('common.access_denied', 'Access denied.'));
}

function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function uuidv4(): string {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

$base = rtrim((string)($config['app']['base_url'] ?? '/immobilier'), '/');
$mode = (string)($_GET['mode'] ?? 'list');
if (!in_array($mode, ['list', 'create', 'edit'], true)) $mode = 'list';
$requestedId = (int)($_GET['id'] ?? 0);
$message = '';
$error = '';
$csrf = Csrf::token();

// Keep language selection in the same database-backed i18n system used by the rest of Prrepl.
if (isset($_GET['lang'])) {
    $requestedLang = strtolower(trim((string)$_GET['lang']));
    $stmt = $db->prepare('SELECT id, code FROM i18n_languages WHERE code = :code AND is_active = 1 LIMIT 1');
    $stmt->execute(['code' => $requestedLang]);
    if ($row = $stmt->fetch()) {
        $db->prepare('UPDATE users SET preferred_language_id = :language_id WHERE id = :user_id')
            ->execute(['language_id' => (int)$row['id'], 'user_id' => (int)$auth->id()]);
        $i18n->setLanguage((string)$row['code']);
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Csrf::verify((string)($_POST['_csrf'] ?? ''))) {
            throw new RuntimeException(__t('common.session_expired', 'Your session expired. Please try again.'));
        }

        $action = (string)($_POST['action'] ?? '');
        $officeId = (int)($_POST['office_id'] ?? 0);

        if ($action === 'save') {
            $postMode = (string)($_POST['mode'] ?? 'create');
            $name = trim((string)($_POST['name'] ?? ''));
            $officeCode = strtoupper(trim((string)($_POST['office_code'] ?? '')));
            $legalName = trim((string)($_POST['legal_name'] ?? '')) ?: null;
            $email = trim((string)($_POST['email'] ?? '')) ?: null;
            $phone = trim((string)($_POST['phone'] ?? '')) ?: null;
            $website = trim((string)($_POST['website'] ?? '')) ?: null;
            $address1 = trim((string)($_POST['address_line_1'] ?? '')) ?: null;
            $address2 = trim((string)($_POST['address_line_2'] ?? '')) ?: null;
            $postcode = trim((string)($_POST['postcode'] ?? '')) ?: null;
            $city = trim((string)($_POST['city'] ?? '')) ?: null;
            $country = strtoupper(trim((string)($_POST['country_code'] ?? 'FR')));
            $defaultLanguage = strtolower(trim((string)($_POST['default_language'] ?? 'fr')));
            $timezone = trim((string)($_POST['timezone'] ?? 'Europe/Paris')) ?: 'Europe/Paris';
            $currency = strtoupper(trim((string)($_POST['currency_code'] ?? 'EUR')));
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if ($name === '') throw new RuntimeException(__t('offices.error.name', 'Office name is required.'));
            if ($officeCode === '') { $letters=preg_replace('/[^A-Z]/','',strtoupper(iconv('UTF-8','ASCII//TRANSLIT',$name) ?: $name)); $officeCode=substr($letters,0,3); }
            if (!preg_match('/^[A-Z]{3}$/', $officeCode)) throw new RuntimeException(__t('offices.error.code', 'Office code must contain exactly three letters.'));
            if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException(__t('offices.error.email', 'Please enter a valid email address.'));
            }
            if (!preg_match('/^[A-Z]{2}$/', $country)) {
                throw new RuntimeException(__t('offices.error.country', 'Country code must contain two letters.'));
            }
            if (!preg_match('/^[A-Z]{3}$/', $currency)) {
                throw new RuntimeException(__t('offices.error.currency', 'Currency code must contain three letters.'));
            }

            $langStmt = $db->prepare('SELECT id FROM i18n_languages WHERE code = :code AND is_active = 1 LIMIT 1');
            $langStmt->execute(['code' => $defaultLanguage]);
            $languageId = $langStmt->fetchColumn();
            if (!$languageId) {
                $languageId = $db->query('SELECT id FROM i18n_languages WHERE is_default = 1 AND is_active = 1 LIMIT 1')->fetchColumn();
            }

            $codeStmt=$db->prepare('SELECT id FROM offices WHERE office_code=:code AND deleted_at IS NULL AND id<>:id LIMIT 1');
            $codeStmt->execute(['code'=>$officeCode,'id'=>$officeId]);
            if ($codeStmt->fetchColumn()) throw new RuntimeException(__t('offices.error.code_duplicate','This office code is already in use.'));

            if ($postMode === 'create') {
                $stmt = $db->prepare('SELECT id FROM offices WHERE LOWER(name)=LOWER(:name) AND deleted_at IS NULL LIMIT 1');
                $stmt->execute(['name' => $name]);
                if ($stmt->fetchColumn()) throw new RuntimeException(__t('offices.error.duplicate', 'An office with this name already exists.'));

                $stmt = $db->prepare('INSERT INTO offices (public_id,name,office_code,legal_name,email,phone,website,address_line_1,address_line_2,postcode,city,country_code,default_language_id,timezone,currency_code,is_active) VALUES (:public_id,:name,:office_code,:legal_name,:email,:phone,:website,:address1,:address2,:postcode,:city,:country,:language_id,:timezone,:currency,:active)');
                $stmt->execute([
                    'public_id'=>uuidv4(),'name'=>$name,'office_code'=>$officeCode,'legal_name'=>$legalName,'email'=>$email,'phone'=>$phone,'website'=>$website,
                    'address1'=>$address1,'address2'=>$address2,'postcode'=>$postcode,'city'=>$city,'country'=>$country,
                    'language_id'=>$languageId ?: null,'timezone'=>$timezone,'currency'=>$currency,'active'=>$isActive,
                ]);
                header('Location: '.$base.'/admin/offices.php?created=1'); exit;
            }

            if ($postMode !== 'edit' || $officeId < 1) throw new RuntimeException(__t('offices.error.invalid', 'Invalid office.'));
            $stmt = $db->prepare('SELECT id FROM offices WHERE id=:id AND deleted_at IS NULL LIMIT 1');
            $stmt->execute(['id'=>$officeId]);
            if (!$stmt->fetchColumn()) throw new RuntimeException(__t('offices.error.not_found', 'Office not found.'));

            $stmt = $db->prepare('SELECT id FROM offices WHERE LOWER(name)=LOWER(:name) AND id<>:id AND deleted_at IS NULL LIMIT 1');
            $stmt->execute(['name'=>$name,'id'=>$officeId]);
            if ($stmt->fetchColumn()) throw new RuntimeException(__t('offices.error.duplicate', 'An office with this name already exists.'));

            $stmt = $db->prepare('UPDATE offices SET name=:name,office_code=:office_code,legal_name=:legal_name,email=:email,phone=:phone,website=:website,address_line_1=:address1,address_line_2=:address2,postcode=:postcode,city=:city,country_code=:country,default_language_id=:language_id,timezone=:timezone,currency_code=:currency,is_active=:active,updated_at=NOW() WHERE id=:id');
            $stmt->execute([
                'id'=>$officeId,'name'=>$name,'office_code'=>$officeCode,'legal_name'=>$legalName,'email'=>$email,'phone'=>$phone,'website'=>$website,
                'address1'=>$address1,'address2'=>$address2,'postcode'=>$postcode,'city'=>$city,'country'=>$country,
                'language_id'=>$languageId ?: null,'timezone'=>$timezone,'currency'=>$currency,'active'=>$isActive,
            ]);
            header('Location: '.$base.'/admin/offices.php?updated=1'); exit;
        }

        if ($action === 'status') {
            if ($officeId < 1) throw new RuntimeException(__t('offices.error.invalid', 'Invalid office.'));
            $active = (int)($_POST['is_active'] ?? 0) === 1 ? 1 : 0;
            $stmt = $db->prepare('SELECT id FROM offices WHERE id=:id AND deleted_at IS NULL LIMIT 1');
            $stmt->execute(['id'=>$officeId]);
            if (!$stmt->fetchColumn()) throw new RuntimeException(__t('offices.error.not_found', 'Office not found.'));
            $db->prepare('UPDATE offices SET is_active=:active,updated_at=NOW() WHERE id=:id')->execute(['active'=>$active,'id'=>$officeId]);
            header('Location: '.$base.'/admin/offices.php?updated=1'); exit;
        }

        if ($action === 'delete') {
            if ($officeId < 1) throw new RuntimeException(__t('offices.error.invalid', 'Invalid office.'));
            $stmt = $db->prepare('SELECT id FROM offices WHERE id=:id AND deleted_at IS NULL LIMIT 1');
            $stmt->execute(['id'=>$officeId]);
            if (!$stmt->fetchColumn()) throw new RuntimeException(__t('offices.error.not_found', 'Office not found.'));

            // Soft-delete the office; users/properties remain historically linked to it.
            $db->prepare('UPDATE offices SET deleted_at=NOW(),is_active=0,updated_at=NOW() WHERE id=:id')->execute(['id'=>$officeId]);
            header('Location: '.$base.'/admin/offices.php?deleted=1'); exit;
        }
    }
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    $error = $e->getMessage();
}

$offices = $db->query(
    'SELECT o.id,o.name,o.legal_name,o.email,o.phone,o.website,o.address_line_1,o.address_line_2,o.postcode,o.city,o.country_code,o.default_language_id,o.timezone,o.currency_code,o.is_active,o.created_at,o.updated_at,
            COUNT(DISTINCT CASE WHEN u.deleted_at IS NULL THEN u.id END) AS employee_count,
            COUNT(DISTINCT CASE WHEN p.deleted_at IS NULL THEN p.id END) AS property_count
     FROM offices o
     LEFT JOIN users u ON u.office_id=o.id
     LEFT JOIN properties p ON p.office_id=o.id
     WHERE o.deleted_at IS NULL
     GROUP BY o.id
     ORDER BY o.name ASC'
)->fetchAll();

$currentUserStmt = $db->prepare('SELECT u.first_name,u.last_name,o.name AS office_name FROM users u LEFT JOIN offices o ON o.id=u.office_id WHERE u.id=:id LIMIT 1');
$currentUserStmt->execute(['id'=>(int)$auth->id()]);
$currentUser = $currentUserStmt->fetch() ?: ['first_name'=>'','last_name'=>'','office_name'=>'Platform'];

$languages = [];
foreach ($i18n->getLanguages() as $langRow) $languages[(string)$langRow['code']] = $langRow;

$editOffice = null;
if ($mode === 'edit') {
    if ($requestedId < 1) {
        $error = __t('offices.error.not_found','Office not found.');
        $mode = 'list';
    } else {
        $stmt = $db->prepare('SELECT id,name,office_code,legal_name,email,phone,website,address_line_1,address_line_2,postcode,city,country_code,default_language_id,timezone,currency_code,is_active FROM offices WHERE id=:id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id'=>$requestedId]);
        $editOffice = $stmt->fetch() ?: null;
        if (!$editOffice) { $error=__t('offices.error.not_found','Office not found.'); $mode='list'; }
    }
}

$total = count($offices);
$activeCount = 0;
$employeeTotal = 0;
$propertyTotal = 0;
foreach ($offices as $office) {
    if ((int)$office['is_active'] === 1) $activeCount++;
    $employeeTotal += (int)$office['employee_count'];
    $propertyTotal += (int)$office['property_count'];
}

$initials = strtoupper(substr((string)$currentUser['first_name'],0,1).substr((string)$currentUser['last_name'],0,1));
if ($initials === '') $initials='PR';
?><!doctype html>
<html lang="<?=e($i18n->getLanguage())?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e(__t('offices.title','Offices'))?> · Prrepl</title>
<link rel="stylesheet" href="<?=e($base)?>/assets/css/dashboard.css"><link rel="stylesheet" href="<?=e($base)?>/assets/css/offices.css?v=40">
</head>
<body>
<div class="app-shell">
<?php require __DIR__ . '/_sidebar.php'; ?>
<main class="main">

<?php if ($message || isset($_GET['created']) || isset($_GET['updated']) || isset($_GET['deleted'])): ?>
<div class="notice success"><span class="notice-icon">✓</span><div><strong><?=e(__t('common.saved','Changes saved'))?></strong><p><?=e($message ?: (isset($_GET['created']) ? __t('offices.created','Office created successfully.') : (isset($_GET['deleted']) ? __t('offices.deleted','Office deleted.') : __t('offices.updated','Office updated successfully.'))))?></p></div></div>
<?php endif; ?>
<?php if ($error): ?><div class="notice error"><span class="notice-icon">!</span><div><strong><?=e(__t('common.error','Something went wrong'))?></strong><p><?=e($error)?></p></div></div><?php endif; ?>

<?php if ($mode === 'list'): ?>
<section class="page-heading"><div><div class="eyebrow"><?=e(__t('offices.kicker','PLATFORM'))?></div><h2><?=e(__t('offices.manage','Manage your offices'))?></h2><p><?=e(__t('offices.subtitle','Manage offices, contact details and workspace settings.'))?></p></div><a class="button-primary" href="?mode=create">+ <?=e(__t('offices.create','New office'))?></a></section>
<section class="stats">
  <div class="stat"><span><?=e(__t('offices.total','TOTAL OFFICES'))?></span><strong><?=$total?></strong><small><?=e(__t('offices.total_hint','All active records'))?></small></div>
  <div class="stat"><span><?=e(__t('offices.active','ACTIVE'))?></span><strong><?=$activeCount?></strong><small><?=e(__t('offices.active_hint','Active workspaces'))?></small></div>
  <div class="stat"><span><?=e(__t('offices.employees','EMPLOYEES'))?></span><strong><?=$employeeTotal?></strong><small><?=e(__t('offices.employees_hint','User accounts'))?></small></div>
  <div class="stat"><span><?=e(__t('offices.properties','PROPERTIES'))?></span><strong><?=$propertyTotal?></strong><small><?=e(__t('offices.properties_hint','Assigned properties'))?></small></div>
</section>
<section class="table-card">
  <div class="table-head"><div><div class="eyebrow"><?=e(__t('offices.directory','DIRECTORY'))?></div><h3><?=e(__t('offices.all','Offices'))?></h3></div><span class="count"><?=$total?></span></div>
  <?php if (!$offices): ?><div class="empty"><strong><?=e(__t('offices.empty','No offices yet'))?></strong><span><?=e(__t('offices.empty_hint','Create your first office to get started.'))?></span></div>
  <?php else: ?>
  <div class="table-scroll"><table class="office-table"><thead><tr><th><?=e(__t('common.office','Office'))?></th><th><?=e(__t('common.location','Location'))?></th><th><?=e(__t('common.employees','Employees'))?></th><th><?=e(__t('common.properties','Properties'))?></th><th><?=e(__t('common.status','Status'))?></th><th><?=e(__t('common.actions','Actions'))?></th></tr></thead><tbody>
  <?php foreach ($offices as $o): ?>
  <tr>
    <td><a class="office-name-link" href="<?=$base?>/admin/employees.php?office_id=<?= (int)$o['id']?>"><div class="office-cell"><div class="office-avatar">⌂</div><div><strong><?=e($o['name'])?></strong><span><?=e($o['legal_name'] ?: ($o['email'] ?: '—'))?></span></div></div></a></td>
    <td><span class="location-main"><?=e($o['city'] ?: '—')?></span><span class="location-sub"><?=e($o['country_code'] ?: '')?></span></td>
    <td><strong class="metric"><?= (int)$o['employee_count'] ?></strong></td>
    <td><strong class="metric"><?= (int)$o['property_count'] ?></strong></td>
    <td><span class="status-pill <?= (int)$o['is_active']===1?'active':'inactive' ?>"><i></i><?=e((int)$o['is_active']===1?__t('common.active','Active'):__t('common.inactive','Inactive'))?></span></td>
    <td><div class="actions"><a href="?mode=edit&id=<?= (int)$o['id'] ?>" class="action-edit">✎ <span><?=e(__t('common.edit','Edit'))?></span></a></div></td>
  </tr>
  <?php endforeach; ?></tbody></table></div>
  <?php endif; ?>
</section>
<?php else: ?>
<section class="edit-heading"><div><div class="breadcrumb"><a href="?">← <?=e(__t('offices.title','Offices'))?></a><span>·</span><?=e($mode==='create'?__t('offices.new','New office'):__t('offices.edit','Edit office'))?></div><h2><?=e($mode==='create'?__t('offices.new','New office'):__t('offices.edit','Edit office'))?></h2><p><?=e($mode==='create'?__t('offices.create_hint','Create a new office and configure its workspace settings.'):__t('offices.edit_hint','Update the office details and workspace settings.'))?></p></div><span class="scope-badge"><?=e(__t('common.platform','Platform'))?></span></section>
<form method="post" class="office-form" id="officeForm">
<input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="save"><input type="hidden" name="mode" value="<?=e($mode)?>"><input type="hidden" name="office_id" value="<?= (int)($editOffice['id'] ?? 0) ?>">
<section class="form-grid">
  <div class="form-card"><div class="form-card-head"><div><div class="eyebrow"><?=e(__t('offices.office_kicker','OFFICE'))?></div><h3><?=e(__t('offices.details','Office details'))?></h3></div><div class="card-icon">⌂</div></div>
    <div class="fields two"><label><?=e(__t('common.name','Name'))?> *<input name="name" required value="<?=e($editOffice['name'] ?? '')?>"></label><label><?=e(__t('offices.code','Office code'))?> *<input name="office_code" maxlength="3" minlength="3" pattern="[A-Za-z]{3}" required value="<?=e($editOffice['office_code'] ?? '')?>"><small class="small"><?=e(__t('offices.code_help','3 letters used for property references, e.g. CDS-00001.'))?></small></label><label><?=e(__t('offices.legal_name','Legal name'))?><input name="legal_name" value="<?=e($editOffice['legal_name'] ?? '')?>"></label><label><?=e(__t('common.email','Email'))?><input type="email" name="email" value="<?=e($editOffice['email'] ?? '')?>"></label><label><?=e(__t('common.phone','Phone'))?><input name="phone" value="<?=e($editOffice['phone'] ?? '')?>"></label><label class="span-2"><?=e(__t('offices.website','Website'))?><input type="url" name="website" placeholder="https://" value="<?=e($editOffice['website'] ?? '')?>"></label></div>
  </div>
  <div class="form-card"><div class="form-card-head"><div><div class="eyebrow"><?=e(__t('offices.location_kicker','LOCATION'))?></div><h3><?=e(__t('offices.address','Address'))?></h3></div><div class="card-icon">⌖</div></div>
    <div class="fields two"><label class="span-2"><?=e(__t('offices.address1','Address line 1'))?><input name="address_line_1" value="<?=e($editOffice['address_line_1'] ?? '')?>"></label><label class="span-2"><?=e(__t('offices.address2','Address line 2'))?><input name="address_line_2" value="<?=e($editOffice['address_line_2'] ?? '')?>"></label><label><?=e(__t('offices.postcode','Postcode'))?><input name="postcode" value="<?=e($editOffice['postcode'] ?? '')?>"></label><label><?=e(__t('common.city','City'))?><input name="city" value="<?=e($editOffice['city'] ?? '')?>"></label><label><?=e(__t('offices.country','Country code'))?><input name="country_code" maxlength="2" value="<?=e($editOffice['country_code'] ?? 'FR')?>"></label></div>
  </div>
  <div class="form-card"><div class="form-card-head"><div><div class="eyebrow"><?=e(__t('offices.settings_kicker','SETTINGS'))?></div><h3><?=e(__t('offices.settings','Workspace settings'))?></h3></div><div class="card-icon">⚙</div></div>
    <div class="fields two"><label><?=e(__t('offices.language','Default language'))?><select name="default_language"><?php foreach($languages as $code=>$lang): ?><option value="<?=e($code)?>" <?=((int)($editOffice['default_language_id'] ?? 0)===(int)$lang['id'])||(!$editOffice && $code==='fr')?'selected':''?>><?=e($lang['native_name'] ?? strtoupper($code))?></option><?php endforeach; ?></select></label><label><?=e(__t('offices.timezone','Timezone'))?><input name="timezone" value="<?=e($editOffice['timezone'] ?? 'Europe/Paris')?>"></label><label><?=e(__t('offices.currency','Currency'))?><input name="currency_code" maxlength="3" value="<?=e($editOffice['currency_code'] ?? 'EUR')?>"></label><div class="status-field"><span><?=e(__t('common.status','Status'))?></span><label class="switch"><input type="checkbox" name="is_active" value="1" <?=((int)($editOffice['is_active'] ?? 1)===1)?'checked':''?>><i></i><b><?=e(__t('common.active','Active'))?></b></label></div></div>
  </div>
  <?php if ($editOffice): ?>
  <div class="form-card danger-zone"><div class="form-card-head"><div><div class="eyebrow danger-eyebrow"><?=e(__t('common.danger_zone','DANGER ZONE'))?></div><h3><?=e(__t('offices.danger','Office actions'))?></h3></div><div class="card-icon danger-icon">!</div></div>
    <div class="danger-row"><div><strong><?=e((int)$editOffice['is_active']===1?__t('offices.deactivate','Deactivate office'):__t('offices.activate','Activate office'))?></strong><span><?=e((int)$editOffice['is_active']===1?__t('offices.deactivate_hint','Prevent this office from being used as an active workspace.'):__t('offices.activate_hint','Make this office available again.'))?></span></div><button type="button" class="danger-action warning" data-modal="statusModal"><?=e((int)$editOffice['is_active']===1?__t('common.deactivate','Deactivate'):__t('common.activate','Activate'))?></button></div>
    <div class="danger-row"><div><strong><?=e(__t('offices.delete','Delete office'))?></strong><span><?=e(__t('offices.delete_hint','Remove this office from the active office directory.'))?></span></div><button type="button" class="danger-action delete" data-modal="deleteModal"><?=e(__t('common.delete','Delete'))?></button></div>
  </div>
  <?php endif; ?>
</section>
<div class="form-footer"><a class="button-secondary" href="?"><?=e(__t('common.cancel','Cancel'))?></a><button class="button-primary" type="submit"><?=e($mode==='create'?__t('offices.create','Create office'):__t('common.save_changes','Save changes'))?> <span>→</span></button></div>
</form>
<?php endif; ?>
</main></div>

<?php if ($editOffice): ?>
<div class="modal-backdrop" id="statusModal" hidden><div class="confirm-modal"><button class="modal-close" type="button">×</button><div class="modal-icon warning">◐</div><div class="eyebrow"><?=e(__t('offices.status_kicker','OFFICE STATUS'))?></div><h2><?=e((int)$editOffice['is_active']===1?__t('offices.deactivate','Deactivate office'):__t('offices.activate','Activate office'))?>?</h2><p><?=e((int)$editOffice['is_active']===1?__t('offices.deactivate_confirm','Are you sure you want to deactivate this office? It will no longer be available as an active workspace.'):__t('offices.activate_confirm','Are you sure you want to activate this office?'))?></p><div class="modal-actions"><button type="button" class="button-secondary modal-cancel"><?=e(__t('common.cancel','Cancel'))?></button><form method="post"><input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="status"><input type="hidden" name="office_id" value="<?= (int)$editOffice['id'] ?>"><input type="hidden" name="is_active" value="<?= (int)$editOffice['is_active']===1?0:1 ?>"><button class="modal-warning" type="submit"><?=e((int)$editOffice['is_active']===1?__t('common.deactivate','Deactivate'):__t('common.activate','Activate'))?></button></form></div></div></div>
<div class="modal-backdrop" id="deleteModal" hidden><div class="confirm-modal"><button class="modal-close" type="button">×</button><div class="modal-icon delete">⌫</div><div class="eyebrow danger-eyebrow"><?=e(__t('common.danger_zone','DANGER ZONE'))?></div><h2><?=e(__t('offices.delete','Delete office'))?>?</h2><p><?=e(__t('offices.delete_confirm','This office will be removed from the active office directory. Existing users and properties will keep their historical link to the office.'))?></p><div class="modal-actions"><button type="button" class="button-secondary modal-cancel"><?=e(__t('common.cancel','Cancel'))?></button><form method="post"><input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="office_id" value="<?= (int)$editOffice['id'] ?>"><button class="modal-delete" type="submit"><?=e(__t('common.delete','Delete'))?></button></form></div></div></div>
<?php endif; ?>
<script>
(function(){
  document.querySelectorAll('[data-modal]').forEach(function(btn){btn.addEventListener('click',function(){var m=document.getElementById(btn.dataset.modal);if(m){m.hidden=false;document.body.classList.add('modal-open');}});});
  document.querySelectorAll('.modal-backdrop').forEach(function(m){m.addEventListener('click',function(e){if(e.target===m){m.hidden=true;document.body.classList.remove('modal-open');}});m.querySelectorAll('.modal-close,.modal-cancel').forEach(function(b){b.addEventListener('click',function(){m.hidden=true;document.body.classList.remove('modal-open');});});});
  document.addEventListener('keydown',function(e){if(e.key==='Escape')document.querySelectorAll('.modal-backdrop:not([hidden])').forEach(function(m){m.hidden=true;document.body.classList.remove('modal-open');});});
})();
</script>
</body></html>
