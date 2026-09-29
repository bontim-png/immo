<?php
namespace App\Controllers;

use App\Models\Office;
use App\Models\Agent;
use App\Models\Property;
use App\Http\Request;

class OfficeController extends Controller
{
    private $officeModel;
    private $agentModel;
    private $propertyModel;

    public function __construct()
    {
        parent::__construct();
        $this->officeModel = new Office();
        $this->agentModel = new Agent();
        $this->propertyModel = new Property();
    }

    public function index(Request $request): void
    {
        if (!$this->isAdmin()) {
            // Non-admin users can only see their own office
            $officeId = $this->getUserOfficeId();
            $offices = [$this->officeModel->find($officeId)];
        } else {
            $offices = $this->officeModel->all();
        }

        // Add counts
        foreach ($offices as &$office) {
            $office['agent_count'] = count($this->agentModel->getAgentsByOffice($office['id']));
            $office['property_count'] = count($this->propertyModel->getByOffice($office['id']));
        }

        $this->view('offices.index', [
            'offices' => $offices,
            'isAdmin' => $this->isAdmin(),
        ]);
    }

    public function create(Request $request): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        $this->view('offices.create');
    }

    public function store(Request $request): void
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'city' => 'required',
        ];

        $errors = $this->validate($rules);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $request->getBody();
            $this->redirect(route('offices.create'));
        }

        $data = $request->getBody();
        $data['status'] = $data['status'] ?? 'active';
        $data['country_code'] = $data['country_code'] ?? 'FR';

        $officeId = $this->officeModel->create($data);

        $_SESSION['toast'] = ['success' => trans('offices.created_success')];
        $this->redirect(route('offices.edit', ['id' => $officeId]));
    }

    public function show(Request $request): void
    {
        $id = (int)$request->getRouteParam('id');
        $office = $this->officeModel->find($id);
        
        if (!$office) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        // Check access
        if (!$this->isAdmin() && $id != $this->getUserOfficeId()) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        $office['agents'] = $this->agentModel->getAgentsByOffice($id);
        $office['properties'] = $this->propertyModel->getByOffice($id);
        $office['property_count'] = count($office['properties']);
        $office['agent_count'] = count($office['agents']);

        $this->view('offices.show', [
            'office' => $office,
        ]);
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->getRouteParam('id');
        $office = $this->officeModel->find($id);
        
        if (!$office) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        // Check access
        if (!$this->isAdmin() && $id != $this->getUserOfficeId()) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        $this->view('offices.edit', [
            'office' => $office,
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->getRouteParam('id');
        $office = $this->officeModel->find($id);
        
        if (!$office) {
            $_SESSION['toast'] = ['error' => trans('offices.not_found')];
            $this->redirect(route('offices.index'));
        }

        // Check access
        if (!$this->isAdmin() && $id != $this->getUserOfficeId()) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'city' => 'required',
        ];

        $errors = $this->validate($rules);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $request->getBody();
            $this->redirect(route('offices.edit', ['id' => $id]));
        }

        $data = $request->getBody();
        $this->officeModel->update($id, $data);

        $_SESSION['toast'] = ['success' => trans('offices.updated_success')];
        $this->redirect(route('offices.edit', ['id' => $id]));
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->getRouteParam('id');
        $office = $this->officeModel->find($id);
        
        if (!$office) {
            $_SESSION['toast'] = ['error' => trans('offices.not_found')];
            $this->redirect(route('offices.index'));
        }

        // Only admin can delete offices
        if (!$this->isAdmin()) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        // Check if office has properties or agents
        $propertyCount = count($this->propertyModel->getByOffice($id));
        $agentCount = count($this->agentModel->getAgentsByOffice($id));
        
        if ($propertyCount > 0 || $agentCount > 0) {
            $_SESSION['toast'] = ['error' => trans('offices.cannot_delete_with_data')];
            $this->redirect(route('offices.index'));
        }

        $this->officeModel->delete($id);

        $_SESSION['toast'] = ['success' => trans('offices.deleted_success')];
        $this->redirect(route('offices.index'));
    }
}
