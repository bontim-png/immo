<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
$auth->requireLogin();

function propertyDeleteBack(string $key='delete_error'): never {
    header('Location: /immobilier/admin/properties.php?'.$key.'=1');
    exit;
}
function propertyDeleteDenied(): never {
    http_response_code(403);
    ?><!doctype html><html lang="<?=htmlspecialchars($i18n->getLanguage(),ENT_QUOTES,'UTF-8')?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=htmlspecialchars(__t('common.access_denied','Access denied.'),ENT_QUOTES,'UTF-8')?></title><style>body{margin:0;background:#f6f8fb;font-family:Inter,system-ui,sans-serif;color:#172033;display:grid;place-items:center;min-height:100vh}.box{width:min(520px,calc(100% - 40px));background:#fff;border:1px solid #e4e7ec;border-radius:20px;padding:32px;box-shadow:0 20px 60px rgba(16,24,40,.1)}.icon{width:48px;height:48px;border-radius:14px;background:#fef3f2;color:#b42318;display:grid;place-items:center;font-weight:800;font-size:22px}.box h1{margin:18px 0 8px}.box p{color:#667085;line-height:1.6}.btn{display:inline-block;margin-top:10px;padding:11px 16px;border-radius:10px;background:#172033;color:#fff;text-decoration:none}</style></head><body><div class="box"><div class="icon">!</div><h1><?=htmlspecialchars(__t('common.access_denied','Access denied.'),ENT_QUOTES,'UTF-8')?></h1><p><?=htmlspecialchars(__t('properties.delete_access_error','You do not have permission to delete this property.'),ENT_QUOTES,'UTF-8')?></p><a class="btn" href="/immobilier/admin/properties.php"><?=htmlspecialchars(__t('properties.back_to_properties','Back to properties'),ENT_QUOTES,'UTF-8')?></a></div></body></html><?php
    exit;
}
try {
    \App\Csrf::verify($_POST['_csrf'] ?? '');
    $id=(int)($_POST['property_id']??$_POST['id']??0);
    if($id<1) propertyDeleteBack();
    $role=(string)$auth->role(); $office=(int)($auth->officeId()??0); $uid=(int)$auth->id(); $agentId=null;
    if($role==='agent'){
        $st=$db->prepare('SELECT id,office_id FROM agents WHERE user_id=:u AND deleted_at IS NULL AND is_active=1 LIMIT 1');
        $st->execute(['u'=>$uid]); $a=$st->fetch();
        if(!$a) propertyDeleteDenied();
        $agentId=(int)$a['id']; $office=(int)$a['office_id'];
    }
    if(!in_array($role,['super_admin','office_admin','agent'],true)) propertyDeleteDenied();
    $where='id=:id AND deleted_at IS NULL'; $params=['id'=>$id];
    if($role==='office_admin'){ $where.=' AND office_id=:o'; $params['o']=$office; }
    if($role==='agent'){ $where.=' AND office_id=:o AND agent_id=:a'; $params['o']=$office; $params['a']=$agentId; }
    $st=$db->prepare("SELECT id FROM properties WHERE $where LIMIT 1"); $st->execute($params);
    if(!$st->fetchColumn()) propertyDeleteDenied();
    $st=$db->prepare('UPDATE properties SET deleted_at=NOW() WHERE id=:id AND deleted_at IS NULL');
    $st->execute(['id'=>$id]);
    if($st->rowCount()!==1) propertyDeleteBack();
    header('Location: /immobilier/admin/properties.php?deleted=1'); exit;
} catch (Throwable $e) {
    propertyDeleteBack();
}
