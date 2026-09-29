<?php
namespace App\Models;

use App\Database\Database;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = [
        'office_id', 'email', 'password', 'first_name', 'last_name',
        'phone', 'role', 'status', 'last_login_at', 'remember_token'
    ];
    protected $hidden = ['password', 'remember_token'];

    public function office()
    {
        $officeModel = new Office();
        return $officeModel->find($this->office_id);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email)
    {
        return $this->firstWhere('email', $email);
    }

    /**
     * Create user with hashed password
     */
    public function create(array $data): int
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        $data['status'] = $data['status'] ?? 'active';
        $data['role'] = $data['role'] ?? 'agent';
        
        return parent::create($data);
    }

    /**
     * Update password
     */
    public function updatePassword($userId, string $password): bool
    {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        return $this->update($userId, ['password' => $hashed]) > 0;
    }

    /**
     * Verify password
     */
    public function verifyPassword($userId, string $password): bool
    {
        $user = $this->find($userId);
        if (!$user || !isset($user['password'])) {
            return false;
        }
        return password_verify($password, $user['password']);
    }

    /**
     * Get users by office
     */
    public function getByOffice($officeId): array
    {
        return $this->where('office_id', $officeId);
    }

    /**
     * Record login
     */
    public function recordLogin($userId): void
    {
        $this->update($userId, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Get user with office and role information
     */
    public function getWithOffice($userId)
    {
        $user = $this->find($userId);
        if (!$user) {
            return null;
        }

        $user['office'] = $this->office();
        return $user;
    }
}
