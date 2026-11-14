<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table         = 'suppliers';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'contact', 'phone', 'email', 'notes'];

    public function search(?string $term): array
    {
        if ($term !== null && trim($term) !== '') {
            $term = trim($term);
            $this->groupStart()
                ->like('name', $term)
                ->orLike('contact', $term)
                ->orLike('phone', $term)
                ->groupEnd();
        }

        return $this->orderBy('id', 'DESC')->findAll();
    }

    public function options(): array
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }
}
