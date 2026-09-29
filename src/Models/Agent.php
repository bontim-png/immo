<?php
namespace App\Models;

use App\Database\Database;

class Agent extends Model
{
    protected $table = 'agents';
    protected $fillable = [
        'office_id', 'first_name', 'last_name', 'email', 'phone',
        'photo_path', 'password', 'role', 'status', 'last_login_at'
    ];
    protected $hidden = ['password'];

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

    /**
     * Find agent by email
     */
    public function findByEmail(string $email)
    {
        return $this->firstWhere('email', $email);
    }

    /**
     * Create agent with hashed password
     */
    public function create(array $data): int
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        $data['status'] = $data['status'] ?? 'active';
        $data['role'] = $data['role'] ?? 'agent';
        
        return parent::create($data);
    }

    /**
     * Update password
     */
    public function updatePassword($agentId, string $password): bool
    {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        return $this->update($agentId, ['password' => $hashed]) > 0;
    }

    /**
     * Verify password
     */
    public function verifyPassword($agentId, string $password): bool
    {
        $agent = $this->find($agentId);
        if (!$agent || !isset($agent['password']) || empty($agent['password'])) {
            return false;
        }
        return password_verify($password, $agent['password']);
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

    /**
     * Record login
     */
    public function recordLogin($agentId): void
    {
        $this->update($agentId, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Get agent with office information
     */
    public function getWithOffice($agentId)
    {
        $agent = $this->find($agentId);
        if (!$agent) {
            return null;
        }

        $agent['office'] = $this->office();
        return $agent;
    }

    /**
     * Check if agent is admin
     */
    public function isAdmin($agentId): bool
    {
        $agent = $this->find($agentId);
        return $agent && ($agent['role'] ?? '') === 'admin';
    }

    /**
     * Get all admins
     */
    public function getAdmins(): array
    {
        return array_filter($this->all(), fn($a) => ($a['role'] ?? '') === 'admin');
    }
}
