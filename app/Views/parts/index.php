<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1>Repuestos</h1><p class="subtitle">Catálogo y stock del taller</p></div>
    <a class="btn btn-primary" href="<?= base_url('parts/new') ?>">+ Nuevo repuesto</a>
</div>
<form method="get" action="<?= base_url('parts') ?>" class="actions mb-2">
    <input type="text" name="q" value="<?= esc($term) ?>" placeholder="Buscar por descripción, SKU, marca o proveedor" style="max-width:400px">
    <button class="btn" type="submit">Buscar</button>
    <?php if ($term !== ''): ?><a class="btn btn-ghost" href="<?= base_url('parts') ?>">Limpiar</a><?php endif; ?>
</form>
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>UID</th><th>Descripción</th><th>Marca / SKU</th><th class="num">Compra</th><th class="num">Venta</th><th>Stock</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                <?php if (count($parts) === 0): ?>
                    <tr><td colspan="8" class="empty">No hay repuestos todavía.</td></tr>
                <?php endif; ?>
                <?php foreach ($parts as $part): ?>
                    <?php $low = (int) $part['stock'] <= (int) $part['min_stock']; ?>
                    <tr>
                        <td><?= esc(uid('REP', $part['id'])) ?></td>
                        <td><strong><?= esc($part['description']) ?></strong><div class="muted text-xs"><?= esc($part['supplier_name'] ?: 'Sin proveedor') ?></div></td>
                        <td><?= esc(trim(($part['brand'] ?? '') . ' ' . ($part['sku'] ?? '')) ?: '—') ?></td>
                        <td class="num"><?= esc(money($part['purchase_price'])) ?></td>
                        <td class="num"><?= esc(money($part['sale_price'])) ?></td>
                        <td><span class="badge <?= $low ? 'rechazado' : 'aprobado' ?>"><?= (int) $part['stock'] ?></span></td>
                        <td><?= (int) $part['active'] === 1 ? '<span class="badge aprobado">Activo</span>' : '<span class="badge inactive">Inactivo</span>' ?></td>
                        <td class="right">
                            <div class="flex gap wrap" style="justify-content:flex-end">
                                <a class="btn btn-sm" href="<?= base_url('parts/' . $part['id'] . '/edit') ?>">Editar</a>
                                <form method="post" action="<?= base_url('parts/' . $part['id'] . '/toggle') ?>" class="inline-form">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm" type="submit"><?= (int) $part['active'] === 1 ? 'Desactivar' : 'Activar' ?></button>
                                </form>
                                <form method="post" action="<?= base_url('parts/' . $part['id'] . '/delete') ?>" class="inline-form">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-danger" type="submit" data-confirm="¿Eliminar este repuesto?">×</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
