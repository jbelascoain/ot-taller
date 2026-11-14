<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php $isEdit = $customer !== null; ?>
<div class="page-head">
    <div><h1><?= $isEdit ? 'Editar cliente' : 'Nuevo cliente' ?></h1></div>
    <a class="btn" href="<?= base_url('customers') ?>">Volver</a>
</div>
<form method="post" action="<?= $isEdit ? base_url('customers/' . $customer['id']) : base_url('customers') ?>" class="card card-pad">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="field span-2">
            <label for="name">Nombre y apellido</label>
            <input type="text" id="name" name="name" value="<?= esc(old('name', $customer['name'] ?? '')) ?>" required>
        </div>
        <div class="field">
            <label for="phone">Teléfono</label>
            <input type="tel" id="phone" name="phone" value="<?= esc(old('phone', $customer['phone'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= esc(old('email', $customer['email'] ?? '')) ?>">
        </div>
        <div class="field span-2">
            <label for="notes">Notas</label>
            <textarea id="notes" name="notes"><?= esc(old('notes', $customer['notes'] ?? '')) ?></textarea>
        </div>
    </div>
    <button class="btn btn-primary" type="submit">Guardar</button>
</form>
<?= $this->endSection() ?>
