<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
$auth->requireLogin();
$id=(int)($_GET['id']??0);if($id<1){http_response_code(404);exit('Not found');}
$role=(string)$auth->role();$where='d.id=:id AND d.deleted_at IS NULL AND p.deleted_at IS NULL';$params=['id'=>$id];
if($role==='office_admin'){$where.=' AND p.office_id=:o';$params['o']=(int)$auth->officeId();}
elseif($role==='agent'){$s=$db->prepare('SELECT id FROM agents WHERE user_id=:u AND deleted_at IS NULL AND is_active=1 LIMIT 1');$s->execute(['u'=>(int)$auth->id()]);$a=(int)$s->fetchColumn();if(!$a){http_response_code(403);exit('Access denied');}$where.=' AND p.agent_id=:a';$params['a']=$a;}
$s=$db->prepare("SELECT d.* FROM property_documents d JOIN properties p ON p.id=d.property_id WHERE $where LIMIT 1");$s->execute($params);$d=$s->fetch();if(!$d){http_response_code(404);exit('Document not found');}
$path=dirname(__DIR__).'/'.ltrim((string)$d['file_path'],'/');if(!is_file($path)){http_response_code(404);exit('File not found');}
$filename=(string)$d['original_filename'];$safe=preg_replace('/[^A-Za-z0-9._ -]+/','_',basename($filename))?:'document';
header('Content-Type: '.((string)$d['mime_type']?:'application/octet-stream'));header('Content-Length: '.filesize($path));header('Content-Disposition: attachment; filename="'.str_replace('"','',$safe).'"');header('X-Content-Type-Options: nosniff');readfile($path);
