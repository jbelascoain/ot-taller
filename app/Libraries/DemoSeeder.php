<?php

namespace App\Libraries;

use App\Models\CustomerModel;
use App\Models\MotorcycleModel;
use App\Models\OrderItemModel;
use App\Models\PartModel;
use App\Models\SettingModel;
use App\Models\SupplierModel;
use App\Models\UserModel;
use App\Models\WorkOrderModel;

class DemoSeeder
{
    public function run(): void
    {
        $settings = new SettingModel();

        if ($settings->getValue('demo_seeded') === '1') {
            return;
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $users = new UserModel();
            if (! $users->findByEmail('staff@example.com')) {
                $users->insert([
                    'name'          => 'Mecánico Demo',
                    'email'         => 'staff@example.com',
                    'password_hash' => password_hash('demo1234', PASSWORD_DEFAULT),
                    'role'          => 'STAFF',
                    'active'        => 1,
                ]);
            }

            if ($db->table('customers')->countAllResults() > 0) {
                $settings->setValue('demo_seeded', '1');
                $db->transCommit();

                return;
            }

            $customers = new CustomerModel();
            $customerIds = [];
            foreach ([
                ['Ana Gómez', '+54 11 5555-0101', 'ana@example.com'],
                ['Bruno Díaz', '+54 11 5555-0102', 'bruno@example.com'],
                ['Carla Ruiz', '+54 11 5555-0103', 'carla@example.com'],
                ['Diego Sosa', '+54 11 5555-0104', 'diego@example.com'],
            ] as [$name, $phone, $email]) {
                $customerIds[] = (int) $customers->insert([
                    'name' => $name, 'phone' => $phone, 'email' => $email, 'notes' => '',
                ], true);
            }

            $suppliers = new SupplierModel();
            $supplierIds = [];
            foreach ([
                ['Repuestos del Norte', 'Laura Méndez', '+54 11 5555-0201', 'ventas@repuestosnorte.example'],
                ['Motoservice Sur', 'Pablo Ferreyra', '+54 11 5555-0202', 'compras@motoservicesur.example'],
                ['Distribuidora Central', 'Sofía Vega', '+54 11 5555-0203', 'hola@districentral.example'],
            ] as [$name, $contact, $phone, $email]) {
                $supplierIds[] = (int) $suppliers->insert([
                    'name' => $name, 'contact' => $contact, 'phone' => $phone, 'email' => $email, 'notes' => '',
                ], true);
            }

            $parts = new PartModel();
            $partIds = [];
            foreach ([
                ['Aceite 10W-40', 'ACE-10W40', 'Motul', 9000, 14500, 12, 4, $supplierIds[0]],
                ['Filtro de aceite', 'FIL-001', 'Hiflofiltro', 4500, 7800, 8, 3, $supplierIds[1]],
                ['Pastillas de freno', 'FRE-001', 'Brembo', 12000, 21000, 6, 2, $supplierIds[0]],
                ['Cadena 428', 'CAD-428', 'DID', 18000, 32000, 3, 2, $supplierIds[1]],
                ['Kit de arrastre', 'KIT-150', 'Renthal', 38000, 62000, 2, 1, $supplierIds[2]],
                ['Bujía NGK', 'BUJ-NGK', 'NGK', 3500, 6500, 15, 5, $supplierIds[2]],
                ['Batería 12V', 'BAT-12V', 'Yuasa', 42000, 68000, 4, 2, $supplierIds[0]],
                ['Cubierta 110/90', 'CUB-110', 'Pirelli', 52000, 82000, 2, 2, $supplierIds[1]],
            ] as [$desc, $sku, $brand, $buy, $sell, $stock, $min, $supplier]) {
                $partIds[] = (int) $parts->insert([
                    'description' => $desc, 'sku' => $sku, 'brand' => $brand,
                    'purchase_price' => $buy, 'sale_price' => $sell,
                    'stock' => $stock, 'min_stock' => $min, 'supplier_id' => $supplier,
                    'active' => 1, 'notes' => '',
                ], true);
            }

            $motos = new MotorcycleModel();
            $motoIds = [];
            foreach ([
                [$customerIds[0], 'AB123CD', 'Honda', 'Wave 110', 2019, 'CH-001', 'EN-001'],
                [$customerIds[1], 'AC456EF', 'Yamaha', 'FZ 2.0', 2021, 'CH-002', 'EN-002'],
                [$customerIds[2], 'AD789GH', 'Zanella', 'RX 150', 2020, 'CH-003', 'EN-003'],
                [$customerIds[3], null, 'Motomel', 'Skua 150', 2022, 'CH-004', 'EN-004'],
                [$customerIds[0], 'AE321IJ', 'Corven', 'Triax 150', 2018, 'CH-005', 'EN-005'],
            ] as [$customer, $plate, $brand, $model, $year, $chassis, $engine]) {
                $motoIds[] = (int) $motos->insert([
                    'customer_id' => $customer, 'plate' => $plate, 'brand' => $brand, 'model' => $model,
                    'year' => $year, 'chassis' => $chassis, 'engine' => $engine, 'notes' => '',
                ], true);
            }

            $orders = new WorkOrderModel();
            $items  = new OrderItemModel();

            $ordersData = [
                [0, $customerIds[0], '-1 day', 42100, 'Service completo y revisión de frenos.', 'INGRESO', 0, null],
                [1, $customerIds[1], '-6 days', 28300, 'Ruido en la transmisión y cadena gastada.', 'PRESUPUESTADO', 15000, null],
                [2, $customerIds[2], '-12 days', 15200, 'No arranca en frío, revisar batería y bujía.', 'EN_REPARACION', 18000, null],
                [3, $customerIds[3], '-20 days', 9800, 'Cambio de aceite y filtro.', 'PARA_ENTREGAR', 12000, null],
                [4, $customerIds[0], '-45 days', 51000, 'Service de 10.000 km y cubierta trasera.', 'ENTREGADO', 22000, '-40 days'],
                [2, $customerIds[2], '-30 days', 61000, 'Presupuesto de kit de arrastre y frenos.', 'CANCELADO', 10000, null],
            ];

            foreach ($ordersData as $index => [$motoIdx, $customer, $entryOffset, $km, $problem, $status, $labor, $deliveryOffset]) {
                $orderId = (int) $orders->insert([
                    'motorcycle_id' => $motoIds[$motoIdx],
                    'customer_id'   => $customer,
                    'entry_date'    => date('Y-m-d', strtotime($entryOffset)),
                    'km'            => $km,
                    'problem'       => $problem,
                    'diagnosis'     => $status === 'INGRESO' ? '' : 'Diagnóstico de demo: revisión realizada.',
                    'status'        => $status,
                    'labor'         => $labor,
                    'payment_method' => in_array($status, ['ENTREGADO'], true) ? 'Efectivo' : '',
                    'invoice_number' => $status === 'ENTREGADO' ? 'A-0001-00000001' : '',
                    'notes'         => '',
                    'delivery_date' => $deliveryOffset ? date('Y-m-d', strtotime($deliveryOffset)) : null,
                ], true);

                $itemPlan = match ($index) {
                    0 => [[0, $partIds[0], 1, 14500, 'APROBADO'], [0, $partIds[1], 1, 7800, 'PRESUPUESTADO']],
                    1 => [[0, $partIds[3], 1, 32000, 'PRESUPUESTADO'], [0, $partIds[4], 1, 62000, 'PRESUPUESTADO']],
                    2 => [[0, $partIds[6], 1, 68000, 'APROBADO'], [0, $partIds[5], 2, 6500, 'APROBADO']],
                    3 => [[0, $partIds[0], 1, 14500, 'APROBADO'], [0, $partIds[1], 1, 7800, 'APROBADO']],
                    4 => [[0, $partIds[7], 1, 82000, 'APROBADO'], [0, $partIds[0], 1, 14500, 'APROBADO']],
                    5 => [[0, $partIds[4], 1, 62000, 'RECHAZADO'], [0, $partIds[2], 1, 21000, 'RECHAZADO']],
                };

                foreach ($itemPlan as [$unused, $partId, $qty, $price, $itemStatus]) {
                    $items->insert([
                        'order_id'    => $orderId,
                        'part_id'     => $partId,
                        'description' => $parts->find($partId)['description'],
                        'quantity'    => $qty,
                        'unit_price'  => $price,
                        'status'      => $itemStatus,
                    ]);
                }
            }

            $settings->setValue('demo_seeded', '1');
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }
}
