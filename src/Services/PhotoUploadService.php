<?php
namespace App\Services;

use App\Models\PropertyPhoto;

class PhotoUploadService
{
    private $photoModel;
    private $uploadDir;
    private $maxSize;
    private $allowedTypes;

    public function __construct()
    {
        $this->photoModel = new PropertyPhoto();
        $this->uploadDir = __DIR__ . '/../../public/uploads/properties/';
        $this->maxSize = config('app.upload_max_size');
        $this->allowedTypes = config('app.upload_allowed_types');
    }

    /**
     * Upload and process multiple photos
     */
    public function uploadPhotos(int $propertyId, array $files): array
    {
        $results = [
            'uploaded' => 0,
            'errors' => [],
            'photos' => [],
        ];

        foreach ($files['tmp_name'] as $index => $tmpName) {
            if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                $results['errors'][] = $this->getUploadError($files['error'][$index]);
                continue;
            }

            $result = $this->processSinglePhoto($propertyId, $files, $index);
            
            if ($result['success']) {
                $results['uploaded']++;
                $results['photos'][] = $result['photo'];
            } else {
                $results['errors'][] = $result['error'];
            }
        }

        return $results;
    }

    /**
     * Process a single photo upload
     */
    private function processSinglePhoto(int $propertyId, array $files, int $index): array
    {
        $fileType = $files['type'][$index];
        $fileSize = $files['size'][$index];

        // Validate file type
        if (!in_array($fileType, $this->allowedTypes)) {
            return [
                'success' => false,
                'error' => trans('photos.invalid_type', ['index' => $index + 1]),
            ];
        }

        // Validate file size
        if ($fileSize > $this->maxSize) {
            return [
                'success' => false,
                'error' => trans('photos.too_large', ['index' => $index + 1]),
            ];
        }

        // Generate unique filename
        $ext = pathinfo($files['name'][$index], PATHINFO_EXTENSION);
        $filename = uniqid('prop_', true) . '.' . $ext;
        $filepath = $this->uploadDir . $filename;
        $relativePath = 'uploads/properties/' . $filename;

        // Move file
        if (!move_uploaded_file($files['tmp_name'][$index], $filepath)) {
            return [
                'success' => false,
                'error' => trans('photos.upload_failed', ['index' => $index + 1]),
            ];
        }

        // Create thumbnail
        $this->createThumbnail($filepath);

        // Get image dimensions
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

        $photoId = $this->photoModel->create($photoData);
        $photoData['id'] = $photoId;

        return [
            'success' => true,
            'photo' => $photoData,
        ];
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

    /**
     * Get upload error message
     */
    private function getUploadError(int $errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return trans('photos.file_too_large');
            case UPLOAD_ERR_PARTIAL:
                return trans('photos.upload_partial');
            case UPLOAD_ERR_NO_FILE:
                return trans('photos.no_file_uploaded');
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION:
                return trans('photos.upload_error_server');
            default:
                return trans('photos.upload_error');
        }
    }

    /**
     * Delete photo and its files
     */
    public function deletePhoto(int $photoId): bool
    {
        $photo = $this->photoModel->find($photoId);
        
        if (!$photo) {
            return false;
        }

        // Delete from database
        $success = $this->photoModel->delete($photoId);
        
        if ($success) {
            // Delete main file
            $filepath = __DIR__ . '/../../public/' . $photo['file_path'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            // Delete thumbnail
            $thumbnailPath = __DIR__ . '/../../public/uploads/properties/thumbnails/' . basename($photo['file_path']);
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }

        return $success;
    }

    /**
     * Reorder photos for a property
     */
    public function reorderPhotos(int $propertyId, array $photoIds): bool
    {
        return $this->photoModel->reorder($propertyId, $photoIds);
    }

    /**
     * Set photo as hero
     */
    public function setHero(int $photoId, int $propertyId): bool
    {
        return $this->photoModel->setAsHero($photoId, $propertyId);
    }
}
