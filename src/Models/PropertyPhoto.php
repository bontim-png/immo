<?php
namespace App\Models;

class PropertyPhoto extends Model
{
    protected $table = 'property_photos';
    protected $fillable = [
        'property_id', 'file_path', 'original_name', 'mime_type',
        'file_size', 'sort_order', 'is_hero', 'width', 'height'
    ];
    protected $casts = [
        'property_id' => 'int',
        'file_size' => 'int',
        'sort_order' => 'int',
        'is_hero' => 'bool',
        'width' => 'int',
        'height' => 'int',
    ];

    public function property()
    {
        $propertyModel = new Property();
        return $propertyModel->find($this->property_id);
    }

    /**
     * Get photos for a property
     */
    public function getByProperty($propertyId): array
    {
        return $this->where('property_id', $propertyId, '=');
    }

    /**
     * Get photos in order
     */
    public function getOrderedByProperty($propertyId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE property_id = ? ORDER BY sort_order ASC, id ASC";
        return $this->query($sql, [$propertyId]);
    }

    /**
     * Get the hero photo for a property
     */
    public function getHeroByProperty($propertyId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE property_id = ? AND is_hero = 1 LIMIT 1";
        return $this->first($sql, [$propertyId]);
    }

    /**
     * Set a photo as hero and clear others
     */
    public function setAsHero($photoId, $propertyId): bool
    {
        // Clear existing hero
        Database::get()->beginTransaction();
        
        try {
            $clearSql = "UPDATE {$this->table} SET is_hero = 0 WHERE property_id = ?";
            Database::get()->prepare($clearSql)->execute([$propertyId]);
            
            // Set new hero
            $setSql = "UPDATE {$this->table} SET is_hero = 1 WHERE id = ? AND property_id = ?";
            $stmt = Database::get()->prepare($setSql);
            $result = $stmt->execute([$photoId, $propertyId]);
            
            Database::get()->commit();
            return $result && $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            Database::get()->rollBack();
            return false;
        }
    }

    /**
     * Reorder photos
     */
    public function reorder($propertyId, array $photoIds): bool
    {
        Database::get()->beginTransaction();
        
        try {
            foreach ($photoIds as $index => $photoId) {
                $sortOrder = $index + 1;
                $sql = "UPDATE {$this->table} SET sort_order = ? WHERE id = ? AND property_id = ?";
                Database::get()->prepare($sql)->execute([$sortOrder, $photoId, $propertyId]);
            }
            
            Database::get()->commit();
            return true;
        } catch (\Exception $e) {
            Database::get()->rollBack();
            return false;
        }
    }

    /**
     * Delete photo by ID
     */
    public function deletePhoto($photoId, $propertyId = null): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $params = [$photoId];
        
        if ($propertyId) {
            $sql .= " AND property_id = ?";
            $params[] = $propertyId;
        }
        
        $stmt = Database::get()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Get next sort order for a property
     */
    public function getNextSortOrder($propertyId): int
    {
        $sql = "SELECT MAX(sort_order) as max_order FROM {$this->table} WHERE property_id = ?";
        $result = $this->first($sql, [$propertyId]);
        return ($result['max_order'] ?? 0) + 1;
    }

    /**
     * Validate photo ownership
     */
    public function validateOwnership($photoId, $propertyId): bool
    {
        $photo = $this->find($photoId);
        return $photo && $photo['property_id'] == $propertyId;
    }
}
