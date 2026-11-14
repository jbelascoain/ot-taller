<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table         = 'customers';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'phone', 'email', 'notes'];

    public function search(?string $term): array
    {
        if ($term !== null && trim($term) !== '') {
            $term = trim($term);
            $this->groupStart()
                ->like('name', $term)
                ->orLike('phone', $term)
                ->orLike('email', $term)
                ->groupEnd();
        }

        return $this->orderBy('id', 'DESC')->findAll();
    }

    public function options(): array
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }
}
