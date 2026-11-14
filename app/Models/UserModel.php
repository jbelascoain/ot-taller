<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = ['name', 'email', 'password_hash', 'role', 'active', 'must_change_password'];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', strtolower(trim($email)))->first();
    }

    public function active(): array
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }

    public function countActiveAdmins(): int
    {
        return $this->where('role', 'ADMIN')->where('active', 1)->countAllResults();
    }
}
