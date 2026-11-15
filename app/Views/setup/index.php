<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Primera instalación · OT Taller</title>
    <script>
        try { if (localStorage.getItem('ot-theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark'); } catch (e) {}
    </script>
    <?php
    $faviconLogo = setting('logo_path');
    $faviconFile = $faviconLogo !== '' ? FCPATH . 'uploads/' . $faviconLogo : '';
    $faviconVersion = ($faviconFile !== '' && is_file($faviconFile)) ? filemtime($faviconFile) : '';
    ?>
    <?php if ($faviconVersion !== ''): ?>
        <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('favicon-32.png?v=' . $faviconVersion) ?>">
        <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('favicon-16.png?v=' . $faviconVersion) ?>">
        <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('apple-touch-icon.png?v=' . $faviconVersion) ?>">
    <?php else: ?>
        <link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= asset_url('assets/app.css') ?>">
</head>
<body>
<div class="center-page">
    <div class="auth-card" style="max-width:560px">
        <div class="auth-brand"><span class="brand-mark">OT</span><span>OT Taller</span></div>
        <div class="card">
            <div class="card-body">
                <h1 class="mb-1">Primera instalación</h1>
                <p class="muted text-sm mb-2">Creá el usuario administrador y el nombre del taller. Después todo se configura desde el panel.</p>
                <?php if ($message = session()->getFlashdata('error')): ?>
                    <div class="alert error"><?= esc($message) ?></div>
                <?php endif; ?>
                <form method="post" action="<?= base_url('setup') ?>">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label for="workshop">Nombre del taller</label>
                        <input type="text" id="workshop" name="workshop" value="<?= esc(old('workshop', 'Taller')) ?>" required>
                    </div>
                    <div class="form-grid">
                        <div class="field">
                            <label for="name">Nombre del administrador</label>
                            <input type="text" id="name" name="name" value="<?= esc(old('name')) ?>" required>
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= esc(old('email')) ?>" required>
                        </div>
                        <div class="field span-2">
                            <label for="password">Contraseña (mínimo 8 caracteres)</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <?php if (! empty($tokenRequired)): ?>
                            <div class="field span-2">
                                <label for="setup_token">Token de instalación</label>
                                <input type="text" id="setup_token" name="setup_token" required>
                                <span class="help">Es el valor de <code>SETUP_TOKEN</code> del archivo de entorno.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <label class="check mb-2">
                        <input type="checkbox" name="demo" value="1" checked>
                        Cargar datos de demo (clientes, motos, repuestos y órdenes ficticias)
                    </label>
                    <button class="btn btn-primary w-full" type="submit">Crear instalación</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
