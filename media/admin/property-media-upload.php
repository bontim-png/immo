<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
$auth->requireLogin();
function uuidv4(): string { $d=random_bytes(16); $d[6]=chr((ord($d[6])&15)|64); $d[8]=chr((ord($d[8])&63)|128); return vsprintf('%s%s-%s-%s-%s-%s%s%s',str_split(bin2hex($d),4)); }
function go(int $id): never { header('Location: property-edit.php?id='.$id); exit; }
\App\Csrf::verify($_POST['_csrf'] ?? ''); $pid=(int)($_POST['property_id']??0); if($pid<1) go(0);
$role=(string)$auth->role(); $office=(int)($auth->officeId()??0); $uid=(int)$auth->id(); $agentId=null;
if($role==='agent'){ $s=$db->prepare('SELECT id,office_id FROM agents WHERE user_id=:u AND deleted_at IS NULL AND is_active=1 LIMIT 1'); $s->execute(['u'=>$uid]); $a=$s->fetch(); if(!$a) go($pid); $agentId=(int)$a['id']; $office=(int)$a['office_id']; }
$s=$db->prepare('SELECT id,office_id,agent_id FROM properties WHERE id=:id AND deleted_at IS NULL LIMIT 1'); $s->execute(['id'=>$pid]); $p=$s->fetch(); if(!$p) go($pid);
if(($role==='office_admin'&&(int)$p['office_id']!==$office)||($role==='agent'&&((int)$p['office_id']!==$office||(int)$p['agent_id']!==$agentId))) go($pid);
$f=$_FILES['photos']??null; if(!$f||!is_array($f['name']??null)) go($pid);
$base=dirname(__DIR__).'/storage/properties/'.$pid; if(!is_dir($base)) {
    if (!mkdir($base, 0755, true) && !is_dir($base)) {
        go($pid);
    }
}
// Keep upload directories web-readable on cPanel/shared hosting.
@chmod($base, 0755);
$s=$db->prepare('SELECT COALESCE(MAX(sort_order),-1) FROM property_media WHERE property_id=:p AND deleted_at IS NULL'); $s->execute(['p'=>$pid]); $sort=(int)$s->fetchColumn()+1;
$fi=new finfo(FILEINFO_MIME_TYPE); $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
for($i=0;$i<count($f['name']);$i++){
 if(($f['error'][$i]??4)!==UPLOAD_ERR_OK || (int)$f['size'][$i]>12*1024*1024) continue;
 $tmp=$f['tmp_name'][$i]; $mime=$fi->file($tmp); if(!isset($allowed[$mime])) continue; $info=@getimagesize($tmp); if(!$info) continue;
 $name=bin2hex(random_bytes(12)).'.'.$allowed[$mime]; $relative='storage/properties/'.$pid.'/'.$name; if(!move_uploaded_file($tmp,$base.'/'.$name)) continue;
// Explicit permissions: cPanel-friendly directory/file modes.
@chmod($base.'/'.$name, 0644);
 $s=$db->prepare('INSERT INTO property_media (public_id,property_id,media_type,original_filename,file_path,mime_type,file_size,width,height,alt_text,sort_order,is_primary) VALUES (:public_id,:property_id,"photo",:original,:path,:mime,:size,:width,:height,:alt,:sort,:primary)');
 $s->execute(['public_id'=>uuidv4(),'property_id'=>$pid,'original'=>basename((string)$f['name'][$i]),'path'=>$relative,'mime'=>$mime,'size'=>(int)$f['size'][$i],'width'=>(int)$info[0],'height'=>(int)$info[1],'alt'=>pathinfo((string)$f['name'][$i],PATHINFO_FILENAME),'sort'=>$sort,'primary'=>$sort===0?1:0]); $sort++;
}
go($pid);
