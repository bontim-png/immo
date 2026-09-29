<?php
namespace App\Controllers;

use App\Models\User;
use App\Http\Request;

class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    public function showLogin(Request $request): void
    {
        if ($this->getUser()) {
            $this->redirect(route('dashboard'));
        }

        $this->view('auth.login');
    }

    public function login(Request $request): void
    {
        $email = $request->getBody('email');
        $password = $request->getBody('password');
        $remember = $request->getBody('remember');

        // Validate
        $errors = [];
        if (empty($email)) {
            $errors['email'][] = trans('validation.required', ['field' => trans('email')]);
        }
        if (empty($password)) {
            $errors['password'][] = trans('validation.required', ['field' => trans('password')]);
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = ['email' => $email];
            $this->redirect(route('login'));
        }

        // Find user
        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !$this->userModel->verifyPassword($user['id'], $password)) {
            $_SESSION['errors'] = ['email' => [trans('auth.invalid_credentials')]];
            $_SESSION['old_input'] = ['email' => $email];
            $this->redirect(route('login'));
        }

        // Check status
        if ($user['status'] !== 'active') {
            $_SESSION['errors'] = ['email' => [trans('auth.account_inactive')]];
            $this->redirect(route('login'));
        }

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Set user session
        unset($user['password']);
        unset($user['remember_token']);
        
        $_SESSION['user'] = $user;
        $_SESSION['logged_in'] = true;
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['ip_address'] = $this->request->getIp();

        // Record login
        $this->userModel->recordLogin($user['id']);

        // Redirect to intended URL or dashboard
        $intended = $_SESSION['url.intended'] ?? route('dashboard');
        unset($_SESSION['url.intended']);
        
        $this->redirect($intended);
    }

    public function logout(Request $request): void
    {
        // Clear session
        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destroy session
        session_destroy();

        $this->redirect(route('login'));
    }
}
