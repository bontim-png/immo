<?php
namespace App\Models;

use App\Database\Database;

class Property extends Model
{
    protected $table = 'properties';
    protected $fillable = [
        'office_id', 'agent_id', 'reference_code', 'property_type',
        'listing_status', 'transaction_type', 'title', 'slug',
        'description', 'price', 'currency', 'living_area_m2',
        'land_area_m2', 'bedrooms', 'bathrooms', 'address_line1',
        'postal_code', 'city', 'country_code', 'latitude', 'longitude'
    ];
    protected $casts = [
        'price' => 'float',
        'living_area_m2' => 'float',
        'land_area_m2' => 'float',
        'bedrooms' => 'int',
        'bathrooms' => 'int',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function office()
    {
        $officeModel = new Office();
        return $officeModel->find($this->office_id);
    }

    public function agent()
    {
        $agentModel = new Agent();
        return $agentModel->find($this->agent_id);
    }

    public function photos()
    {
        $photoModel = new PropertyPhoto();
        return $photoModel->where('property_id', $this->id);
    }

    public function getHeroPhoto()
    {
        $photos = $this->photos();
        foreach ($photos as $photo) {
            if ($photo['is_hero']) {
                return $photo;
            }
        }
        return $photos[0] ?? null;
    }

    /**
     * Get properties for a specific office
     */
    public function getByOffice($officeId): array
    {
        return $this->where('office_id', $officeId);
    }

    /**
     * Get properties for a specific agent
     */
    public function getByAgent($agentId): array
    {
        return $this->where('agent_id', $agentId);
    }

    /**
     * Validate that property belongs to the specified office
     */
    public function validateOffice($propertyId, $officeId): bool
    {
        $property = $this->find($propertyId);
        return $property && $property['office_id'] == $officeId;
    }

    /**
     * Validate that property can be assigned to the specified agent
     * (agent must belong to the same office as the property)
     */
    public function validateAgentAssignment($propertyId, $agentId): bool
    {
        $property = $this->find($propertyId);
        if (!$property) {
            return false;
        }

        $agentModel = new Agent();
        $agent = $agentModel->find($agentId);
        
        return $agent && $agent['office_id'] == $property['office_id'];
    }

    /**
     * Get properties with pagination
     */
    public function paginate($officeId = null, $perPage = 20, $page = 1): array
    {
        $offset = ($page - 1) * $perPage;
        
        $where = '';
        $params = [];
        
        if ($officeId) {
            $where = 'WHERE office_id = ?';
            $params[] = $officeId;
        }

        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $properties = $this->query($sql, $params);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} {$where}";
        $total = $this->first($countSql, $where ? $params : [])['count'] ?? 0;

        return [
            'data' => $properties,
            'total' => (int)$total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => (int)ceil($total / $perPage),
        ];
    }

    /**
     * Search properties
     */
    public function search($officeId = null, $query = null, $status = null, $type = null): array
    {
        $where = [];
        $params = [];

        if ($officeId) {
            $where[] = 'office_id = ?';
            $params[] = $officeId;
        }

        if ($query) {
            $where[] = '(title LIKE ? OR description LIKE ? OR reference_code LIKE ?)';
            $searchParam = '%' . $query . '%';
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }

        if ($status) {
            $where[] = 'listing_status = ?';
            $params[] = $status;
        }

        if ($type) {
            $where[] = 'property_type = ?';
            $params[] = $type;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT * FROM {$this->table} {$whereClause} ORDER BY created_at DESC";

        return $this->query($sql, $params);
    }

    public function create(array $data)
    {
        $data['listing_status'] = $data['listing_status'] ?? 'draft';
        $data['transaction_type'] = $data['transaction_type'] ?? 'sale';
        $data['currency'] = $data['currency'] ?? 'EUR';
        $data['country_code'] = $data['country_code'] ?? 'FR';
        
        // Generate slug if not provided
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }
        
        return parent::create($data);
    }

    public function update($id, array $data)
    {
        // Generate slug if title changed
        if (isset($data['title']) && !isset($data['slug'])) {
            $property = $this->find($id);
            if ($property && $property['title'] !== $data['title']) {
                $data['slug'] = $this->generateSlug($data['title']);
            }
        }
        
        return parent::update($id, $data);
    }

    private function generateSlug(string $title): string
    {
        // Convert to lowercase
        $slug = strtolower($title);
        
        // Remove special characters
        $slug = preg_replace('/[^\pL\d]+/u', '-', $slug);
        
        // Remove extra hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Trim hyphens from start and end
        $slug = trim($slug, '-');
        
        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;
        while ($this->firstWhere('slug', $slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}
