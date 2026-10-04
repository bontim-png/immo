<?php
declare(strict_types=1);

use App\Csrf;

$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$csrf = (string)($_POST['_csrf'] ?? '');

$errors = [];

if (!Csrf::verify($csrf)) {
    $errors[] = 'Invalid security token.';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if ($password === '') {
    $errors[] = 'Please enter your password.';
}

if (!$errors && !$auth->attempt($email, $password)) {
    $errors[] = 'Invalid email or password.';
}

if ($errors) {
    $_SESSION['_login_errors'] = $errors;
    $_SESSION['_login_email'] = $email;

    header('Location: ' . $config['app']['base_url'] . '/login');
    exit;
}

header('Location: ' . $config['app']['base_url'] . '/views/dashboard.php');
exit;
