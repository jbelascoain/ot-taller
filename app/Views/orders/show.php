<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php $bike = trim(implode(' ', array_filter([$order['brand'] ?? '', $order['model'] ?? '']))); ?>
<div class="page-head">
    <div>
        <div class="flex center gap">
            <h1><?= esc(uid('OT', $order['id'])) ?></h1>
            <span class="badge <?= esc(estado_class($order['status'])) ?>"><?= esc(estado_label($order['status'])) ?></span>
        </div>
        <p class="subtitle"><?= esc($bike ?: uid('MOT', $order['motorcycle_id'])) ?><?= $order['plate'] ? ' · ' . esc($order['plate']) : '' ?></p>
    </div>
    <div class="actions">
        <a class="btn" href="<?= base_url('orders') ?>">Volver</a>
        <a class="btn" href="<?= base_url('orders/' . $order['id'] . '/pdf') ?>" target="_blank" rel="noreferrer">PDF</a>
        <?php if ($whatsapp): ?>
            <a class="btn" href="<?= esc($whatsapp) ?>" target="_blank" rel="noreferrer">WhatsApp</a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-2 mb-2">
    <div class="card card-pad">
        <h2 class="mb-1">Cliente</h2>
        <p class="name"><strong><?= esc($order['customer_name'] ?? '—') ?></strong></p>
        <p class="muted"><?= esc($order['customer_phone'] ?? 'Sin teléfono') ?></p>
    </div>
    <div class="card card-pad">
        <h2 class="mb-1">Moto</h2>
        <p><strong><?= esc(uid('MOT', $order['motorcycle_id'])) ?></strong> · <?= esc($bike ?: 'Sin marca/modelo') ?></p>
        <p class="muted">Patente: <?= esc($order['plate'] ?: '—') ?> · KM: <?= esc($order['km'] ?? '—') ?></p>
        <p class="muted">Chasis: <?= esc($order['chassis'] ?: '—') ?> · Motor: <?= esc($order['engine'] ?: '—') ?></p>
        <p class="muted">Ingreso: <?= esc(fecha($order['entry_date'])) ?> · Entrega: <?= esc(fecha($order['delivery_date'])) ?></p>
    </div>
</div>

<div class="card mb-2">
    <div class="card-head"><span class="card-title">Estado y datos de la orden</span></div>
    <div class="card-body">
        <form method="post" action="<?= base_url('orders/' . $order['id'] . '/status') ?>" class="actions mb-2">
            <?= csrf_field() ?>
            <label class="text-sm muted">Estado</label>
            <select name="status" style="max-width:220px">
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= esc($status) ?>" <?= $status === $order['status'] ? 'selected' : '' ?>><?= esc(estado_label($status)) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-sm" type="submit">Cambiar estado</button>
        </form>

        <form method="post" action="<?= base_url('orders/' . $order['id'] . '/update') ?>">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="field span-2">
                    <label for="problem">Problema informado</label>
                    <textarea id="problem" name="problem"><?= esc($order['problem']) ?></textarea>
                </div>
                <div class="field span-2">
                    <label for="diagnosis">Diagnóstico</label>
                    <textarea id="diagnosis" name="diagnosis"><?= esc($order['diagnosis'] ?? '') ?></textarea>
                </div>
                <div class="field">
                    <label for="km">Kilometraje</label>
                    <input type="number" id="km" name="km" value="<?= esc($order['km'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="labor">Mano de obra</label>
                    <input type="number" step="0.01" id="labor" name="labor" value="<?= esc($order['labor']) ?>">
                </div>
                <div class="field">
                    <label for="payment_method">Forma de pago</label>
                    <select id="payment_method" name="payment_method">
                        <?php foreach (['' => 'Sin definir', 'Efectivo' => 'Efectivo', 'Transferencia' => 'Transferencia', 'Cheque' => 'Cheque'] as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= $order['payment_method'] === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="invoice_number">Número de factura</label>
                    <input type="text" id="invoice_number" name="invoice_number" value="<?= esc($order['invoice_number'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="delivery_date">Fecha de entrega</label>
                    <input type="date" id="delivery_date" name="delivery_date" value="<?= esc($order['delivery_date'] ?? '') ?>">
                </div>
                <div class="field span-2">
                    <label for="notes">Observaciones</label>
                    <textarea id="notes" name="notes"><?= esc($order['notes'] ?? '') ?></textarea>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Guardar cambios</button>
        </form>
    </div>
</div>

<div class="grid grid-side">
    <div class="card">
        <div class="card-head"><span class="card-title">Totales</span></div>
        <div class="card-body">
            <div class="totals">
                <div class="row"><span class="muted">Mano de obra</span><span class="money"><?= esc(money($totals['labor'])) ?></span></div>
                <div class="row"><span class="muted">Repuestos</span><span class="money"><?= esc(money($totals['parts'])) ?></span></div>
                <div class="row"><span class="muted">Aprobado por el cliente</span><span class="money"><?= esc(money($totals['approved'])) ?></span></div>
                <div class="row total"><span>Total</span><span class="money"><?= esc(money($totals['total'])) ?></span></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <span class="card-title">Repuestos e ítems</span>
            <span class="muted text-xs"><?= count($items) ?> ítems</span>
        </div>
        <div class="card-body">
            <div class="table-wrap mb-2">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th class="num">Cant.</th>
                            <th class="num">P. unitario</th>
                            <th class="num">Subtotal</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($items) === 0): ?>
                            <tr><td colspan="6" class="empty">Todavía no hay ítems.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($items as $item): ?>
                            <?php $subtotal = (float) $item['unit_price'] * (int) $item['quantity']; ?>
                            <tr>
                                <td data-label="Descripción"><?= esc($item['description']) ?></td>
                                <td class="num" data-label="Cantidad"><?= (int) $item['quantity'] ?></td>
                                <td class="num" data-label="P. unitario"><?= esc(money($item['unit_price'])) ?></td>
                                <td class="num" data-label="Subtotal"><?= esc(money($subtotal)) ?></td>
                                <td data-label="Estado">
                                    <span class="badge <?= esc(item_estado_class($item['status'])) ?>"><?= esc(item_estado_label($item['status'])) ?></span>
                                </td>
                                <td class="cell-actions" data-label="Acciones">
                                    <div class="flex gap wrap">
                                        <form method="post" action="<?= base_url('orders/' . $order['id'] . '/items/' . $item['id'] . '/status') ?>" class="flex gap">
                                            <?= csrf_field() ?>
                                            <?php foreach ($itemStatuses as $status): ?>
                                                <button class="btn btn-sm <?= $item['status'] === $status ? 'btn-primary' : '' ?>" type="submit" name="status" value="<?= esc($status) ?>">
                                                    <?= esc(item_estado_label($status)) ?>
                                                </button>
                                            <?php endforeach; ?>
                                        </form>
                                        <form method="post" action="<?= base_url('orders/' . $order['id'] . '/items/' . $item['id'] . '/delete') ?>">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-danger" type="submit" data-confirm="¿Eliminar este ítem?">×</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <form method="post" action="<?= base_url('orders/' . $order['id'] . '/items') ?>" class="card card-pad" style="background:var(--surface-2)">
                <?= csrf_field() ?>
                <div class="radio-row">
                    <label><input type="radio" name="mode" value="catalog" checked data-toggle-target="#catalog-fields"> Del catálogo</label>
                    <label><input type="radio" name="mode" value="new" data-toggle-target="#new-item-fields"> Crear repuesto</label>
                </div>

                <div id="catalog-fields">
                    <div class="form-grid">
                        <div class="field span-2">
                            <label for="part_id">Repuesto</label>
                            <select id="part_id" name="part_id" data-part-select>
                                <option value="">Elegir repuesto…</option>
                                <?php foreach ($parts as $part): ?>
                                    <option value="<?= (int) $part['id'] ?>" data-price="<?= esc((string) $part['sale_price']) ?>" data-desc="<?= esc($part['description']) ?>">
                                        <?= esc(uid('REP', $part['id'])) ?> · <?= esc($part['description']) ?> · <?= esc(money($part['sale_price'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="quantity">Cantidad</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1">
                        </div>
                        <div class="field">
                            <label for="unit_price">Precio unitario</label>
                            <input type="number" step="0.01" id="unit_price" name="unit_price" data-part-price>
                        </div>
                    </div>
                </div>

                <div id="new-item-fields" class="hidden">
                    <div class="form-grid">
                        <div class="field span-2">
                            <label for="description">Descripción</label>
                            <input type="text" id="description" name="description">
                        </div>
                        <div class="field">
                            <label for="purchase_price">Precio de compra</label>
                            <input type="number" step="0.01" id="purchase_price" name="purchase_price">
                        </div>
                        <div class="field">
                            <label for="new_unit_price">Precio de venta (unitario)</label>
                            <input type="number" step="0.01" id="new_unit_price" name="unit_price">
                        </div>
                        <div class="field">
                            <label for="new_quantity">Cantidad</label>
                            <input type="number" id="new_quantity" name="quantity" value="1" min="1">
                        </div>
                        <div class="field">
                            <label for="supplier_id">Proveedor</label>
                            <select id="supplier_id" name="supplier_id">
                                <option value="">Sin proveedor</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= (int) $supplier['id'] ?>"><?= esc($supplier['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <label class="check span-2">
                            <input type="checkbox" name="new_supplier" value="1" data-toggle-target="#new-supplier-fields">
                            El proveedor no existe, crearlo
                        </label>
                        <div id="new-supplier-fields" class="form-grid span-2 hidden">
                            <div class="field">
                                <label for="new_supplier_name">Nombre del proveedor</label>
                                <input type="text" id="new_supplier_name" name="new_supplier_name">
                            </div>
                            <div class="field">
                                <label for="new_supplier_phone">Teléfono</label>
                                <input type="tel" id="new_supplier_phone" name="new_supplier_phone">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="field">
                        <label for="item_status">Estado del ítem</label>
                        <select id="item_status" name="item_status">
                            <?php foreach ($itemStatuses as $status): ?>
                                <option value="<?= esc($status) ?>"><?= esc(item_estado_label($status)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field" style="justify-content:flex-end">
                        <button class="btn btn-primary" type="submit">Agregar ítem</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
