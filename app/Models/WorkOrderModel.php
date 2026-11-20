<?php

namespace App\Models;

use CodeIgniter\Model;

class WorkOrderModel extends Model
{
    protected $table         = 'work_orders';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'motorcycle_id', 'customer_id', 'entry_date', 'km', 'problem', 'diagnosis',
        'status', 'labor', 'payment_method', 'invoice_number', 'notes', 'next_service', 'delivery_date',
    ];

    public const STATUSES = ['INGRESO', 'PRESUPUESTADO', 'EN_REPARACION', 'PARA_ENTREGAR', 'ENTREGADO', 'CANCELADO'];

    private function baseSelect()
    {
        return $this->select('work_orders.*, motorcycles.brand, motorcycles.model, motorcycles.plate, motorcycles.chassis, motorcycles.engine')
            ->join('motorcycles', 'motorcycles.id = work_orders.motorcycle_id', 'left')
            ->join('customers', 'customers.id = work_orders.customer_id', 'left')
            ->select('customers.name AS customer_name, customers.phone AS customer_phone');
    }

    public function withDetails(): array
    {
        return $this->baseSelect()->orderBy('work_orders.id', 'DESC')->findAll();
    }

    public function findWithDetails(int $id): ?array
    {
        return $this->baseSelect()->where('work_orders.id', $id)->first();
    }

    public function forCustomer(int $customerId): array
    {
        return $this->baseSelect()->where('work_orders.customer_id', $customerId)->orderBy('work_orders.id', 'DESC')->findAll();
    }

    public function forMotorcycle(int $motorcycleId): array
    {
        return $this->baseSelect()->where('work_orders.motorcycle_id', $motorcycleId)->orderBy('work_orders.id', 'DESC')->findAll();
    }

    public function totals(int $orderId): array
    {
        $items = db_connect()->table('order_items')->where('order_id', $orderId)->get()->getResultArray();
        $parts = 0.0;
        $approved = 0.0;

        foreach ($items as $item) {
            $subtotal = (float) $item['unit_price'] * (int) $item['quantity'];
            $parts += $subtotal;
            if ($item['status'] === 'APROBADO') {
                $approved += $subtotal;
            }
        }

        $order = $this->find($orderId);
        $labor = (float) ($order['labor'] ?? 0);

        return [
            'parts'    => $parts,
            'approved' => $approved,
            'labor'    => $labor,
            'total'    => $labor + $parts,
        ];
    }
}
