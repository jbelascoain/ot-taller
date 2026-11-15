<?php
$user = current_user();
$logo = setting('logo_path');
$path = trim(service('request')->getUri()->getPath(), '/');
$is = static fn (string $prefix): bool => $path === $prefix || str_starts_with($path, $prefix . '/');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title><?= esc($title ?? 'OT Taller') ?> · <?= esc(setting('workshop_name', 'Taller')) ?></title>
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
<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="<?= base_url('/') ?>">
            <?php if ($logo): ?>
                <img src="<?= base_url('uploads/' . $logo) ?>" alt="<?= esc(setting('workshop_name', 'Taller')) ?>">
            <?php else: ?>
                <span class="brand-mark">OT</span>
                <span><?= esc(setting('workshop_name', 'Taller')) ?></span>
            <?php endif; ?>
        </a>
        <button class="nav-toggle" id="navToggle" type="button" aria-label="Menú">☰</button>
        <nav class="nav" id="mainNav">
            <a href="<?= base_url('/') ?>" class="<?= $path === '' ? 'active' : '' ?>">Tablero</a>
            <a href="<?= base_url('orders') ?>" class="<?= $is('orders') ? 'active' : '' ?>">Órdenes</a>
            <a href="<?= base_url('customers') ?>" class="<?= $is('customers') ? 'active' : '' ?>">Clientes</a>
            <a href="<?= base_url('motorcycles') ?>" class="<?= $is('motorcycles') ? 'active' : '' ?>">Motos</a>
            <a href="<?= base_url('parts') ?>" class="<?= $is('parts') ? 'active' : '' ?>">Repuestos</a>
            <a href="<?= base_url('suppliers') ?>" class="<?= $is('suppliers') ? 'active' : '' ?>">Proveedores</a>
            <?php if (is_admin()): ?>
                <a href="<?= base_url('admin/settings') ?>" class="<?= $is('admin/settings') ? 'active' : '' ?>">Configuración</a>
            <?php endif; ?>
        <div class="user-menu">
            <span><?= esc($user['name'] ?? '') ?></span>
            <button class="theme-toggle" id="themeToggle" type="button" title="Cambiar tema" aria-label="Cambiar tema">
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
            </button>
            <a class="icon-link" href="<?= base_url('change-password') ?>" title="Cambiar contraseña" aria-label="Cambiar contraseña">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3L22 7l-3-3"/></svg>
            </a>
            <a class="icon-link" href="<?= base_url('logout') ?>" title="Salir" aria-label="Salir">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </a>
        </div>
        </nav>
    </div>
</header>
<main class="container">
    <?php if ($message = session()->getFlashdata('success')): ?>
        <div class="alert success"><?= esc($message) ?></div>
    <?php endif; ?>
    <?php if ($message = session()->getFlashdata('error')): ?>
        <div class="alert error"><?= esc($message) ?></div>
    <?php endif; ?>
    <?= $this->renderSection('content') ?>
</main>
<script src="<?= asset_url('assets/app.js') ?>"></script>
</body>
</html>
