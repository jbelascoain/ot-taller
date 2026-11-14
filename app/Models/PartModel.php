<?php

namespace App\Models;

use CodeIgniter\Model;

class PartModel extends Model
{
    protected $table         = 'parts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'description', 'sku', 'brand', 'purchase_price', 'sale_price',
        'stock', 'min_stock', 'supplier_id', 'active', 'notes',
    ];

    public function withSupplier(): array
    {
        return $this->select('parts.*, suppliers.name AS supplier_name')
            ->join('suppliers', 'suppliers.id = parts.supplier_id', 'left')
            ->orderBy('parts.id', 'DESC')
            ->findAll();
    }

    public function findWithSupplier(int $id): ?array
    {
        return $this->select('parts.*, suppliers.name AS supplier_name')
            ->join('suppliers', 'suppliers.id = parts.supplier_id', 'left')
            ->where('parts.id', $id)
            ->first();
    }

    public function options(): array
    {
        return $this->where('active', 1)->orderBy('description', 'ASC')->findAll();
    }
}
