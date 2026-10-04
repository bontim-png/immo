<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';
use App\Csrf;
$errors = $_SESSION['_login_errors'] ?? [];
$email = $_SESSION['_login_email'] ?? '';
$success = $_SESSION['_login_success'] ?? '';
unset($_SESSION['_login_errors'], $_SESSION['_login_email'], $_SESSION['_login_success']);
?>
<!doctype html>
<html lang="<?=e($i18n->getLanguage())?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e(__t('auth.login_title','Login'))?> · Immo</title>
<style>
:root{--blue:#315cf6;--blue2:#eef4ff;--ink:#172033;--muted:#667085;--border:#e3e8f0;--danger:#b42318;--dangerbg:#fef3f2;--success:#027a48;--successbg:#ecfdf3}
*{box-sizing:border-box}body{margin:0;min-height:100vh;background:#f7f9fc;color:var(--ink);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;display:grid;place-items:center;padding:28px}
.login-shell{width:min(440px,100%)}.brand{text-align:center;margin-bottom:24px}.brand-mark{width:52px;height:52px;margin:0 auto 14px;border-radius:16px;background:var(--blue2);display:grid;place-items:center;color:var(--blue);font-weight:800;font-size:24px}.brand h1{font-size:26px;margin:0 0 6px;letter-spacing:-.03em}.brand p{margin:0;color:var(--muted);font-size:14px}
.card{background:#fff;border:1px solid var(--border);border-radius:24px;padding:30px;box-shadow:0 12px 40px rgba(16,24,40,.07)}.card h2{margin:0;font-size:22px;letter-spacing:-.02em}.card .intro{margin:7px 0 22px;color:var(--muted);font-size:14px}
.alert{border-radius:13px;padding:13px 14px;margin:0 0 18px;font-size:13px;line-height:1.45}.alert.error{background:var(--dangerbg);border:1px solid #fecdca;color:var(--danger)}.alert.success{background:var(--successbg);border:1px solid #abefc6;color:var(--success)}
.field{margin-bottom:17px}.field label{display:block;font-size:13px;font-weight:700;margin-bottom:7px}.field input{width:100%;height:48px;border:1px solid #d7dde7;border-radius:12px;padding:0 14px;font:inherit;font-size:15px;outline:none;transition:.15s;background:#fff}.field input:focus{border-color:#8aa8ff;box-shadow:0 0 0 4px #eef4ff}.password-row{position:relative}.password-row input{padding-right:78px}.show-password{position:absolute;right:7px;top:7px;height:34px;padding:0 10px;border:0;border-radius:8px;background:#f2f4f7;color:#475467;cursor:pointer;font-weight:600;font-size:12px}
.actions{margin-top:22px}.btn{width:100%;height:48px;border:0;border-radius:12px;background:var(--blue);color:#fff;font:inherit;font-weight:700;cursor:pointer;box-shadow:0 5px 14px rgba(49,92,246,.18)}.btn:hover{filter:brightness(.97)}.forgot{text-align:center;margin-top:17px}.forgot a{color:var(--blue);font-size:13px;font-weight:700;text-decoration:none}.forgot a:hover{text-decoration:underline}.footer{text-align:center;color:#98a2b3;font-size:12px;margin-top:18px}
@media(max-width:480px){body{padding:18px}.card{padding:23px;border-radius:20px}}
</style></head>
<body><main class="login-shell">
<div class="brand"><div class="brand-mark">I</div><h1>Immo</h1><p><?=e(__t('auth.platform_desc','Real estate management platform'))?></p></div>
<section class="card"><h2><?=e(__t('auth.welcome','Welcome back'))?></h2><p class="intro"><?=e(__t('auth.signin_intro','Sign in to continue to your workspace.'))?></p>
<?php if($errors): ?><div class="alert error" role="alert"><?php foreach($errors as $error): ?><div><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endforeach; ?></div><?php endif; ?>
<?php if($success): ?><div class="alert success" role="status"><?=htmlspecialchars($success,ENT_QUOTES,'UTF-8')?></div><?php endif; ?>
<form method="post" action="/immobilier/login" novalidate>
<input type="hidden" name="_csrf" value="<?=htmlspecialchars(Csrf::token(),ENT_QUOTES,'UTF-8')?>">
<div class="field"><label for="email"><?=e(__t('common.email','Email'))?></label><input id="email" type="email" name="email" value="<?=htmlspecialchars((string)$email,ENT_QUOTES,'UTF-8')?>" autocomplete="username" required autofocus></div>
<div class="field"><label for="password"><?=e(__t('auth.password','Password'))?></label><div class="password-row"><input id="password" type="password" name="password" autocomplete="current-password" required><button class="show-password" type="button" id="showPassword"><?=e(__t('auth.show','Show'))?></button></div></div>
<div class="actions"><button class="btn" type="submit"><?=e(__t('auth.sign_in','Sign in'))?></button></div>
</form><div class="forgot"><a href="/immobilier/forgot-password"><?=e(__t('auth.forgot','Forgot your password?'))?></a></div></section>
<div class="footer"><?=e(__t('auth.secure_access','Secure access · Immo'))?></div></main>
<script>document.getElementById('showPassword').addEventListener('click',function(){const p=document.getElementById('password');const show=p.type==='password';p.type=show?'text':'password';this.textContent=show?<?=json_encode(__t('auth.hide','Hide'))?>:<?=json_encode(__t('auth.show','Show'))?>;});</script>
</body></html>