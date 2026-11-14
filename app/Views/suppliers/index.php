<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1>Proveedores</h1><p class="subtitle">Quién abastece cada repuesto</p></div>
    <a class="btn btn-primary" href="<?= base_url('suppliers/new') ?>">+ Nuevo proveedor</a>
</div>
<form method="get" action="<?= base_url('suppliers') ?>" class="actions mb-2">
    <input type="text" name="q" value="<?= esc($term) ?>" placeholder="Buscar por nombre, contacto o teléfono" style="max-width:360px">
    <button class="btn" type="submit">Buscar</button>
    <?php if ($term !== ''): ?><a class="btn btn-ghost" href="<?= base_url('suppliers') ?>">Limpiar</a><?php endif; ?>
</form>
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Proveedor</th><th>Contacto</th><th>Teléfono</th><th>Email</th><th></th></tr></thead>
            <tbody>
                <?php if (count($suppliers) === 0): ?>
                    <tr><td colspan="5" class="empty">No hay proveedores todavía.</td></tr>
                <?php endif; ?>
                <?php foreach ($suppliers as $supplier): ?>
                    <tr>
                        <td><a href="<?= base_url('suppliers/' . $supplier['id']) ?>"><strong><?= esc($supplier['name']) ?></strong></a></td>
                        <td><?= esc($supplier['contact'] ?: '—') ?></td>
                        <td><?= esc($supplier['phone'] ?: '—') ?></td>
                        <td><?= esc($supplier['email'] ?: '—') ?></td>
                        <td class="right"><a class="btn btn-sm" href="<?= base_url('suppliers/' . $supplier['id'] . '/edit') ?>">Editar</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
