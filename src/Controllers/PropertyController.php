<?php
namespace App\Controllers;

use App\Models\Property;
use App\Models\Office;
use App\Models\Agent;
use App\Models\PropertyPhoto;
use App\Http\Request;

class PropertyController extends Controller
{
    private $propertyModel;
    private $officeModel;
    private $agentModel;
    private $photoModel;

    public function __construct()
    {
        parent::__construct();
        $this->propertyModel = new Property();
        $this->officeModel = new Office();
        $this->agentModel = new Agent();
        $this->photoModel = new PropertyPhoto();
    }

    public function index(Request $request): void
    {
        $user = $this->getUser();
        
        // Get filter parameters
        $officeId = $request->getQuery('office_id');
        $status = $request->getQuery('status');
        $query = $request->getQuery('q');
        $page = (int)($request->getQuery('page') ?? 1);
        $perPage = 20;

        // Determine office filter based on user role
        if (!$this->isAdmin()) {
            $officeId = $this->getUserOfficeId();
        }

        // Get properties
        $properties = $this->propertyModel->search($officeId, $query, $status);
        
        // Get offices for filter
        $offices = [];
        if ($this->isAdmin()) {
            $offices = $this->officeModel->getActiveOffices();
        }

        // Get agents for each property
        foreach ($properties as &$property) {
            $property['agent'] = $this->agentModel->find($property['agent_id']);
            $property['office'] = $this->officeModel->find($property['office_id']);
            $property['hero_photo'] = $this->photoModel->getHeroByProperty($property['id']);
        }

        $this->view('properties.index', [
            'properties' => $properties,
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

        // Get agents for each office
        $agentsByOffice = [];
        foreach ($offices as $office) {
            $agentsByOffice[$office['id']] = $this->agentModel->getAgentsByOffice($office['id']);
        }

        $this->view('properties.create', [
            'offices' => $offices,
            'agentsByOffice' => $agentsByOffice,
            'user' => $user,
        ]);
    }

    public function store(Request $request): void
    {
        $user = $this->getUser();
        
        // Validate
        $rules = [
            'office_id' => 'required|exists:App\Models\Office',
            'agent_id' => 'required|exists:App\Models\Agent',
            'reference_code' => 'required',
            'title' => 'required',
            'property_type' => 'required',
            'transaction_type' => 'required',
        ];

        $errors = $this->validate($rules);
        
        // Custom validation: agent must belong to selected office
        $officeId = $request->getBody('office_id');
        $agentId = $request->getBody('agent_id');
        
        if (!$this->agentModel->validateOffice($agentId, $officeId)) {
            $errors['agent_id'][] = trans('validation.agent_office_mismatch');
        }

        // Custom validation: user can only assign to their own office
        if (!$this->isAdmin() && $officeId != $this->getUserOfficeId()) {
            $errors['office_id'][] = trans('validation.office_access_denied');
        }

        // Custom validation: reference must be unique
        $reference = $request->getBody('reference_code');
        if ($reference && $this->propertyModel->firstWhere('reference_code', $reference)) {
            $errors['reference_code'][] = trans('validation.unique', ['field' => trans('reference')]);
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $request->getBody();
            $this->redirect(route('properties.create'));
        }

        // Prepare data
        $data = $request->getBody();
        
        // Only admin can set certain fields
        if (!$this->isAdmin()) {
            $data['office_id'] = $this->getUserOfficeId();
        }

        // Set default values
        $data['listing_status'] = $data['listing_status'] ?? 'draft';
        $data['currency'] = $data['currency'] ?? 'EUR';
        $data['country_code'] = $data['country_code'] ?? 'FR';

        // Create property
        $propertyId = $this->propertyModel->create($data);

        // Handle photos if uploaded
        if ($request->getFiles('photos')) {
            $this->handlePhotoUpload($propertyId, $request->getFiles('photos'));
        }

        $_SESSION['toast'] = ['success' => trans('properties.created_success')];
        $this->redirect(route('properties.edit', ['id' => $propertyId]));
    }

    public function show(Request $request): void
    {
        $id = $request->getRouteParam('id');
        $property = $this->propertyModel->find($id);
        
        if (!$property) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        // Check access
        if (!$this->canAccessOffice($property['office_id'])) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        $property['agent'] = $this->agentModel->find($property['agent_id']);
        $property['office'] = $this->officeModel->find($property['office_id']);
        $property['photos'] = $this->photoModel->getOrderedByProperty($property['id']);
        $property['hero_photo'] = $this->photoModel->getHeroByProperty($property['id']);

        $this->view('properties.show', [
            'property' => $property,
        ]);
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->getRouteParam('id');
        $property = $this->propertyModel->find($id);
        
        if (!$property) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        // Check access
        if (!$this->canAccessOffice($property['office_id'])) {
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

        // Get agents for property's office
        $agentsByOffice = [];
        foreach ($offices as $office) {
            $agentsByOffice[$office['id']] = $this->agentModel->getAgentsByOffice($office['id']);
        }

        $property['photos'] = $this->photoModel->getOrderedByProperty($property['id']);
        $property['hero_photo'] = $this->photoModel->getHeroByProperty($property['id']);

        $this->view('properties.edit', [
            'property' => $property,
            'offices' => $offices,
            'agentsByOffice' => $agentsByOffice,
        ]);
    }

    public function update(Request $request): void
    {
        $id = $request->getRouteParam('id');
        $property = $this->propertyModel->find($id);
        
        if (!$property) {
            $_SESSION['toast'] = ['error' => trans('properties.not_found')];
            $this->redirect(route('properties.index'));
        }

        // Check access
        if (!$this->canAccessOffice($property['office_id'])) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        // Validate
        $rules = [
            'office_id' => 'required|exists:App\Models\Office',
            'agent_id' => 'required|exists:App\Models\Agent',
            'reference_code' => 'required',
            'title' => 'required',
            'property_type' => 'required',
            'transaction_type' => 'required',
        ];

        $errors = $this->validate($rules);
        
        // Custom validation: agent must belong to selected office
        $officeId = $request->getBody('office_id');
        $agentId = $request->getBody('agent_id');
        
        if (!$this->agentModel->validateOffice($agentId, $officeId)) {
            $errors['agent_id'][] = trans('validation.agent_office_mismatch');
        }

        // Custom validation: user can only assign to their own office
        if (!$this->isAdmin() && $officeId != $this->getUserOfficeId()) {
            $errors['office_id'][] = trans('validation.office_access_denied');
        }

        // Custom validation: reference must be unique (excluding current property)
        $reference = $request->getBody('reference_code');
        if ($reference && $reference !== $property['reference_code']) {
            $existing = $this->propertyModel->firstWhere('reference_code', $reference);
            if ($existing && $existing['id'] != $id) {
                $errors['reference_code'][] = trans('validation.unique', ['field' => trans('reference')]);
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $request->getBody();
            $this->redirect(route('properties.edit', ['id' => $id]));
        }

        // Prepare data
        $data = $request->getBody();
        
        // Only admin can change office
        if (!$this->isAdmin()) {
            unset($data['office_id']);
        }

        // Update property
        $this->propertyModel->update($id, $data);

        // Handle photos if uploaded
        if ($request->getFiles('photos')) {
            $this->handlePhotoUpload($id, $request->getFiles('photos'));
        }

        $_SESSION['toast'] = ['success' => trans('properties.updated_success')];
        $this->redirect(route('properties.edit', ['id' => $id]));
    }

    public function destroy(Request $request): void
    {
        $id = $request->getRouteParam('id');
        $property = $this->propertyModel->find($id);
        
        if (!$property) {
            $_SESSION['toast'] = ['error' => trans('properties.not_found')];
            $this->redirect(route('properties.index'));
        }

        // Check access
        if (!$this->canAccessOffice($property['office_id'])) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        // Delete property (and its photos via cascade)
        $this->propertyModel->delete($id);

        $_SESSION['toast'] = ['success' => trans('properties.deleted_success')];
        $this->redirect(route('properties.index'));
    }

    /**
     * Handle photo upload
     */
    private function handlePhotoUpload(int $propertyId, array $files): void
    {
        $uploadDir = __DIR__ . '/../../public/uploads/properties/';
        
        // Ensure directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $maxSize = config('app.upload_max_size');
        $allowedTypes = config('app.upload_allowed_types');
        
        foreach ($files['tmp_name'] as $index => $tmpName) {
            if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                continue;
            }

            // Validate file
            $fileType = $files['type'][$index];
            $fileSize = $files['size'][$index];
            
            if (!in_array($fileType, $allowedTypes)) {
                continue;
            }

            if ($fileSize > $maxSize) {
                continue;
            }

            // Generate unique filename
            $ext = pathinfo($files['name'][$index], PATHINFO_EXTENSION);
            $filename = uniqid('prop_', true) . '.' . $ext;
            $filepath = $uploadDir . $filename;
            $relativePath = 'uploads/properties/' . $filename;

            // Move file
            if (move_uploaded_file($tmpName, $filepath)) {
                // Get image dimensions
                $dimensions = @getimagesize($filepath);
                
                // Create thumbnail
                $this->createThumbnail($filepath);

                // Save to database
                $nextOrder = $this->photoModel->getNextSortOrder($propertyId);
                
                $photoData = [
                    'property_id' => $propertyId,
                    'file_path' => $relativePath,
                    'original_name' => $files['name'][$index],
                    'mime_type' => $fileType,
                    'file_size' => $fileSize,
                    'sort_order' => $nextOrder,
                    'is_hero' => 0,
                    'width' => $dimensions[0] ?? null,
                    'height' => $dimensions[1] ?? null,
                ];

                $this->photoModel->create($photoData);
            }
        }
    }

    /**
     * Create thumbnail from image
     */
    private function createThumbnail(string $filepath): void
    {
        $thumbnailDir = __DIR__ . '/../../public/uploads/properties/thumbnails/';
        
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        $thumbnailPath = $thumbnailDir . basename($filepath);

        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $source = imagecreatefromjpeg($filepath);
                break;
            case 'png':
                $source = imagecreatefrompng($filepath);
                break;
            case 'webp':
                $source = imagecreatefromwebp($filepath);
                break;
            default:
                return;
        }

        if (!$source) {
            return;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        
        // Calculate thumbnail dimensions (max 200x200, maintain aspect ratio)
        $maxSize = 200;
        $ratio = min($maxSize / $width, $maxSize / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save thumbnail
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($thumbnail, $thumbnailPath, 80);
                break;
            case 'png':
                imagepng($thumbnail, $thumbnailPath, 8);
                break;
            case 'webp':
                imagewebp($thumbnail, $thumbnailPath, 80);
                break;
        }

        imagedestroy($source);
        imagedestroy($thumbnail);
    }
}
