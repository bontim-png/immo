<?php
declare(strict_types=1);
use App\Csrf;
$email=trim((string)($_POST['email']??''));
if(!Csrf::verify((string)($_POST['_csrf']??''))){$_SESSION['_forgot_errors']=['Invalid security token.'];header('Location: '.$config['app']['base_url'].'/forgot-password');exit;}
if($email===''||!filter_var($email,FILTER_VALIDATE_EMAIL)){$_SESSION['_forgot_errors']=['Please enter a valid email address.'];$_SESSION['_forgot_email']=$email;header('Location: '.$config['app']['base_url'].'/forgot-password');exit;}
// Always show the same result to avoid revealing whether an account exists.
$stmt=$db->prepare('SELECT id,first_name,email FROM users WHERE LOWER(email)=LOWER(:email) AND is_active=1 AND deleted_at IS NULL LIMIT 1');$stmt->execute(['email'=>$email]);$user=$stmt->fetch();
if($user){
  $raw=bin2hex(random_bytes(32));$hash=hash('sha256',$raw);
  $db->prepare('DELETE FROM password_reset_tokens WHERE user_id=:uid OR expires_at<NOW()')->execute(['uid'=>(int)$user['id']]);
  $db->prepare('INSERT INTO password_reset_tokens (user_id,token_hash,expires_at,created_at) VALUES (:uid,:hash,DATE_ADD(NOW(),INTERVAL 60 MINUTE),NOW())')->execute(['uid'=>(int)$user['id'],'hash'=>$hash]);
  $base=rtrim((string)($config['app']['base_url']??'/immobilier'),'/');$link=$base.'/reset-password?token='.urlencode($raw);
  $host=$_SERVER['HTTP_HOST']??'localhost';$from=(string)($config['mail']['from_email']??('no-reply@'.preg_replace('/:\\d+$/','',$host)));$fromName=(string)($config['mail']['from_name']??'Immo');
  $subject='Reset your Immo password';$name=trim((string)$user['first_name'])?:'there';
  $html='<div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;color:#172033"><h2>Reset your Immo password</h2><p>Hello '.htmlspecialchars($name,ENT_QUOTES,'UTF-8').',</p><p>We received a request to reset your Immo password. This link is valid for 60 minutes.</p><p><a href="'.htmlspecialchars($link,ENT_QUOTES,'UTF-8').'" style="display:inline-block;padding:12px 18px;background:#315cf6;color:#fff;text-decoration:none;border-radius:8px">Reset password</a></p><p>If you did not request this, you can safely ignore this email.</p></div>';
  $headers="MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: ".sprintf('%s <%s>',$fromName,$from)."\r\n";
  @mail($user['email'],$subject,$html,$headers);
}
$_SESSION['_forgot_success']='If an account exists for that email address, a password reset link has been sent.';header('Location: '.$config['app']['base_url'].'/forgot-password');exit;