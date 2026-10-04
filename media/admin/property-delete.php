<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
$auth->requireLogin();
\App\Csrf::verify($_POST['_csrf'] ?? '');
function back(): never { header('Location: /immobilier/admin/properties.php'); exit; }
$id=(int)($_POST['property_id']??0); if($id<1) back();
$role=(string)$auth->role(); $office=(int)($auth->officeId()??0); $uid=(int)$auth->id(); $agentId=null;
if($role==='agent'){
 $s=$db->prepare('SELECT id,office_id FROM agents WHERE user_id=:u AND deleted_at IS NULL AND is_active=1 LIMIT 1'); $s->execute(['u'=>$uid]); $a=$s->fetch(); if(!$a){http_response_code(403);exit('Access denied.');} $agentId=(int)$a['id']; $office=(int)$a['office_id'];
}
$s=$db->prepare('SELECT id,office_id,agent_id FROM properties WHERE id=:id AND deleted_at IS NULL LIMIT 1'); $s->execute(['id'=>$id]); $p=$s->fetch(); if(!$p){back();}
if($role!=='super_admin' && $role!=='office_admin' && $role!=='agent'){http_response_code(403);exit('Access denied.');}
if($role==='office_admin' && (int)$p['office_id']!==$office){http_response_code(403);exit('Access denied.');}
if($role==='agent' && ((int)$p['office_id']!==$office || (int)$p['agent_id']!==$agentId)){http_response_code(403);exit('Access denied.');}
$s=$db->prepare('UPDATE properties SET deleted_at=NOW() WHERE id=:id AND deleted_at IS NULL'); $s->execute(['id'=>$id]);
back();
