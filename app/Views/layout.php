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
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">
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
        </nav>
        <div class="user-menu">
            <button class="theme-toggle" id="themeToggle" type="button" title="Cambiar tema" aria-label="Cambiar tema">
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
            </button>
            <span><?= esc($user['name'] ?? '') ?></span>
            <a href="<?= base_url('change-password') ?>">Contraseña</a>
            <a href="<?= base_url('logout') ?>">Salir</a>
        </div>
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
<script src="<?= base_url('assets/app.js') ?>"></script>
</body>
</html>
