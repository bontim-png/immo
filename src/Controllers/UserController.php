<?php
namespace App\Controllers;

use App\Models\Agent;
use App\Models\Office;
use App\Http\Request;

class UserController extends Controller
{
    private $agentModel;
    private $officeModel;

    public function __construct()
    {
        parent::__construct();
        $this->agentModel = new Agent();
        $this->officeModel = new Office();
    }

    public function index(Request $request): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        $agents = $this->agentModel->all();
        
        // Add office info
        foreach ($agents as &$agent) {
            $agent['office'] = $this->officeModel->find($agent['office_id']);
        }

        $this->view('agents.index', [
            'agents' => $agents,
            'isAdmin' => true,
        ]);
    }

    public function create(Request $request): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        $offices = $this->officeModel->getActiveOffices();

        $this->view('agents.create', [
            'offices' => $offices,
            'isAdmin' => true,
        ]);
    }

    public function store(Request $request): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            $this->view('errors/403');
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
        if ($email && $this->agentModel->findByEmail($email)) {
            $errors['email'][] = trans('validation.unique', ['field' => trans('email')]);
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $request->getBody();
            $this->redirect(route('users.create'));
        }

        $data = $request->getBody();
        $data['status'] = $data['status'] ?? 'active';

        $agentId = $this->agentModel->create($data);

        $_SESSION['toast'] = ['success' => trans('agents.created_success')];
        $this->redirect(route('users.edit', ['id' => $agentId]));
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->getRouteParam('id');
        $agent = $this->agentModel->find($id);
        
        if (!$agent) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        // Check access - only admin can edit other agents
        if (!$this->isAdmin() && $id !== $this->getUserId()) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        $offices = $this->officeModel->getActiveOffices();
        $agent['office'] = $this->officeModel->find($agent['office_id']);

        $this->view('users.edit', [
            'agent' => $agent,
            'offices' => $offices,
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->getRouteParam('id');
        $agent = $this->agentModel->find($id);
        
        if (!$agent) {
            $_SESSION['toast'] = ['error' => trans('agents.not_found')];
            $this->redirect(route('users.index'));
        }

        // Check access
        if (!$this->isAdmin() && $id !== $this->getUserId()) {
            http_response_code(403);
            $this->view('errors/403');
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

        // Custom validation: email must be unique (excluding current agent)
        $email = $request->getBody('email');
        if ($email && $email !== $agent['email']) {
            $existing = $this->agentModel->findByEmail($email);
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

        $this->agentModel->update($id, $data);

        // Update password separately if provided
        if (!empty($password)) {
            $this->agentModel->updatePassword($id, $password);
        }

        $_SESSION['toast'] = ['success' => trans('agents.updated_success')];
        $this->redirect(route('users.edit', ['id' => $id]));
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->getRouteParam('id');
        $agent = $this->agentModel->find($id);
        
        if (!$agent) {
            $_SESSION['toast'] = ['error' => trans('agents.not_found')];
            $this->redirect(route('users.index'));
        }

        // Only admin can delete agents
        if (!$this->isAdmin()) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        // Cannot delete self
        if ($id === $this->getUserId()) {
            $_SESSION['toast'] = ['error' => trans('users.cannot_delete_self')];
            $this->redirect(route('users.index'));
        }

        // Check if agent has properties
        $propertyModel = new \App\Models\Property();
        $propertyCount = count($propertyModel->getByAgent($id));
        if ($propertyCount > 0) {
            $_SESSION['toast'] = ['error' => trans('agents.cannot_delete_with_properties')];
            $this->redirect(route('users.index'));
        }

        $this->agentModel->delete($id);

        $_SESSION['toast'] = ['success' => trans('agents.deleted_success')];
        $this->redirect(route('users.index'));
    }
}
