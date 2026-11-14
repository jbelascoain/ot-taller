<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1><?= esc(uid('MOT', $moto['id'])) ?> · <?= esc(trim($moto['brand'] . ' ' . $moto['model'])) ?></h1><p class="subtitle">Ficha de la moto</p></div>
    <div class="actions">
        <a class="btn" href="<?= base_url('motorcycles') ?>">Volver</a>
        <a class="btn" href="<?= base_url('motorcycles/' . $moto['id'] . '/edit') ?>">Editar</a>
        <form method="post" action="<?= base_url('motorcycles/' . $moto['id'] . '/delete') ?>" class="inline-form">
            <?= csrf_field() ?>
            <button class="btn btn-danger" type="submit" data-confirm="¿Eliminar esta moto?">Eliminar</button>
        </form>
    </div>
</div>
<div class="grid grid-2 mb-2">
    <div class="card card-pad">
        <h2 class="mb-1">Datos</h2>
        <div class="kv">
            <span class="k">Cliente</span><span><a href="<?= base_url('customers/' . $moto['customer_id']) ?>"><?= esc($moto['customer_name'] ?? '—') ?></a></span>
            <span class="k">Patente</span><span><?= esc($moto['plate'] ?: '—') ?></span>
            <span class="k">Año</span><span><?= esc($moto['year'] ?: '—') ?></span>
            <span class="k">Chasis</span><span><?= esc($moto['chassis'] ?: '—') ?></span>
            <span class="k">Motor</span><span><?= esc($moto['engine'] ?: '—') ?></span>
            <span class="k">Notas</span><span><?= nl2br(esc($moto['notes'] ?: '—')) ?></span>
        </div>
    </div>
    <div class="card card-pad">
        <h2 class="mb-1">Órdenes</h2>
        <?php if (count($orders) === 0): ?><p class="muted">Sin órdenes.</p><?php endif; ?>
        <?php foreach ($orders as $order): ?>
            <div class="list-row">
                <div>
                    <a href="<?= base_url('orders/' . $order['id']) ?>"><strong><?= esc(uid('OT', $order['id'])) ?></strong></a>
                    <div class="muted text-sm"><?= esc(fecha($order['entry_date'])) ?> · <?= esc(estado_label($order['status'])) ?></div>
                </div>
                <span class="badge <?= esc(estado_class($order['status'])) ?>"><?= esc(estado_label($order['status'])) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>
