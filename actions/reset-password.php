<?php
declare(strict_types=1);
use App\Csrf;
$token=(string)($_POST['token']??'');$password=(string)($_POST['password']??'');$confirmation=(string)($_POST['password_confirmation']??'');
if(!Csrf::verify((string)($_POST['_csrf']??''))){$_SESSION['_reset_errors']=['Invalid security token.'];header('Location: '.$config['app']['base_url'].'/reset-password?token='.urlencode($token));exit;}
$errors=[];if(!preg_match('/^[a-f0-9]{64}$/',$token))$errors[]='This reset link is invalid or has expired.';if(strlen($password)<8)$errors[]='Password must contain at least 8 characters.';if($password!==$confirmation)$errors[]='The passwords do not match.';
if(!$errors){$hash=hash('sha256',$token);$stmt=$db->prepare('SELECT id,user_id FROM password_reset_tokens WHERE token_hash=:hash AND expires_at>NOW() LIMIT 1');$stmt->execute(['hash'=>$hash]);$reset=$stmt->fetch();if(!$reset)$errors[]='This reset link is invalid or has expired.';}
if($errors){$_SESSION['_reset_errors']=$errors;header('Location: '.$config['app']['base_url'].'/reset-password?token='.urlencode($token));exit;}
$db->beginTransaction();try{$db->prepare('UPDATE users SET password_hash=:ph,updated_at=NOW() WHERE id=:id')->execute(['ph'=>password_hash($password,PASSWORD_DEFAULT),'id'=>(int)$reset['user_id']]);$db->prepare('DELETE FROM password_reset_tokens WHERE user_id=:id')->execute(['id'=>(int)$reset['user_id']]);$db->commit();}catch(Throwable $e){$db->rollBack();$_SESSION['_reset_errors']=['We could not reset your password. Please try again.'];header('Location: '.$config['app']['base_url'].'/reset-password?token='.urlencode($token));exit;}
$_SESSION['_login_success']='Your password has been changed. You can now sign in.';header('Location: '.$config['app']['base_url'].'/login');exit;