<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Office;
use App\Http\Request;

class UserController extends Controller
{
    private $userModel;
    private $officeModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->officeModel = new Office();
    }

    public function index(Request $request): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            $this->view('errors.403');
            return;
        }

        $users = $this->userModel->all();
        
        // Add office info
        foreach ($users as &$user) {
            $user['office'] = $this->officeModel->find($user['office_id']);
        }

        $this->view('users.index', [
            'users' => $users,
        ]);
    }

    public function create(Request $request): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            $this->view('errors.403');
            return;
        }

        $offices = $this->officeModel->getActiveOffices();

        $this->view('users.create', [
            'offices' => $offices,
        ]);
    }

    public function store(Request $request): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            $this->view('errors.403');
            return;
        }

        $rules = [
            'office_id' => 'required|exists:App\Models\Office',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'role' => 'required',
        ];

        $errors = $this->validate($rules);

        // Custom validation: email must be unique
        $email = $request->getBody('email');
        if ($email && $this->userModel->findByEmail($email)) {
            $errors['email'][] = trans('validation.unique', ['field' => trans('email')]);
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $request->getBody();
            $this->redirect(route('users.create'));
        }

        $data = $request->getBody();
        $data['status'] = $data['status'] ?? 'active';

        $userId = $this->userModel->create($data);

        $_SESSION['toast'] = ['success' => trans('users.created_success')];
        $this->redirect(route('users.edit', ['id' => $userId]));
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->getRouteParam('id');
        $user = $this->userModel->find($id);
        
        if (!$user) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        // Check access - only admin can edit other users
        if (!$this->isAdmin() && $id !== $this->getUserId()) {
            http_response_code(403);
            $this->view('errors.403');
            return;
        }

        $offices = $this->officeModel->getActiveOffices();
        $user['office'] = $this->officeModel->find($user['office_id']);

        $this->view('users.edit', [
            'user' => $user,
            'offices' => $offices,
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->getRouteParam('id');
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $_SESSION['toast'] = ['error' => trans('users.not_found')];
            $this->redirect(route('users.index'));
        }

        // Check access
        if (!$this->isAdmin() && $id !== $this->getUserId()) {
            http_response_code(403);
            $this->view('errors.403');
            return;
        }

        $rules = [
            'office_id' => 'required|exists:App\Models\Office',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'role' => 'required',
        ];

        // Don't allow non-admin to change role or office
        if (!$this->isAdmin()) {
            unset($rules['role']);
            unset($rules['office_id']);
        }

        $errors = $this->validate($rules);

        // Custom validation: email must be unique (excluding current user)
        $email = $request->getBody('email');
        if ($email && $email !== $user['email']) {
            $existing = $this->userModel->findByEmail($email);
            if ($existing && $existing['id'] != $id) {
                $errors['email'][] = trans('validation.unique', ['field' => trans('email')]);
            }
        }

        // Handle password change separately
        $password = $request->getBody('password');
        if (!empty($password)) {
            if (strlen($password) < 8) {
                $errors['password'][] = trans('validation.min', ['field' => trans('password'), 'min' => 8]);
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $request->getBody();
            $this->redirect(route('users.edit', ['id' => $id]));
        }

        $data = $request->getBody();
        
        // Only admin can change role and office
        if (!$this->isAdmin()) {
            unset($data['role']);
            unset($data['office_id']);
        }
        
        // Remove password from data if not changing
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $this->userModel->update($id, $data);

        // Update password separately if provided
        if (!empty($password)) {
            $this->userModel->updatePassword($id, $password);
        }

        $_SESSION['toast'] = ['success' => trans('users.updated_success')];
        $this->redirect(route('users.edit', ['id' => $id]));
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->getRouteParam('id');
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $_SESSION['toast'] = ['error' => trans('users.not_found')];
            $this->redirect(route('users.index'));
        }

        // Only admin can delete users
        if (!$this->isAdmin()) {
            http_response_code(403);
            $this->view('errors.403');
            return;
        }

        // Cannot delete self
        if ($id === $this->getUserId()) {
            $_SESSION['toast'] = ['error' => trans('users.cannot_delete_self')];
            $this->redirect(route('users.index'));
        }

        $this->userModel->delete($id);

        $_SESSION['toast'] = ['success' => trans('users.deleted_success')];
        $this->redirect(route('users.index'));
    }
}
