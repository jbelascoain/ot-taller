<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php $isEdit = $supplier !== null; ?>
<div class="page-head">
    <div><h1><?= $isEdit ? 'Editar proveedor' : 'Nuevo proveedor' ?></h1></div>
    <a class="btn" href="<?= base_url('suppliers') ?>">Volver</a>
</div>
<form method="post" action="<?= $isEdit ? base_url('suppliers/' . $supplier['id']) : base_url('suppliers') ?>" class="card card-pad">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="field span-2">
            <label for="name">Nombre</label>
            <input type="text" id="name" name="name" value="<?= esc(old('name', $supplier['name'] ?? '')) ?>" required>
        </div>
        <div class="field">
            <label for="contact">Contacto</label>
            <input type="text" id="contact" name="contact" value="<?= esc(old('contact', $supplier['contact'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="phone">Teléfono</label>
            <input type="tel" id="phone" name="phone" value="<?= esc(old('phone', $supplier['phone'] ?? '')) ?>">
        </div>
        <div class="field span-2">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= esc(old('email', $supplier['email'] ?? '')) ?>">
        </div>
        <div class="field span-2">
            <label for="notes">Notas</label>
            <textarea id="notes" name="notes"><?= esc(old('notes', $supplier['notes'] ?? '')) ?></textarea>
        </div>
    </div>
    <button class="btn btn-primary" type="submit">Guardar</button>
</form>
<?= $this->endSection() ?>
