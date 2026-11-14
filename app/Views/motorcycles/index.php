<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1>Motos</h1><p class="subtitle">Catálogo de motos del taller</p></div>
    <div class="actions">
        <a class="btn btn-primary" href="<?= base_url('motorcycles/new') ?>">+ Nueva moto</a>
    </div>
</div>
<form method="get" action="<?= base_url('motorcycles') ?>" class="actions mb-2">
    <input type="text" name="q" value="<?= esc($term) ?>" placeholder="Buscar por UID, patente, marca o cliente" style="max-width:380px">
    <button class="btn" type="submit">Buscar</button>
    <?php if ($term !== ''): ?><a class="btn btn-ghost" href="<?= base_url('motorcycles') ?>">Limpiar</a><?php endif; ?>
</form>
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>UID</th><th>Moto</th><th>Patente</th><th>Cliente</th><th></th></tr></thead>
            <tbody>
                <?php if (count($motos) === 0): ?>
                    <tr><td colspan="5" class="empty">No hay motos todavía.</td></tr>
                <?php endif; ?>
                <?php foreach ($motos as $moto): ?>
                    <tr>
                        <td data-label="UID"><a href="<?= base_url('motorcycles/' . $moto['id']) ?>"><strong><?= esc(uid('MOT', $moto['id'])) ?></strong></a></td>
                        <td data-label="Moto"><?= esc(trim($moto['brand'] . ' ' . $moto['model'])) ?></td>
                        <td data-label="Patente"><?= esc($moto['plate'] ?: '—') ?></td>
                        <td data-label="Cliente"><?= esc($moto['customer_name'] ?? '—') ?></td>
                        <td class="right cell-actions" data-label="Acciones"><a class="btn btn-sm" href="<?= base_url('motorcycles/' . $moto['id'] . '/edit') ?>">Editar</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
