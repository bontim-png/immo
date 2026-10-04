<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php'; $auth->requireLogin();
function go2(int $id): never { header('Location: property-edit.php?id='.$id); exit; }
\App\Csrf::verify($_POST['_csrf']??''); $pid=(int)($_POST['property_id']??0); $mid=(int)($_POST['media_id']??0); $action=(string)($_POST['action']??''); if($pid<1||$mid<1) go2($pid);
$role=(string)$auth->role(); $office=(int)($auth->officeId()??0); $uid=(int)$auth->id(); $agent=null;
if($role==='agent'){ $s=$db->prepare('SELECT id,office_id FROM agents WHERE user_id=:u AND deleted_at IS NULL AND is_active=1'); $s->execute(['u'=>$uid]); $a=$s->fetch(); if(!$a) go2($pid); $agent=(int)$a['id']; $office=(int)$a['office_id']; }
$s=$db->prepare('SELECT p.office_id,p.agent_id,m.id,m.file_path FROM property_media m JOIN properties p ON p.id=m.property_id WHERE m.id=:m AND m.property_id=:p AND m.deleted_at IS NULL AND p.deleted_at IS NULL'); $s->execute(['m'=>$mid,'p'=>$pid]); $row=$s->fetch(); if(!$row) go2($pid);
if(($role==='office_admin'&&(int)$row['office_id']!==$office)||($role==='agent'&&((int)$row['office_id']!==$office||(int)$row['agent_id']!==$agent))) go2($pid);
if($action==='primary'){ $db->beginTransaction(); $s=$db->prepare('UPDATE property_media SET is_primary=0 WHERE property_id=:p AND deleted_at IS NULL'); $s->execute(['p'=>$pid]); $s=$db->prepare('UPDATE property_media SET is_primary=1,alt_text=:alt WHERE id=:m'); $s->execute(['m'=>$mid,'alt'=>trim((string)($_POST['alt_text']??''))?:null]); $db->commit(); }
elseif($action==='delete'){ $s=$db->prepare('UPDATE property_media SET deleted_at=NOW(),is_primary=0 WHERE id=:m'); $s->execute(['m'=>$mid]); $path=dirname(__DIR__).'/'.ltrim((string)$row['file_path'],'/'); if(is_file($path)) @unlink($path); $s=$db->prepare('SELECT id FROM property_media WHERE property_id=:p AND deleted_at IS NULL ORDER BY sort_order,id LIMIT 1'); $s->execute(['p'=>$pid]); $first=$s->fetchColumn(); if($first){$s=$db->prepare('UPDATE property_media SET is_primary=1 WHERE id=:m AND NOT EXISTS(SELECT 1 FROM property_media WHERE property_id=:p AND is_primary=1 AND deleted_at IS NULL)');$s->execute(['m'=>$first,'p'=>$pid]);} }
else { $s=$db->prepare('UPDATE property_media SET alt_text=:alt WHERE id=:m'); $s->execute(['m'=>$mid,'alt'=>trim((string)($_POST['alt_text']??''))?:null]); }
go2($pid);
