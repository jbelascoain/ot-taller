<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1>Usuarios</h1><p class="subtitle">Admin y personal del taller</p></div>
    <div class="actions">
        <a class="btn" href="<?= base_url('admin/settings') ?>">Configuración</a>
    </div>
</div>

<div class="card mb-2">
    <div class="card-head"><span class="card-title">Usuarios</span></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Contraseña</th><th></th></tr></thead>
            <tbody>
                <?php $me = current_user(); ?>
                <?php foreach ($users as $user): ?>
                    <?php $isSelf = (int) $me['id'] === (int) $user['id']; ?>
                    <tr>
                        <td>
                            <strong><?= esc($user['name']) ?></strong>
                            <?php if ($isSelf): ?><span class="badge ingreso">Vos</span><?php endif; ?>
                            <?php if ((int) $user['must_change_password'] === 1): ?>
                                <div class="muted text-xs">Debe cambiar contraseña</div>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($user['email']) ?></td>
                        <td><?= esc($user['role']) ?></td>
                        <td><?= (int) $user['active'] === 1 ? '<span class="badge aprobado">Activo</span>' : '<span class="badge inactive">Inactivo</span>' ?></td>
                        <td>
                            <form method="post" action="<?= base_url('admin/users/' . $user['id'] . '/password') ?>" class="flex gap">
                                <?= csrf_field() ?>
                                <input type="password" name="password" placeholder="Nueva contraseña" minlength="8" style="max-width:180px">
                                <button class="btn btn-sm" type="submit">Cambiar</button>
                            </form>
                        </td>
                        <td class="right">
                            <div class="flex gap wrap" style="justify-content:flex-end">
                                <form method="post" action="<?= base_url('admin/users/' . $user['id'] . '/toggle') ?>" class="inline-form">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm" type="submit" <?= $isSelf ? 'disabled title="No podés desactivarte"' : '' ?>><?= (int) $user['active'] === 1 ? 'Desactivar' : 'Activar' ?></button>
                                </form>
                                <?php if (! $isSelf): ?>
                                    <form method="post" action="<?= base_url('admin/users/' . $user['id'] . '/delete') ?>" class="inline-form">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-danger" type="submit" data-confirm="¿Eliminar definitivamente a <?= esc($user['name']) ?>?">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
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
        <p class="muted text-sm mb-2">El usuario deberá cambiar la contraseña obligatoriamente en su próximo login.</p>
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
                    <label for="password">Contraseña temporal (mínimo 8)</label>
                    <input type="password" id="password" name="password" minlength="8" required>
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
