<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1>Usuarios</h1><p class="subtitle">Admin y personal del taller</p></div>
</div>

<div class="card mb-2">
    <div class="card-head"><span class="card-title">Usuarios</span></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Contraseña</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?= esc($user['name']) ?></strong></td>
                        <td><?= esc($user['email']) ?></td>
                        <td><?= esc($user['role']) ?></td>
                        <td><?= (int) $user['active'] === 1 ? '<span class="badge aprobado">Activo</span>' : '<span class="badge inactive">Inactivo</span>' ?></td>
                        <td>
                            <form method="post" action="<?= base_url('admin/users/' . $user['id'] . '/password') ?>" class="flex gap">
                                <?= csrf_field() ?>
                                <input type="password" name="password" placeholder="Nueva contraseña" style="max-width:180px">
                                <button class="btn btn-sm" type="submit">Cambiar</button>
                            </form>
                        </td>
                        <td class="right">
                            <form method="post" action="<?= base_url('admin/users/' . $user['id'] . '/toggle') ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm" type="submit"><?= (int) $user['active'] === 1 ? 'Desactivar' : 'Activar' ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-head"><span class="card-title">Nuevo usuario</span></div>
    <div class="card-body">
        <form method="post" action="<?= base_url('admin/users') ?>">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="field">
                    <label for="name">Nombre</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="field">
                    <label for="password">Contraseña (mínimo 8)</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="field">
                    <label for="role">Rol</label>
                    <select id="role" name="role">
                        <option value="STAFF">Staff</option>
                        <option value="ADMIN">Admin</option>
                    </select>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Crear usuario</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
