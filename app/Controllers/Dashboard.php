<?php

namespace App\Controllers;

use App\Models\OrderItemModel;
use App\Models\WorkOrderModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $orders = (new WorkOrderModel())->withDetails();
        $items  = db_connect()->table('order_items')->get()->getResultArray();

        $byOrder = [];
        foreach ($items as $item) {
            $id = (int) $item['order_id'];
            $byOrder[$id]['parts'] = ($byOrder[$id]['parts'] ?? 0) + ((float) $item['unit_price'] * (int) $item['quantity']);
            if ($item['status'] === 'PRESUPUESTADO') {
                $byOrder[$id]['pending'] = true;
            }
            if ($item['status'] === 'APROBADO') {
                $byOrder[$id]['approved'] = ($byOrder[$id]['approved'] ?? 0) + ((float) $item['unit_price'] * (int) $item['quantity']);
            }
        }

        foreach ($orders as &$order) {
            $id = (int) $order['id'];
            $order['parts_total'] = $byOrder[$id]['parts'] ?? 0;
            $order['approved_total'] = $byOrder[$id]['approved'] ?? 0;
            $order['total'] = (float) $order['labor'] + $order['parts_total'];
            $order['has_pending'] = $byOrder[$id]['pending'] ?? false;
        }
        unset($order);

        $filter = $this->request->getGet('filter');
        $waiting = array_values(array_filter($orders, static function (array $order): bool {
            if ($order['status'] === 'PARA_ENTREGAR') {
                return true;
            }
            if (in_array($order['status'], ['ENTREGADO', 'CANCELADO'], true)) {
                return false;
            }

            return (bool) $order['has_pending'];
        }));

        $visible = $filter === 'waiting' ? $waiting : $orders;

        $columns = [];
        foreach (WorkOrderModel::STATUSES as $status) {
            $columns[$status] = array_values(array_filter($visible, static fn (array $order): bool => $order['status'] === $status));
        }

        return view('dashboard/index', [
            'title'      => 'Tablero',
            'orders'     => $visible,
            'columns'    => $columns,
            'waiting'    => $waiting,
            'filter'     => $filter,
            'openCount'  => count(array_filter($orders, static fn (array $o): bool => ! in_array($o['status'], ['ENTREGADO', 'CANCELADO'], true))),
        ]);
    }
}
