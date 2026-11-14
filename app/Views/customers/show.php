<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1><?= esc($customer['name']) ?></h1><p class="subtitle">Cliente</p></div>
    <div class="actions">
        <a class="btn" href="<?= base_url('customers') ?>">Volver</a>
        <a class="btn" href="<?= base_url('customers/' . $customer['id'] . '/edit') ?>">Editar</a>
        <form method="post" action="<?= base_url('customers/' . $customer['id'] . '/delete') ?>" class="inline-form">
            <?= csrf_field() ?>
            <button class="btn btn-danger" type="submit" data-confirm="¿Eliminar este cliente?">Eliminar</button>
        </form>
    </div>
</div>
<div class="grid grid-2 mb-2">
    <div class="card card-pad">
        <h2 class="mb-1">Datos</h2>
        <div class="kv">
            <span class="k">Teléfono</span><span><?= esc($customer['phone'] ?: '—') ?></span>
            <span class="k">Email</span><span><?= esc($customer['email'] ?: '—') ?></span>
            <span class="k">Notas</span><span><?= nl2br(esc($customer['notes'] ?: '—')) ?></span>
        </div>
    </div>
    <div class="card card-pad">
        <h2 class="mb-1">Motos</h2>
        <?php if (count($motos) === 0): ?><p class="muted">Sin motos.</p><?php endif; ?>
        <?php foreach ($motos as $moto): ?>
            <div class="list-row">
                <div><a href="<?= base_url('motorcycles/' . $moto['id']) ?>"><strong><?= esc(uid('MOT', $moto['id'])) ?></strong></a>
                    <div class="muted text-sm"><?= esc(trim($moto['brand'] . ' ' . $moto['model'])) ?><?= $moto['plate'] ? ' · ' . esc($moto['plate']) : '' ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="card">
    <div class="card-head"><span class="card-title">Órdenes</span></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>OT</th><th>Moto</th><th>Ingreso</th><th>Estado</th></tr></thead>
            <tbody>
                <?php if (count($orders) === 0): ?><tr><td colspan="4" class="empty">Sin órdenes.</td></tr><?php endif; ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><a href="<?= base_url('orders/' . $order['id']) ?>"><strong><?= esc(uid('OT', $order['id'])) ?></strong></a></td>
                        <td><?= esc(trim(($order['brand'] ?? '') . ' ' . ($order['model'] ?? ''))) ?></td>
                        <td><?= esc(fecha($order['entry_date'])) ?></td>
                        <td><span class="badge <?= esc(estado_class($order['status'])) ?>"><?= esc(estado_label($order['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
