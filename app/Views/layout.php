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
            <?php endif; ?>
            <span><?= esc(setting('workshop_name', 'Taller')) ?></span>
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
                <a href="<?= base_url('admin/settings') ?>" class="<?= $is('admin') ? 'active' : '' ?>">Admin</a>
            <?php endif; ?>
        </nav>
        <div class="user-menu">
            <span><?= esc($user['name'] ?? '') ?></span>
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
