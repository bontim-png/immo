<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
$auth->requireLogin();
if (isset($_GET['lang'])) { $requestedLang=strtolower(trim((string)$_GET['lang'])); $langStmt=$db->prepare('SELECT id,code FROM i18n_languages WHERE code=:code AND is_active=1 LIMIT 1'); $langStmt->execute(['code'=>$requestedLang]); if($langRow=$langStmt->fetch()){ $db->prepare('UPDATE users SET preferred_language_id=:l WHERE id=:u')->execute(['l'=>(int)$langRow['id'],'u'=>(int)$auth->id()]); $i18n->setLanguage((string)$langRow['code']); } }
function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$role=(string)$auth->role(); $userId=(int)$auth->id(); $officeId=$auth->officeId(); $agentId=null;
if($role==='agent'){
 $s=$db->prepare('SELECT id,office_id FROM agents WHERE user_id=:u AND deleted_at IS NULL AND is_active=1 LIMIT 1'); $s->execute(['u'=>$userId]); $a=$s->fetch();
 if(!$a){http_response_code(403); exit('No active agent profile is linked to this user.');} $agentId=(int)$a['id']; $officeId=(int)$a['office_id'];
}
$message='';$error='';
if(($_GET['deleted']??'')==='1') $message=__t('properties.deleted','Property deleted successfully.');
if(($_GET['delete_error']??'')==='1') $error=__t('properties.delete_error','The property could not be deleted. Please check your permissions and try again.');
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  \App\Csrf::verify($_POST['_csrf']??'');
  $action=$_POST['action']??''; $id=(int)($_POST['id']??0);
  if($action==='delete'){
   if($id<1) throw new RuntimeException('Invalid property.');
   $where='p.id=:id AND p.deleted_at IS NULL';$params=['id'=>$id];
   if($role==='office_admin'){ $where.=' AND p.office_id=:o';$params['o']=(int)$officeId; }
   elseif($role==='agent'){ $where.=' AND p.agent_id=:a';$params['a']=(int)$agentId; }
   $s=$db->prepare("SELECT id FROM properties p WHERE $where LIMIT 1");$s->execute($params);
   if(!$s->fetchColumn()) throw new RuntimeException('Property not found or access denied.');
   $s=$db->prepare('UPDATE properties SET deleted_at=NOW() WHERE id=:id');$s->execute(['id'=>$id]);
   $message=__t('properties.deleted','Property deleted successfully.');
  }
 }catch(Throwable $ex){$error=$ex->getMessage();}
}
$where=['p.deleted_at IS NULL'];$params=[];
if($role==='office_admin'){ $where[]='p.office_id=:o';$params['o']=(int)$officeId; }
elseif($role==='agent'){ $where[]='p.agent_id=:a';$params['a']=(int)$agentId; }
$q=trim((string)($_GET['q']??'')); if($q!==''){ $where[]='(p.reference LIKE :q OR p.title LIKE :q OR p.city LIKE :q)';$params['q']="%$q%"; }
$status=trim((string)($_GET['status']??'')); if(in_array($status,['draft','active','sold','rented','withdrawn'],true)){ $where[]='p.status=:st';$params['st']=$status; }
$sql='SELECT p.id,p.reference,p.title,p.status,p.transaction_type,p.price,p.currency_code,p.city,p.living_area_m2,p.bedrooms,p.has_pool,p.pool_length_m,p.pool_width_m,p.pool_depth_m,p.is_featured,p.is_published,o.name office_name,CONCAT(a.first_name," ",a.last_name) agent_name,pt.code property_type_code,pm.file_path primary_photo FROM properties p INNER JOIN offices o ON o.id=p.office_id LEFT JOIN agents a ON a.id=p.agent_id LEFT JOIN property_types pt ON pt.id=p.property_type_id LEFT JOIN property_media pm ON pm.property_id=p.id AND pm.is_primary=1 AND pm.deleted_at IS NULL WHERE '.implode(' AND ',$where).' ORDER BY p.created_at DESC,p.id DESC';
$s=$db->prepare($sql);$s->execute($params);$properties=$s->fetchAll();$csrf=\App\Csrf::token(); $languages=$db->query('SELECT code,native_name FROM i18n_languages WHERE is_active=1 ORDER BY id')->fetchAll();
?><?php $t=fn(string $k,string $fallback=''):string=>__t($k,$fallback); ?>
<!doctype html><html lang="<?=e($i18n->getLanguage())?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e(__t('properties.title','Properties'))?> - Immo</title><style>
body{margin:0;background:#f5f7fb;color:#172033;font-family:Inter,system-ui,sans-serif}.wrap{max-width:1450px;margin:auto;padding:30px 24px}.top{display:flex;justify-content:space-between;gap:20px;align-items:center}.top-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.languages{display:flex;gap:4px}.lang{font-size:20px;text-decoration:none;padding:5px 7px;border-radius:8px;opacity:.65}.lang.active{opacity:1;background:#eef2f6;box-shadow:inset 0 0 0 1px #d0d5dd}.card{background:#fff;border:1px solid #e4e7ec;border-radius:14px;padding:22px;margin:20px 0}.nav{display:flex;gap:8px;flex-wrap:wrap}.nav a{padding:9px 13px;border:1px solid #e4e7ec;border-radius:9px;text-decoration:none;background:#fff}.nav a.active{background:#111827;color:#fff}.toolbar{display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap}.filters{display:flex;gap:8px;flex-wrap:wrap}input,select{padding:10px;border:1px solid #d0d5dd;border-radius:9px}button,.btn{border:0;border-radius:9px;padding:9px 12px;background:#111827;color:#fff;text-decoration:none;cursor:pointer}.btn.light{background:#fff;color:#172033;border:1px solid #d0d5dd}.danger{background:#b42318}table{width:100%;border-collapse:collapse;min-width:1050px}th,td{text-align:left;padding:12px;border-bottom:1px solid #eef0f3}.table{overflow:auto}.thumb{width:68px;height:50px;object-fit:cover;border-radius:7px;background:#eee}.muted{color:#667085;font-size:12px}.pill{display:inline-block;padding:4px 8px;border-radius:999px;background:#f2f4f7;font-size:12px}.notice,.error{padding:12px;border-radius:9px}.notice{background:#ecfdf3;color:#067647}.error{background:#fef3f2;color:#b42318}@media(max-width:700px){.top{align-items:flex-start;flex-direction:column}}


:root{--immo-bg:#f6f8fb;--immo-card:#fff;--immo-border:#e4e7ec;--immo-text:#172033;--immo-muted:#667085;--immo-blue:#315cf6;--immo-danger:#b42318;--immo-danger-bg:#fef3f2;--immo-success:#067647;--immo-success-bg:#ecfdf3;--immo-warning:#b54708;--immo-warning-bg:#fffaeb}
.immo-alert{display:flex;align-items:flex-start;gap:13px;padding:15px 17px;border:1px solid;border-radius:14px;margin:16px 0;background:#fff;box-shadow:0 4px 14px rgba(16,24,40,.04)}
.immo-alert .alert-icon{width:34px;height:34px;flex:0 0 34px;border-radius:10px;display:grid;place-items:center;font-weight:800}
.immo-alert strong{display:block;margin-bottom:3px}.immo-alert p{margin:0;color:var(--immo-muted);font-size:13px;line-height:1.5}
.immo-alert.success{border-color:#abefc6;background:var(--immo-success-bg);color:var(--immo-success)}.immo-alert.success .alert-icon{background:#d1fadf}
.immo-alert.error{border-color:#fecdca;background:var(--immo-danger-bg);color:var(--immo-danger)}.immo-alert.error .alert-icon{background:#fee4e2}
.immo-alert.warning{border-color:#fedf89;background:var(--immo-warning-bg);color:var(--immo-warning)}.immo-alert.warning .alert-icon{background:#fef0c7}
.immo-alert .alert-close{margin-left:auto;border:0;background:transparent;color:inherit;font-size:20px;cursor:pointer;padding:2px 5px}
.immo-modal[hidden]{display:none!important}.immo-modal{position:fixed;inset:0;z-index:10000;display:grid;place-items:center;padding:20px}
.immo-modal-backdrop{position:absolute;inset:0;background:rgba(16,24,40,.52);backdrop-filter:blur(3px)}
.immo-dialog{position:relative;width:min(500px,100%);background:#fff;border:1px solid #eaecf0;border-radius:20px;box-shadow:0 28px 80px rgba(16,24,40,.25);overflow:hidden}
.immo-dialog-head{display:flex;align-items:flex-start;gap:14px;padding:24px 24px 18px}
.immo-dialog-icon{width:44px;height:44px;border-radius:13px;display:grid;place-items:center;background:#fef3f2;color:var(--immo-danger);font-size:21px}
.immo-dialog h3{margin:0;font-size:19px;color:var(--immo-text)}.immo-dialog .dialog-sub{margin:5px 0 0;color:var(--immo-muted);font-size:13px;line-height:1.5}
.immo-dialog-body{padding:0 24px 22px;color:#475467;font-size:14px;line-height:1.6}
.immo-dialog-foot{display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;background:#f9fafb;border-top:1px solid #eaecf0}
.immo-dialog button,.immo-dialog .btn{border:0;border-radius:10px;padding:10px 15px;font-weight:650;cursor:pointer}
.immo-dialog .cancel{background:#fff;color:#344054;border:1px solid #d0d5dd}.immo-dialog .confirm-danger{background:#b42318;color:#fff}.immo-dialog .confirm-danger:hover{background:#912018}
body.immo-modal-open{overflow:hidden}

</style><link rel="stylesheet" href="/immobilier/assets/css/dashboard.css?v=41">
<link rel="stylesheet" href="/immobilier/assets/css/property-edit-shell.css?v=40"></head><body><div class="pr-app-shell"><?php require __DIR__ . '/_sidebar.php'; ?>
<main class="pr-main"><div class="wrap"><?php if($message):?><div class="immo-alert success"><span class="alert-icon">✓</span><div><strong><?=e(__t('common.success','Success'))?></strong><p><?=e($message)?></p></div><button class="alert-close" type="button" data-alert-close>×</button></div><?php endif;?><?php if($error):?><div class="immo-alert error"><span class="alert-icon">!</span><div><strong><?=e(__t('common.error','Something went wrong'))?></strong><p><?=e($error)?></p></div><button class="alert-close" type="button" data-alert-close>×</button></div><?php endif;?>
<div class="card"><div class="toolbar"><form class="filters"><input name="q" value="<?=e($q)?>" placeholder="<?=e(__t('properties.search','Reference, title or city'))?>"><select name="status"><option value=""><?=$t('properties.all_statuses','All statuses')?></option><?php foreach(['draft','active','sold','rented','withdrawn'] as $x):?><option value="<?=$x?>" <?=$status===$x?'selected':''?>><?=e($t('properties.status_'.$x,ucfirst($x)))?></option><?php endforeach;?></select><button><?=e(__t('common.filter','Filter'))?></button></form><a class="btn" href="/immobilier/admin/property-edit.php"><?=$t('properties.create','Create property')?></a></div></div>
<div class="card"><div class="table"><table><thead><tr><th><?=$t('properties.photo','Photo')?></th><th><?=$t('properties.reference','Reference')?></th><th><?=$t('properties.property','Property')?></th><th><?=$t('properties.property_type','Type')?></th><th><?=$t('properties.office','Office')?></th><th><?=$t('properties.agent','Agent')?></th><th><?=$t('properties.price','Price')?></th><th><?=$t('properties.status','Status')?></th><th><?=$t('properties.actions','Actions')?></th></tr></thead><tbody><?php foreach($properties as $p):?><tr><td><?php if($p['primary_photo']):?><img class="thumb" src="/immobilier/<?=e(ltrim((string)$p['primary_photo'],'/'))?>" alt=""><?php else:?>—<?php endif;?></td><td><strong><?=e($p['reference'])?></strong></td><td><?=e($p['title']?:'Untitled')?><div class="muted"><?=e($p['city']?:'')?></div></td><td><?=e($p['property_type_code']?:'—')?></td><td><?=e($p['office_name'])?></td><td><?=e($p['agent_name']?:__t('properties.unassigned','Unassigned'))?></td><td><?= $p['price']!==null?e(number_format((float)$p['price'],0,',',' ')).' €':'—'?></td><td><span class="pill"><?=e($p['status'])?></span></td><td><a class="btn light" href="/immobilier/admin/property-edit.php?id=<?=(int)$p['id']?>"><?=$t('properties.edit','Edit')?></a> <a class="btn light" href="/immobilier/admin/property-edit.php?id=<?=(int)$p['id']?>#photos"><?=$t('properties.photos','Photos')?></a> <button class="btn danger" type="button" data-confirm-modal="deleteProperty<?=(int)$p['id']?>"><?=$t('properties.delete','Delete')?></button>
<div class="immo-modal" id="deleteProperty<?=(int)$p['id']?>" hidden>
<div class="immo-modal-backdrop" data-modal-backdrop></div>
<div class="immo-dialog" role="dialog" aria-modal="true">
<div class="immo-dialog-head"><span class="immo-dialog-icon">!</span><div><h3><?=e($t('properties.delete_title','Delete property?'))?></h3><p class="dialog-sub"><?=e($p['reference'].' · '.($p['title']?:'Untitled'))?></p></div></div>
<div class="immo-dialog-body"><p><?=e($t('properties.delete_warning','This property will be removed from the active property list. This action cannot be undone from this screen.'))?></p></div>
<div class="immo-dialog-foot"><button type="button" class="cancel" data-modal-cancel><?=e($t('common.cancel','Cancel'))?></button><form method="post"><input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=(int)$p['id']?>"><button class="confirm-danger" type="submit"><?=e($t('properties.delete','Delete property'))?></button></form></div>
</div></div></td></tr><?php endforeach;?><?php if(!$properties):?><tr><td colspan="9" style="text-align:center;padding:40px"><?=$t('properties.no_results','No properties found.')?></td></tr><?php endif;?></tbody></table></div></div></div></main></div>
<script>
(function(){
  function initImmoMessages(){
    document.querySelectorAll('[data-alert-close]').forEach(function(btn){
      btn.addEventListener('click',function(){var a=btn.closest('.immo-alert');if(a)a.remove();});
    });
    document.querySelectorAll('[data-confirm-modal]').forEach(function(btn){
      btn.addEventListener('click',function(){
        var id=btn.getAttribute('data-confirm-modal'), modal=document.getElementById(id);
        if(!modal)return;
        modal.hidden=false; document.body.classList.add('immo-modal-open');
        var focus=modal.querySelector('[data-modal-cancel], [data-modal-confirm]'); if(focus)focus.focus();
      });
    });
    document.querySelectorAll('.immo-modal').forEach(function(modal){
      modal.querySelectorAll('[data-modal-cancel]').forEach(function(btn){
        btn.addEventListener('click',function(){modal.hidden=true;document.body.classList.remove('immo-modal-open');});
      });
      modal.querySelectorAll('[data-modal-backdrop]').forEach(function(btn){
        btn.addEventListener('click',function(){modal.hidden=true;document.body.classList.remove('immo-modal-open');});
      });
    });
    document.addEventListener('keydown',function(e){
      if(e.key==='Escape') document.querySelectorAll('.immo-modal:not([hidden])').forEach(function(m){m.hidden=true;document.body.classList.remove('immo-modal-open');});
    });
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',initImmoMessages);else initImmoMessages();
})();
</script>

</body></html>
