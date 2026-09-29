<?php
namespace App\Models;

class Office extends Model
{
    protected $table = 'offices';
    protected $fillable = [
        'name', 'legal_name', 'email', 'phone', 'website',
        'address_line1', 'postal_code', 'city', 'country_code',
        'logo_path', 'status'
    ];

    public function agents()
    {
        $agentModel = new Agent();
        return $agentModel->where('office_id', $this->id);
    }

    public function properties()
    {
        $propertyModel = new Property();
        return $propertyModel->where('office_id', $this->id);
    }

    public function getById($id)
    {
        return $this->find($id);
    }

    public function getActiveOffices()
    {
        return $this->where('status', 'active');
    }

    public function create(array $data)
    {
        $data['status'] = $data['status'] ?? 'active';
        return parent::create($data);
    }
}
