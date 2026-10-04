<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
$auth->requireLogin();
header('Content-Type: application/json; charset=utf-8');
function out(bool $success, string $message=''): never { echo json_encode(['success'=>$success,'message'=>$message]); exit; }
try {
    \App\Csrf::verify($_POST['_csrf'] ?? '');
    $propertyId=(int)($_POST['property_id']??0);
    if($propertyId<1) out(false,'Invalid property.');
    $role=(string)$auth->role();
    $where='id=:id AND deleted_at IS NULL'; $params=['id'=>$propertyId];
    if($role==='office_admin'){ $where.=' AND office_id=:office_id'; $params['office_id']=(int)$auth->officeId(); }
    elseif($role==='agent'){
        $s=$db->prepare('SELECT id FROM agents WHERE user_id=:u AND deleted_at IS NULL AND is_active=1 LIMIT 1');$s->execute(['u'=>(int)$auth->id()]);$agentId=(int)$s->fetchColumn();
        if(!$agentId) out(false,'Agent profile not found.');
        $where.=' AND agent_id=:agent_id';$params['agent_id']=$agentId;
    }
    $s=$db->prepare("SELECT id FROM properties WHERE $where LIMIT 1");$s->execute($params);if(!$s->fetchColumn()) out(false,'Property not found or access denied.');
    $mediaType=(string)($_POST['media_type']??'photo');if(!in_array($mediaType,['photo','video'],true)) out(false,'Invalid media type.');$order=array_values(array_unique(array_map('intval',$_POST['order']??[])));if(!$order) out(false,'No media order supplied.');
    $in=implode(',',array_fill(0,count($order),'?'));
    $s=$db->prepare("SELECT id FROM property_media WHERE property_id=? AND media_type=? AND deleted_at IS NULL AND id IN ($in)");$s->execute(array_merge([$propertyId,$mediaType],$order));$valid=array_map('intval',$s->fetchAll(PDO::FETCH_COLUMN));
    if(count($valid)!==count($order) || array_diff($order,$valid)) out(false,'Invalid photo order.');
    $db->beginTransaction();$u=$db->prepare('UPDATE property_media SET sort_order=:sort WHERE id=:id AND property_id=:property_id');
    foreach($order as $i=>$mediaId)$u->execute(['sort'=>$i,'id'=>$mediaId,'property_id'=>$propertyId]);
    $db->commit();out(true,'Order saved.');
}catch(Throwable $e){if($db->inTransaction())$db->rollBack();out(false,$e->getMessage());}
