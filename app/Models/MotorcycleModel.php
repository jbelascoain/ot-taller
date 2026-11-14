<?php

namespace App\Models;

use CodeIgniter\Model;

class MotorcycleModel extends Model
{
    protected $table         = 'motorcycles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['customer_id', 'plate', 'brand', 'model', 'year', 'chassis', 'engine', 'notes'];

    public function withCustomer(): array
    {
        return $this->select('motorcycles.*, customers.name AS customer_name, customers.phone AS customer_phone')
            ->join('customers', 'customers.id = motorcycles.customer_id', 'left')
            ->orderBy('motorcycles.id', 'DESC')
            ->findAll();
    }

    public function findWithCustomer(int $id): ?array
    {
        return $this->select('motorcycles.*, customers.name AS customer_name, customers.phone AS customer_phone')
            ->join('customers', 'customers.id = motorcycles.customer_id', 'left')
            ->where('motorcycles.id', $id)
            ->first();
    }

    public function options(): array
    {
        return $this->select('motorcycles.*, customers.name AS customer_name')
            ->join('customers', 'customers.id = motorcycles.customer_id', 'left')
            ->orderBy('motorcycles.id', 'DESC')
            ->findAll();
    }

    public function forCustomer(int $customerId): array
    {
        return $this->where('customer_id', $customerId)->orderBy('id', 'DESC')->findAll();
    }
}
