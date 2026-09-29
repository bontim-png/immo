<?php
namespace App\Controllers;

use App\Models\Agent;
use App\Models\Office;
use App\Models\Property;
use App\Http\Request;

class AgentController extends Controller
{
    private $agentModel;
    private $officeModel;
    private $propertyModel;

    public function __construct()
    {
        parent::__construct();
        $this->agentModel = new Agent();
        $this->officeModel = new Office();
        $this->propertyModel = new Property();
    }

    public function index(Request $request): void
    {
        $user = $this->getUser();
        
        // Get filter parameters
        $officeId = $request->getQuery('office_id');
        $status = $request->getQuery('status');
        $query = $request->getQuery('q');

        // Determine office filter based on user role
        if (!$this->isAdmin()) {
            $officeId = $this->getUserOfficeId();
        }

        // Get agents
        if ($officeId) {
            $agents = $this->agentModel->getAgentsByOffice($officeId);
        } else {
            $agents = $this->agentModel->all();
        }

        // Filter by status and query
        if ($status) {
            $agents = array_filter($agents, fn($a) => $a['status'] === $status);
        }
        
        if ($query) {
            $search = strtolower($query);
            $agents = array_filter($agents, function($a) use ($search) {
                return strpos(strtolower($a['first_name'] . ' ' . $a['last_name']), $search) !== false ||
                       strpos(strtolower($a['email'] ?? ''), $search) !== false;
            });
        }

        // Add office info
        foreach ($agents as &$agent) {
            $agent['office'] = $this->officeModel->find($agent['office_id']);
            $agent['property_count'] = count($this->propertyModel->getByAgent($agent['id']));
        }

        // Get offices for filter
        $offices = [];
        if ($this->isAdmin()) {
            $offices = $this->officeModel->getActiveOffices();
        }

        $this->view('agents.index', [
            'agents' => $agents,
            'offices' => $offices,
            'status' => $status,
            'query' => $query,
            'currentOfficeId' => $officeId,
            'user' => $user,
        ]);
    }

    public function create(Request $request): void
    {
        $user = $this->getUser();
        
        // Get offices
        if ($this->isAdmin()) {
            $offices = $this->officeModel->getActiveOffices();
        } else {
            $officeId = $this->getUserOfficeId();
            $offices = [$this->officeModel->find($officeId)];
        }

        $this->view('agents.create', [
            'offices' => $offices,
            'user' => $user,
        ]);
    }

    public function store(Request $request): void
    {
        $rules = [
            'office_id' => 'required|exists:App\Models\Office',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];

        $errors = $this->validate($rules);

        // Custom validation: user can only assign to their own office
        if (!$this->isAdmin()) {
            $officeId = $request->getBody('office_id');
            if ($officeId != $this->getUserOfficeId()) {
                $errors['office_id'][] = trans('validation.office_access_denied');
            }
        }

        // Custom validation: email must be unique
        $email = $request->getBody('email');
        if ($email && $this->agentModel->findByEmail($email)) {
            $errors['email'][] = trans('validation.unique', ['field' => trans('email')]);
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $request->getBody();
            $this->redirect(route('agents.create'));
        }

        $data = $request->getBody();
        $data['status'] = $data['status'] ?? 'active';
        $data['role'] = $data['role'] ?? 'agent';

        // Only admin can assign to different offices or set admin role
        if (!$this->isAdmin()) {
            $data['office_id'] = $this->getUserOfficeId();
            $data['role'] = 'agent';
        }

        $agentId = $this->agentModel->create($data);

        $_SESSION['toast'] = ['success' => trans('agents.created_success')];
        $this->redirect(route('agents.edit', ['id' => $agentId]));
    }

    public function show(Request $request): void
    {
        $id = (int)$request->getRouteParam('id');
        $agent = $this->agentModel->find($id);
        
        if (!$agent) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        // Check access
        if (!$this->canManageAgent($id)) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        $agent['office'] = $this->officeModel->find($agent['office_id']);
        $agent['properties'] = $this->propertyModel->getByAgent($agent['id']);
        $agent['property_count'] = count($agent['properties']);

        $this->view('agents.show', [
            'agent' => $agent,
        ]);
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

        // Check access
        if (!$this->canManageAgent($id)) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        // Get offices
        if ($this->isAdmin()) {
            $offices = $this->officeModel->getActiveOffices();
        } else {
            $officeId = $this->getUserOfficeId();
            $offices = [$this->officeModel->find($officeId)];
        }

        $this->view('agents.edit', [
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
            $this->redirect(route('agents.index'));
        }

        // Check access
        if (!$this->canManageAgent($id)) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        $rules = [
            'office_id' => 'required|exists:App\Models\Office',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
        ];

        // Only admin can change office and role
        if (!$this->isAdmin()) {
            unset($rules['office_id']);
        }

        $errors = $this->validate($rules);

        // Custom validation: user can only assign to their own office
        if (!$this->isAdmin()) {
            $officeId = $request->getBody('office_id');
            if ($officeId && $officeId != $this->getUserOfficeId()) {
                $errors['office_id'][] = trans('validation.office_access_denied');
            }
        }

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
            $this->redirect(route('agents.edit', ['id' => $id]));
        }

        $data = $request->getBody();
        
        // Only admin can change office and role
        if (!$this->isAdmin()) {
            unset($data['office_id']);
            unset($data['role']);
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
        $this->redirect(route('agents.edit', ['id' => $id]));
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->getRouteParam('id');
        $agent = $this->agentModel->find($id);
        
        if (!$agent) {
            $_SESSION['toast'] = ['error' => trans('agents.not_found')];
            $this->redirect(route('agents.index'));
        }

        // Check access
        if (!$this->canManageAgent($id)) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        // Cannot delete self
        if ($id === $this->getUserId()) {
            $_SESSION['toast'] = ['error' => trans('users.cannot_delete_self')];
            $this->redirect(route('agents.index'));
        }

        // Check if agent has properties
        $propertyCount = count($this->propertyModel->getByAgent($id));
        if ($propertyCount > 0) {
            $_SESSION['toast'] = ['error' => trans('agents.cannot_delete_with_properties')];
            $this->redirect(route('agents.index'));
        }

        $this->agentModel->delete($id);

        $_SESSION['toast'] = ['success' => trans('agents.deleted_success')];
        $this->redirect(route('agents.index'));
    }
}
