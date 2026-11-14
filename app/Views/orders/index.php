<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <h1>Órdenes</h1>
        <p class="subtitle">Todas las órdenes de trabajo</p>
    </div>
    <div class="actions">
        <a class="btn btn-primary" href="<?= base_url('orders/new') ?>">+ Nueva orden</a>
    </div>
</div>

<form method="get" action="<?= base_url('orders') ?>" class="actions mb-2">
    <input type="text" name="q" value="<?= esc($term) ?>" placeholder="Buscar por OT, cliente, moto o patente" style="max-width:360px">
    <button class="btn" type="submit">Buscar</button>
    <?php if ($term !== ''): ?><a class="btn btn-ghost" href="<?= base_url('orders') ?>">Limpiar</a><?php endif; ?>
</form>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>OT</th>
                    <th>Cliente</th>
                    <th>Moto</th>
                    <th>Ingreso</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) === 0): ?>
                    <tr><td colspan="5" class="empty">No hay órdenes todavía.</td></tr>
                <?php endif; ?>
                <?php foreach ($orders as $order): ?>
                    <?php $bike = trim(implode(' ', array_filter([$order['brand'] ?? '', $order['model'] ?? '']))); ?>
                    <tr>
                        <td><a href="<?= base_url('orders/' . $order['id']) ?>"><strong><?= esc(uid('OT', $order['id'])) ?></strong></a></td>
                        <td><?= esc($order['customer_name'] ?? '—') ?></td>
                        <td><?= esc($bike ?: '—') ?><?= $order['plate'] ? ' · ' . esc($order['plate']) : '' ?></td>
                        <td><?= esc(fecha($order['entry_date'])) ?></td>
                        <td><span class="badge <?= esc(estado_class($order['status'])) ?>"><?= esc(estado_label($order['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
