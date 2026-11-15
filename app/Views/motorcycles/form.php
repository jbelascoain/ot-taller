<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php $isEdit = $moto !== null; ?>
<?php
$selectedCustomerId   = old('customer_id', $moto['customer_id'] ?? '');
$selectedCustomerName = old('customer_search', $moto['customer_name'] ?? '');
?>
<div class="page-head">
    <div><h1><?= $isEdit ? 'Editar moto' : 'Nueva moto' ?></h1><p class="subtitle">La patente es opcional; el UID identifica la moto.</p></div>
    <a class="btn" href="<?= base_url('motorcycles') ?>">Volver</a>
</div>
<form method="post" action="<?= $isEdit ? base_url('motorcycles/' . $moto['id']) : base_url('motorcycles') ?>" class="card card-pad">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="field span-2">
            <label for="customer_search">Cliente dueño</label>
            <div class="combo" data-combo data-endpoint="<?= base_url('customers/search') ?>" data-combo-empty="No hay clientes cargados.">
                <input type="hidden" id="customer_id" name="customer_id" value="<?= esc((string) $selectedCustomerId) ?>" data-combo-value>
                <input type="text" id="customer_search" name="customer_search" class="combo-input" value="<?= esc((string) $selectedCustomerName) ?>" placeholder="Buscar por nombre, teléfono o email…" autocomplete="off" data-combo-input>
                <div class="combo-list" data-combo-list hidden></div>
            </div>
        </div>
        <div class="field">
            <label for="plate">Patente (opcional)</label>
            <input type="text" id="plate" name="plate" value="<?= esc(old('plate', $moto['plate'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="year">Año</label>
            <input type="number" id="year" name="year" value="<?= esc(old('year', $moto['year'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="brand">Marca</label>
            <input type="text" id="brand" name="brand" value="<?= esc(old('brand', $moto['brand'] ?? '')) ?>" required>
        </div>
        <div class="field">
            <label for="model">Modelo</label>
            <input type="text" id="model" name="model" value="<?= esc(old('model', $moto['model'] ?? '')) ?>" required>
        </div>
        <div class="field">
            <label for="chassis">Número de chasis (opcional)</label>
            <input type="text" id="chassis" name="chassis" value="<?= esc(old('chassis', $moto['chassis'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="engine">Número de motor (opcional)</label>
            <input type="text" id="engine" name="engine" value="<?= esc(old('engine', $moto['engine'] ?? '')) ?>">
        </div>
        <div class="field span-2">
            <label for="notes">Notas</label>
            <textarea id="notes" name="notes"><?= esc(old('notes', $moto['notes'] ?? '')) ?></textarea>
        </div>
    </div>
    <button class="btn btn-primary" type="submit">Guardar</button>
</form>
<?= $this->endSection() ?>
