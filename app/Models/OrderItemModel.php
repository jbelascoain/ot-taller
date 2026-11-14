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

    public const STATUSES = ['PRESUPUESTADO', 'APROBADO', 'RECHAZADO'];

    public function forOrder(int $orderId): array
    {
        return $this->select('order_items.*, parts.id AS catalog_id')
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
