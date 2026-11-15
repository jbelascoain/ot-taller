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

    /**
     * Busca repuestos activos por descripción, SKU, marca, proveedor o UID.
     * Pensado para el selector con búsqueda del detalle de la orden.
     */
    public function search(string $term, int $limit = 20): array
    {
        $limit = max(1, min(50, $limit));
        $term  = trim($term);

        $builder = $this->select('parts.id, parts.description, parts.sku, parts.brand, parts.sale_price, parts.stock, suppliers.name AS supplier_name')
            ->join('suppliers', 'suppliers.id = parts.supplier_id', 'left')
            ->where('parts.active', 1);

        if ($term !== '') {
            $builder->groupStart()
                ->like('parts.description', $term)
                ->orLike('parts.sku', $term)
                ->orLike('parts.brand', $term)
                ->orLike('suppliers.name', $term);

            // "REP-123", "rep123" o "123" buscan por número de repuesto.
            if (preg_match('/^(?:rep[-\s]?)?(\d+)$/i', $term, $matches)) {
                $builder->orWhere('parts.id', (int) $matches[1]);
            }

            $builder->groupEnd();
        }

        return $builder->orderBy('parts.description', 'ASC')
            ->findAll($limit);
    }
}
