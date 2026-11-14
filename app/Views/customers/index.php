<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1>Clientes</h1><p class="subtitle">Dueños de las motos del taller</p></div>
    <a class="btn btn-primary" href="<?= base_url('customers/new') ?>">+ Nuevo cliente</a>
</div>
<form method="get" action="<?= base_url('customers') ?>" class="actions mb-2">
    <input type="text" name="q" value="<?= esc($term) ?>" placeholder="Buscar por nombre, teléfono o email" style="max-width:360px">
    <button class="btn" type="submit">Buscar</button>
    <?php if ($term !== ''): ?><a class="btn btn-ghost" href="<?= base_url('customers') ?>">Limpiar</a><?php endif; ?>
</form>
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Cliente</th><th>Teléfono</th><th>Email</th><th></th></tr></thead>
            <tbody>
                <?php if (count($customers) === 0): ?>
                    <tr><td colspan="4" class="empty">No hay clientes todavía.</td></tr>
                <?php endif; ?>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td data-label="Cliente"><a href="<?= base_url('customers/' . $customer['id']) ?>"><strong><?= esc($customer['name']) ?></strong></a></td>
                        <td data-label="Teléfono"><?= esc($customer['phone'] ?: '—') ?></td>
                        <td class="cell-block" data-label="Email"><?= esc($customer['email'] ?: '—') ?></td>
                        <td class="right cell-actions" data-label="Acciones"><a class="btn btn-sm" href="<?= base_url('customers/' . $customer['id'] . '/edit') ?>">Editar</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
