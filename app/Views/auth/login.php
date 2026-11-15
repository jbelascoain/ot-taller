<?php $logo = setting('logo_path'); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar · <?= esc(setting('workshop_name', 'Taller')) ?></title>
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
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">
</head>
<body>
<div class="center-page">
    <div class="auth-card">
        <div class="auth-brand">
            <?php if ($logo): ?>
                <img src="<?= base_url('uploads/' . $logo) ?>" alt="<?= esc(setting('workshop_name', 'Taller')) ?>">
            <?php else: ?>
                <span class="brand-mark">OT</span>
                <span><?= esc(setting('workshop_name', 'Taller')) ?></span>
            <?php endif; ?>
        </div>
        <div class="card">
            <div class="card-body">
                <h1 class="mb-1">Ingresar</h1>
                <p class="muted text-sm mb-2">Acceso para el personal del taller.</p>
                <?php if ($message = session()->getFlashdata('error')): ?>
                    <div class="alert error"><?= esc($message) ?></div>
                <?php endif; ?>
                <?php if ($message = session()->getFlashdata('success')): ?>
                    <div class="alert success"><?= esc($message) ?></div>
                <?php endif; ?>
                <form method="post" action="<?= base_url('login') ?>">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= esc(old('email')) ?>" required autofocus>
                    </div>
                    <div class="field">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button class="btn btn-primary w-full" type="submit">Entrar</button>
                </form>
                <?php if (! empty($needsSetup)): ?>
                    <p class="text-sm mt-2"><a href="<?= base_url('setup') ?>">Primera instalación</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
