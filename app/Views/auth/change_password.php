<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div style="max-width:520px">
    <div class="page-head">
        <div>
            <h1>Cambiar contraseña</h1>
            <p class="subtitle">Elegí una contraseña nueva de al menos 8 caracteres.</p>
        </div>
    </div>

    <?php if (! empty($forced)): ?>
        <div class="alert error">Por seguridad, tenés que cambiar la contraseña temporal antes de usar la app.</div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('change-password') ?>" class="card card-pad">
        <?= csrf_field() ?>
        <div class="field">
            <label for="current_password">Contraseña actual</label>
            <input type="password" id="current_password" name="current_password" required autofocus>
        </div>
        <div class="field">
            <label for="new_password">Contraseña nueva</label>
            <input type="password" id="new_password" name="new_password" minlength="8" required>
        </div>
        <div class="field">
            <label for="confirm_password">Repetir contraseña nueva</label>
            <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
        </div>
        <div class="actions mt-2">
            <button class="btn btn-primary" type="submit">Guardar contraseña</button>
            <a class="btn" href="<?= base_url('logout') ?>">Salir</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
