<?php
namespace App\Models;

class Agent extends Model
{
    protected $table = 'agents';
    protected $fillable = [
        'office_id', 'first_name', 'last_name', 'email', 'phone',
        'photo_path', 'status'
    ];

    public function office()
    {
        $officeModel = new Office();
        return $officeModel->find($this->office_id);
    }

    public function properties()
    {
        $propertyModel = new Property();
        return $propertyModel->where('agent_id', $this->id);
    }

    public function getByOffice($officeId)
    {
        return $this->where('office_id', $officeId);
    }

    public function getActiveAgents($officeId = null)
    {
        $query = $this->where('status', 'active');
        if ($officeId) {
            $query = array_filter($query, fn($a) => $a['office_id'] == $officeId);
        }
        return $query;
    }

    public function create(array $data)
    {
        $data['status'] = $data['status'] ?? 'active';
        return parent::create($data);
    }

    /**
     * Get agents belonging to a specific office
     */
    public function getAgentsByOffice($officeId): array
    {
        return $this->where('office_id', $officeId);
    }

    /**
     * Validate that agent belongs to the specified office
     */
    public function validateOffice($agentId, $officeId): bool
    {
        $agent = $this->find($agentId);
        return $agent && $agent['office_id'] == $officeId;
    }
}
