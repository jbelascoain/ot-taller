<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1><?= esc($supplier['name']) ?></h1><p class="subtitle">Proveedor</p></div>
    <div class="actions">
        <a class="btn" href="<?= base_url('suppliers') ?>">Volver</a>
        <a class="btn" href="<?= base_url('suppliers/' . $supplier['id'] . '/edit') ?>">Editar</a>
        <form method="post" action="<?= base_url('suppliers/' . $supplier['id'] . '/delete') ?>" class="inline-form">
            <?= csrf_field() ?>
            <button class="btn btn-danger" type="submit" data-confirm="¿Eliminar este proveedor?">Eliminar</button>
        </form>
    </div>
</div>
<div class="grid grid-2">
    <div class="card card-pad">
        <h2 class="mb-1">Datos</h2>
        <div class="kv">
            <span class="k">Contacto</span><span><?= esc($supplier['contact'] ?: '—') ?></span>
            <span class="k">Teléfono</span><span><?= esc($supplier['phone'] ?: '—') ?></span>
            <span class="k">Email</span><span><?= esc($supplier['email'] ?: '—') ?></span>
            <span class="k">Notas</span><span><?= nl2br(esc($supplier['notes'] ?: '—')) ?></span>
        </div>
    </div>
    <div class="card card-pad">
        <h2 class="mb-1">Repuestos</h2>
        <?php if (count($parts) === 0): ?><p class="muted">Sin repuestos.</p><?php endif; ?>
        <?php foreach ($parts as $part): ?>
            <div class="list-row">
                <div><strong><?= esc($part['description']) ?></strong><div class="muted text-sm"><?= esc(uid('REP', $part['id'])) ?> · Venta <?= esc(money($part['sale_price'])) ?></div></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>
