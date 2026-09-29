<?php
namespace App\Controllers;

use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Http\Request;

class PropertyPhotoController extends Controller
{
    private $propertyModel;
    private $photoModel;

    public function __construct()
    {
        parent::__construct();
        $this->propertyModel = new Property();
        $this->photoModel = new PropertyPhoto();
    }

    /**
     * Upload photos for a property
     */
    public function upload(Request $request): void
    {
        $propertyId = (int)$request->getRouteParam('id');
        $property = $this->propertyModel->find($propertyId);
        
        if (!$property) {
            $this->json(['error' => trans('properties.not_found')], 404);
        }

        // Check access
        if (!$this->canAccessOffice($property['office_id'])) {
            $this->json(['error' => trans('validation.office_access_denied')], 403);
        }

        $files = $request->getFiles('photos');
        
        if (!$files || !isset($files['tmp_name'])) {
            $this->json(['error' => trans('photos.no_files_uploaded')], 400);
        }

        $uploaded = 0;
        $errors = [];
        
        $uploadDir = __DIR__ . '/../../public/uploads/properties/';
        $maxSize = config('app.upload_max_size');
        $allowedTypes = config('app.upload_allowed_types');

        foreach ($files['tmp_name'] as $index => $tmpName) {
            if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                $errors[] = trans('photos.upload_error', ['index' => $index + 1]);
                continue;
            }

            $fileType = $files['type'][$index];
            $fileSize = $files['size'][$index];

            // Validate
            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = trans('photos.invalid_type', ['index' => $index + 1]);
                continue;
            }

            if ($fileSize > $maxSize) {
                $errors[] = trans('photos.too_large', ['index' => $index + 1]);
                continue;
            }

            // Generate filename
            $ext = pathinfo($files['name'][$index], PATHINFO_EXTENSION);
            $filename = uniqid('prop_', true) . '.' . $ext;
            $filepath = $uploadDir . $filename;
            $relativePath = 'uploads/properties/' . $filename;

            // Move file
            if (move_uploaded_file($tmpName, $filepath)) {
                // Create thumbnail
                $this->createThumbnail($filepath);

                // Get dimensions
                $dimensions = @getimagesize($filepath);

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
                $uploaded++;
            } else {
                $errors[] = trans('photos.upload_failed', ['index' => $index + 1]);
            }
        }

        // Get updated photos list
        $photos = $this->photoModel->getOrderedByProperty($propertyId);
        
        $this->json([
            'success' => $uploaded > 0,
            'uploaded' => $uploaded,
            'errors' => $errors,
            'photos' => $this->formatPhotosForJson($photos),
        ]);
    }

    /**
     * Reorder photos
     */
    public function reorder(Request $request): void
    {
        $propertyId = (int)$request->getRouteParam('id');
        $property = $this->propertyModel->find($propertyId);
        
        if (!$property) {
            $this->json(['error' => trans('properties.not_found')], 404);
        }

        // Check access
        if (!$this->canAccessOffice($property['office_id'])) {
            $this->json(['error' => trans('validation.office_access_denied')], 403);
        }

        $photoIds = $request->getBody('photo_ids');
        
        if (!is_array($photoIds)) {
            $this->json(['error' => trans('photos.invalid_order')], 400);
        }

        // Validate that all photos belong to this property
        foreach ($photoIds as $photoId) {
            if (!$this->photoModel->validateOwnership($photoId, $propertyId)) {
                $this->json(['error' => trans('photos.invalid_photo')], 400);
            }
        }

        $success = $this->photoModel->reorder($propertyId, $photoIds);
        
        if ($success) {
            $photos = $this->photoModel->getOrderedByProperty($propertyId);
            $this->json([
                'success' => true,
                'photos' => $this->formatPhotosForJson($photos),
            ]);
        } else {
            $this->json(['error' => trans('photos.reorder_failed')], 500);
        }
    }

    /**
     * Set photo as hero
     */
    public function setHero(Request $request): void
    {
        $propertyId = (int)$request->getRouteParam('id');
        $photoId = (int)$request->getRouteParam('photo_id');
        
        $property = $this->propertyModel->find($propertyId);
        
        if (!$property) {
            $this->json(['error' => trans('properties.not_found')], 404);
        }

        // Check access
        if (!$this->canAccessOffice($property['office_id'])) {
            $this->json(['error' => trans('validation.office_access_denied')], 403);
        }

        // Validate photo ownership
        if (!$this->photoModel->validateOwnership($photoId, $propertyId)) {
            $this->json(['error' => trans('photos.invalid_photo')], 400);
        }

        $success = $this->photoModel->setAsHero($photoId, $propertyId);
        
        if ($success) {
            $photos = $this->photoModel->getOrderedByProperty($propertyId);
            $this->json([
                'success' => true,
                'photos' => $this->formatPhotosForJson($photos),
            ]);
        } else {
            $this->json(['error' => trans('photos.hero_failed')], 500);
        }
    }

    /**
     * Delete a photo
     */
    public function destroy(Request $request): void
    {
        $propertyId = (int)$request->getRouteParam('id');
        $photoId = (int)$request->getRouteParam('photo_id');
        
        $property = $this->propertyModel->find($propertyId);
        
        if (!$property) {
            $this->json(['error' => trans('properties.not_found')], 404);
        }

        // Check access
        if (!$this->canAccessOffice($property['office_id'])) {
            $this->json(['error' => trans('validation.office_access_denied')], 403);
        }

        // Validate photo ownership
        if (!$this->photoModel->validateOwnership($photoId, $propertyId)) {
            $this->json(['error' => trans('photos.invalid_photo')], 400);
        }

        // Get photo to delete file
        $photo = $this->photoModel->find($photoId);
        
        // Delete from database
        $success = $this->photoModel->deletePhoto($photoId, $propertyId);
        
        // Delete files
        if ($success && $photo) {
            $filepath = __DIR__ . '/../../public/' . $photo['file_path'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            $thumbnailPath = __DIR__ . '/../../public/uploads/properties/thumbnails/' . basename($photo['file_path']);
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }

        if ($success) {
            $photos = $this->photoModel->getOrderedByProperty($propertyId);
            $this->json([
                'success' => true,
                'photos' => $this->formatPhotosForJson($photos),
            ]);
        } else {
            $this->json(['error' => trans('photos.delete_failed')], 500);
        }
    }

    /**
     * Format photos for JSON response
     */
    private function formatPhotosForJson(array $photos): array
    {
        return array_map(function($photo) {
            return [
                'id' => $photo['id'],
                'file_path' => $photo['file_path'],
                'original_name' => $photo['original_name'],
                'is_hero' => (bool)$photo['is_hero'],
                'sort_order' => $photo['sort_order'],
                'thumbnail' => 'uploads/properties/thumbnails/' . basename($photo['file_path']),
            ];
        }, $photos);
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
        
        $maxSize = 200;
        $ratio = min($maxSize / $width, $maxSize / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

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
