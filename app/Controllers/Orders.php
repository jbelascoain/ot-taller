<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\MotorcycleModel;
use App\Models\OrderItemModel;
use App\Models\PartModel;
use App\Models\SupplierModel;
use App\Models\WorkOrderModel;
use Dompdf\Dompdf;

class Orders extends BaseController
{
    private function numberOrNull($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Pasa la OT a "Presupuestado" cuando ya tiene diagnóstico y todos sus
     * repuestos están presupuestados. Solo avanza desde "Ingreso" para no
     * retroceder órdenes que ya están en reparación o entregadas.
     */
    private function syncQuotedOrderStatus(int $orderId): bool
    {
        $order = (new WorkOrderModel())->find($orderId);

        if (! $order || $order['status'] !== 'INGRESO' || trim((string) $order['diagnosis']) === '') {
            return false;
        }

        $items = (new OrderItemModel())->where('order_id', $orderId)->findAll();

        if (count($items) === 0) {
            return false;
        }

        foreach ($items as $item) {
            if ($item['status'] !== 'PRESUPUESTADO') {
                return false;
            }
        }

        (new WorkOrderModel())->update($orderId, ['status' => 'PRESUPUESTADO']);

        return true;
    }

    public function index()
    {
        $term   = trim((string) $this->request->getGet('q'));
        $orders = (new WorkOrderModel())->withDetails();

        if ($term !== '') {
            $needle = mb_strtolower($term);
            $orders = array_values(array_filter($orders, static function (array $order) use ($needle): bool {
                $haystack = mb_strtolower(implode(' ', [
                    uid('OT', $order['id']),
                    $order['customer_name'] ?? '',
                    $order['brand'] ?? '',
                    $order['model'] ?? '',
                    $order['plate'] ?? '',
                ]));

                return str_contains($haystack, $needle);
            }));
        }

        return view('orders/index', [
            'title'  => 'Órdenes',
            'orders' => $orders,
            'term'   => $term,
        ]);
    }

    public function new()
    {
        return view('orders/new', [
            'title' => 'Nueva orden',
            'today' => date('Y-m-d'),
        ]);
    }

    public function create()
    {
        $db     = db_connect();
        $mode   = $this->request->getPost('mode') === 'new' ? 'new' : 'existing';
        $problem = trim((string) $this->request->getPost('problem'));
        $entry   = (string) ($this->request->getPost('entry_date') ?: date('Y-m-d'));

        if ($problem === '') {
            return redirect()->back()->withInput()->with('error', 'Contá el problema de la moto.');
        }

        $db->transBegin();

        try {
            $customerId = 0;
            $motoId     = 0;

            if ($mode === 'existing') {
                $motoId = (int) $this->request->getPost('motorcycle_id');
                $moto   = (new MotorcycleModel())->find($motoId);

                if (! $moto) {
                    throw new \RuntimeException('Elegí una moto existente o creá una nueva.');
                }

                $customerId = (int) $moto['customer_id'];
            } else {
                $customerId = (int) $this->request->getPost('customer_id');

                if ($customerId === 0) {
                    $name = trim((string) $this->request->getPost('new_customer_name'));
                    if ($name === '') {
                        throw new \RuntimeException('Elegí un cliente o cargá el nombre del cliente nuevo.');
                    }

                    $customerId = (int) (new CustomerModel())->insert([
                        'name'  => $name,
                        'phone' => trim((string) $this->request->getPost('new_customer_phone')),
                        'email' => trim((string) $this->request->getPost('new_customer_email')),
                        'notes' => '',
                    ], true);
                }

                $brand = trim((string) $this->request->getPost('brand'));
                $model = trim((string) $this->request->getPost('model'));
                if ($brand === '' || $model === '') {
                    throw new \RuntimeException('Para la moto nueva, completá marca y modelo.');
                }

                $motoId = (int) (new MotorcycleModel())->insert([
                    'customer_id' => $customerId,
                    'plate'       => trim((string) $this->request->getPost('plate')),
                    'brand'       => $brand,
                    'model'       => $model,
                    'year'        => $this->numberOrNull($this->request->getPost('year')),
                    'chassis'     => trim((string) $this->request->getPost('chassis')),
                    'engine'      => trim((string) $this->request->getPost('engine')),
                    'notes'       => '',
                ], true);
            }

            $orderId = (int) (new WorkOrderModel())->insert([
                'motorcycle_id' => $motoId,
                'customer_id'   => $customerId,
                'entry_date'    => $entry,
                'km'            => $this->numberOrNull($this->request->getPost('km')),
                'problem'       => $problem,
                'diagnosis'     => trim((string) $this->request->getPost('diagnosis')),
                'status'        => 'INGRESO',
                'labor'         => 0,
                'notes'         => '',
            ], true);

            $db->transCommit();

            return redirect()->to('/orders/' . $orderId)->with('success', 'Orden creada.');
        } catch (\Throwable $e) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(int $id)
    {
        $order = (new WorkOrderModel())->findWithDetails($id);

        if (! $order) {
            return redirect()->to('/orders')->with('error', 'Orden no encontrada.');
        }

        $items  = (new OrderItemModel())->forOrder($id);
        $totals = (new WorkOrderModel())->totals($id);

        $moto = trim(implode(' ', array_filter([$order['brand'] ?? '', $order['model'] ?? ''])));
        $message = whatsapp_message(array_merge($order, ['total' => $totals['total']]), $moto ?: uid('MOT', $order['motorcycle_id']));

        return view('orders/show', [
            'title'    => 'Orden ' . uid('OT', $id),
            'order'    => $order,
            'items'    => $items,
            'totals'   => $totals,
            'suppliers' => (new SupplierModel())->options(),
            'statuses' => WorkOrderModel::STATUSES,
            'itemStatuses' => OrderItemModel::STATUSES,
            'defaultItemStatus' => OrderItemModel::DEFAULT_STATUS,
            'whatsapp' => wa_link($order['customer_phone'] ?? '', $message),
        ]);
    }

    public function update(int $id)
    {
        $order = (new WorkOrderModel())->find($id);
        if (! $order) {
            return redirect()->to('/orders')->with('error', 'Orden no encontrada.');
        }

        $labor = $this->numberOrNull($this->request->getPost('labor'));
        if ($labor === null || $labor < 0) {
            return redirect()->back()->with('error', 'La mano de obra debe ser un número.');
        }

        (new WorkOrderModel())->update($id, [
            'problem'        => trim((string) $this->request->getPost('problem')),
            'diagnosis'      => trim((string) $this->request->getPost('diagnosis')),
            'km'             => $this->numberOrNull($this->request->getPost('km')),
            'labor'          => $labor,
            'payment_method' => trim((string) $this->request->getPost('payment_method')),
            'invoice_number' => trim((string) $this->request->getPost('invoice_number')),
            'notes'          => trim((string) $this->request->getPost('notes')),
            'delivery_date'  => $this->request->getPost('delivery_date') ?: null,
        ]);

        $autoQuoted = $this->syncQuotedOrderStatus($id);
        $message    = 'Orden actualizada.' . ($autoQuoted ? ' La orden pasó a Presupuestado.' : '');

        return redirect()->to('/orders/' . $id)->with('success', $message);
    }

    public function status(int $id)
    {
        $status = (string) $this->request->getPost('status');

        if (! in_array($status, WorkOrderModel::STATUSES, true)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'error' => 'Estado inválido']);
            }

            return redirect()->back()->with('error', 'Estado inválido.');
        }

        (new WorkOrderModel())->update($id, ['status' => $status]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => true, 'status' => $status]);
        }

        return redirect()->to('/orders/' . $id)->with('success', 'Estado actualizado.');
    }

    public function addItem(int $id)
    {
        $order = (new WorkOrderModel())->find($id);
        if (! $order) {
            return redirect()->to('/orders')->with('error', 'Orden no encontrada.');
        }

        $mode     = $this->request->getPost('mode') === 'new' ? 'new' : 'catalog';
        $quantity = max(1, (int) $this->request->getPost('quantity'));
        $price    = $this->numberOrNull($this->request->getPost('unit_price'));
        $status   = (string) $this->request->getPost('item_status');
        if (! in_array($status, OrderItemModel::STATUSES, true)) {
            $status = $mode === 'new' ? 'PRESUPUESTAR' : OrderItemModel::DEFAULT_STATUS;
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $partId     = null;
            $description = '';

            if ($price !== null && $price < 0) {
                throw new \RuntimeException('El precio unitario no puede ser negativo.');
            }

            if ($mode === 'catalog') {
                $partId = (int) $this->request->getPost('part_id');
                $part   = (new PartModel())->find($partId);

                if (! $part) {
                    throw new \RuntimeException('Elegí un repuesto del catálogo.');
                }

                $description = $part['description'];
                if ($price === null || $price == 0) {
                    $price = (float) $part['sale_price'];
                }
            } else {
                $description = trim((string) $this->request->getPost('description'));
                if ($description === '') {
                    throw new \RuntimeException('Falta la descripción del repuesto.');
                }

                $supplierId = (int) $this->request->getPost('supplier_id');
                $newSupplier = trim((string) $this->request->getPost('new_supplier_name'));

                if ($supplierId === 0 && $newSupplier !== '') {
                    $supplierId = (int) (new SupplierModel())->insert([
                        'name'    => $newSupplier,
                        'contact' => '',
                        'phone'   => trim((string) $this->request->getPost('new_supplier_phone')),
                        'email'   => '',
                        'notes'   => '',
                    ], true);
                }

                $partId = (int) (new PartModel())->insert([
                    'description'    => $description,
                    'sku'            => '',
                    'brand'          => '',
                    'purchase_price' => $this->numberOrNull($this->request->getPost('purchase_price')) ?? 0,
                    'sale_price'     => $price ?? 0,
                    'stock'          => 0,
                    'min_stock'      => 0,
                    'supplier_id'    => $supplierId ?: null,
                    'active'         => 1,
                    'notes'          => '',
                ], true);
            }

            $price = $price ?? 0.0;

            (new OrderItemModel())->insert([
                'order_id'    => $id,
                'part_id'     => $partId ?: null,
                'description' => $description,
                'quantity'    => $quantity,
                'unit_price'  => $price,
                'status'      => $status,
            ]);

            $db->transCommit();

            $autoQuoted = $this->syncQuotedOrderStatus($id);
            $message    = 'Ítem agregado.' . ($autoQuoted ? ' La orden pasó a Presupuestado.' : '');

            return redirect()->to('/orders/' . $id . '#add-item')->with('success', $message);
        } catch (\Throwable $e) {
            $db->transRollback();

            return redirect()->to('/orders/' . $id . '#add-item')->withInput()->with('error', $e->getMessage());
        }
    }

    public function itemStatus(int $orderId, int $itemId)
    {
        $status = (string) $this->request->getPost('status');

        if (! in_array($status, OrderItemModel::STATUSES, true)) {
            return redirect()->back()->with('error', 'Estado de ítem inválido.');
        }

        (new OrderItemModel())
            ->where('id', $itemId)
            ->where('order_id', $orderId)
            ->set(['status' => $status])
            ->update();

        $autoQuoted = $this->syncQuotedOrderStatus($orderId);
        $message    = 'Ítem actualizado.' . ($autoQuoted ? ' La orden pasó a Presupuestado.' : '');

        return redirect()->to('/orders/' . $orderId . '#items')->with('success', $message);
    }

    public function updateItem(int $orderId, int $itemId)
    {
        $item = (new OrderItemModel())
            ->where('id', $itemId)
            ->where('order_id', $orderId)
            ->first();

        if (! $item) {
            return redirect()->to('/orders/' . $orderId . '#items')->with('error', 'Ítem no encontrado.');
        }

        $part = ! empty($item['part_id']) ? (new PartModel())->find((int) $item['part_id']) : null;

        $description = trim((string) $this->request->getPost('description'));
        $quantity    = max(1, (int) $this->request->getPost('quantity'));
        $price       = $this->numberOrNull($this->request->getPost('unit_price')) ?? 0.0;
        $status      = (string) $this->request->getPost('status');

        $sku           = trim((string) $this->request->getPost('sku'));
        $brand         = trim((string) $this->request->getPost('brand'));
        $purchasePrice = $this->numberOrNull($this->request->getPost('purchase_price')) ?? 0.0;
        $salePrice     = $this->numberOrNull($this->request->getPost('sale_price')) ?? 0.0;
        $stock         = max(0, (int) $this->request->getPost('stock'));
        $minStock      = max(0, (int) $this->request->getPost('min_stock'));
        $supplierId    = (int) ($this->request->getPost('supplier_id') ?? 0);
        $active        = $this->request->getPost('active') === '1' ? 1 : 0;
        $notes         = trim((string) $this->request->getPost('notes'));

        if ($description === '') {
            return redirect()->to('/orders/' . $orderId . '#items')->with('error', 'La descripción es obligatoria.');
        }

        if (mb_strlen($description) > 200) {
            return redirect()->to('/orders/' . $orderId . '#items')->with('error', 'La descripción no puede superar los 200 caracteres.');
        }

        if (mb_strlen($sku) > 80) {
            return redirect()->to('/orders/' . $orderId . '#items')->with('error', 'El modelo no puede superar los 80 caracteres.');
        }

        if (mb_strlen($brand) > 80) {
            return redirect()->to('/orders/' . $orderId . '#items')->with('error', 'La marca no puede superar los 80 caracteres.');
        }

        if ($price < 0 || $purchasePrice < 0 || $salePrice < 0) {
            return redirect()->to('/orders/' . $orderId . '#items')->with('error', 'Los precios no pueden ser negativos.');
        }

        if ($supplierId > 0 && ! (new SupplierModel())->find($supplierId)) {
            $supplierId = 0;
        }

        if (! in_array($status, OrderItemModel::STATUSES, true)) {
            $status = $item['status'];
        }

        $db = db_connect();
        $db->transBegin();

        try {
            (new OrderItemModel())->update($itemId, [
                'description' => $description,
                'quantity'    => $quantity,
                'unit_price'  => $price,
                'status'      => $status,
            ]);

            if ($part) {
                (new PartModel())->update((int) $part['id'], [
                    'description'    => $description,
                    'sku'            => $sku,
                    'brand'          => $brand,
                    'purchase_price' => $purchasePrice,
                    'sale_price'     => $salePrice,
                    'stock'          => $stock,
                    'min_stock'      => $minStock,
                    'supplier_id'    => $supplierId ?: null,
                    'active'         => $active,
                    'notes'          => $notes,
                ]);
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            return redirect()->to('/orders/' . $orderId . '#items')->with('error', 'No se pudo guardar el repuesto.');
        }

        $autoQuoted = $this->syncQuotedOrderStatus($orderId);
        $message    = 'Repuesto actualizado.' . ($autoQuoted ? ' La orden pasó a Presupuestado.' : '');

        return redirect()->to('/orders/' . $orderId . '#items')->with('success', $message);
    }

    public function deleteItem(int $orderId, int $itemId)
    {
        (new OrderItemModel())
            ->where('id', $itemId)
            ->where('order_id', $orderId)
            ->delete();

        $autoQuoted = $this->syncQuotedOrderStatus($orderId);
        $message    = 'Ítem eliminado.' . ($autoQuoted ? ' La orden pasó a Presupuestado.' : '');

        return redirect()->to('/orders/' . $orderId . '#items')->with('success', $message);
    }

    public function pdf(int $id)
    {
        $order = (new WorkOrderModel())->findWithDetails($id);

        if (! $order) {
            return redirect()->to('/orders')->with('error', 'Orden no encontrada.');
        }

        $items  = (new OrderItemModel())->forOrder($id);
        $totals = (new WorkOrderModel())->totals($id);

        $logoFile = setting('logo_path');
        $logo     = null;
        if ($logoFile !== '' && is_file(FCPATH . 'uploads/' . $logoFile)) {
            $path = FCPATH . 'uploads/' . $logoFile;
            $mime = mime_content_type($path) ?: 'image/png';
            $logo = 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
        }

        $html = view('pdf/order', [
            'order'   => $order,
            'items'   => $items,
            'totals'  => $totals,
            'logo'    => $logo,
            'workshop' => setting('workshop_name', 'Taller'),
            'contact'  => setting('workshop_contact'),
            'footer'   => setting('pdf_footer'),
        ]);

        $dompdf = new Dompdf(['isRemoteEnabled' => false, 'defaultFont' => 'DejaVu Sans']);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = uid('OT', $id) . '.pdf';
        $inline   = $this->request->getGet('download') !== '1';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    public function whatsapp(int $id)
    {
        $order = (new WorkOrderModel())->findWithDetails($id);

        if (! $order) {
            return redirect()->to('/orders')->with('error', 'Orden no encontrada.');
        }

        $totals = (new WorkOrderModel())->totals($id);
        $moto   = trim(implode(' ', array_filter([$order['brand'] ?? '', $order['model'] ?? ''])));
        $link   = wa_link($order['customer_phone'] ?? '', whatsapp_message(array_merge($order, ['total' => $totals['total']]), $moto));

        if ($link === '') {
            return redirect()->to('/orders/' . $id)->with('error', 'El cliente no tiene teléfono cargado.');
        }

        return redirect()->to($link);
    }
}
