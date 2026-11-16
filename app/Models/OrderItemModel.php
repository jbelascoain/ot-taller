<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table         = 'order_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['order_id', 'part_id', 'description', 'quantity', 'unit_price', 'status'];

    public const STATUSES = ['PRESUPUESTAR', 'PRESUPUESTADO', 'APROBADO', 'RECHAZADO'];

    public const DEFAULT_STATUS = 'PRESUPUESTADO';

    public function forOrder(int $orderId): array
    {
        return $this->select('order_items.*, parts.id AS catalog_id, parts.description AS part_description, parts.sku AS part_sku, parts.brand AS part_brand, parts.purchase_price AS part_purchase_price, parts.supplier_id AS part_supplier_id, parts.active AS part_active, parts.notes AS part_notes')
            ->join('parts', 'parts.id = order_items.part_id', 'left')
            ->where('order_id', $orderId)
            ->orderBy('order_items.id', 'ASC')
            ->findAll();
    }

    public function pendingOrderIds(): array
    {
        $rows = $this->select('order_id')
            ->where('status', 'PRESUPUESTADO')
            ->groupBy('order_id')
            ->findAll();

        return array_map(static fn ($row) => (int) $row['order_id'], $rows);
    }
}
