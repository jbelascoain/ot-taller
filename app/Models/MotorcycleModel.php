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

    /**
     * Busca motos por UID, patente, marca, modelo o cliente.
     * Pensado para el selector con búsqueda de la nueva OT.
     */
    public function search(string $term, int $limit = 20): array
    {
        $limit = max(1, min(50, $limit));
        $term  = trim($term);

        $builder = $this->select('motorcycles.*, customers.name AS customer_name')
            ->join('customers', 'customers.id = motorcycles.customer_id', 'left');

        if ($term !== '') {
            $builder->groupStart()
                ->like('motorcycles.brand', $term)
                ->orLike('motorcycles.model', $term)
                ->orLike('motorcycles.plate', $term)
                ->orLike('customers.name', $term);

            if (preg_match('/^(?:mot[-\s]?)?(\d+)$/i', $term, $matches)) {
                $builder->orWhere('motorcycles.id', (int) $matches[1]);
            }

            $builder->groupEnd();
        }

        return $builder->orderBy('motorcycles.brand', 'ASC')
            ->orderBy('motorcycles.model', 'ASC')
            ->findAll($limit);
    }

    public function forCustomer(int $customerId): array
    {
        return $this->where('customer_id', $customerId)->orderBy('id', 'DESC')->findAll();
    }
}
