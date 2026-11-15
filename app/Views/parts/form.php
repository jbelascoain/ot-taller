<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php $isEdit = $part !== null; ?>
<div class="page-head">
    <div><h1><?= $isEdit ? 'Editar repuesto' : 'Nuevo repuesto' ?></h1><p class="subtitle">Precio de compra, precio de venta y stock.</p></div>
    <a class="btn" href="<?= base_url('parts') ?>">Volver</a>
</div>
<form method="post" action="<?= $isEdit ? base_url('parts/' . $part['id']) : base_url('parts') ?>" class="card card-pad">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="field span-2">
            <label for="description">Descripción</label>
            <input type="text" id="description" name="description" value="<?= esc(old('description', $part['description'] ?? '')) ?>" required>
        </div>
        <div class="field">
            <label for="sku">Modelo</label>
            <input type="text" id="sku" name="sku" value="<?= esc(old('sku', $part['sku'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="brand">Marca</label>
            <input type="text" id="brand" name="brand" value="<?= esc(old('brand', $part['brand'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="purchase_price">Precio de compra</label>
            <input type="number" step="0.01" id="purchase_price" name="purchase_price" value="<?= esc(old('purchase_price', $part['purchase_price'] ?? '0')) ?>">
        </div>
        <div class="field">
            <label for="sale_price">Precio de venta</label>
            <input type="number" step="0.01" id="sale_price" name="sale_price" value="<?= esc(old('sale_price', $part['sale_price'] ?? '0')) ?>">
        </div>
        <div class="field">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" value="<?= esc(old('stock', $part['stock'] ?? '0')) ?>">
        </div>
        <div class="field">
            <label for="min_stock">Stock mínimo</label>
            <input type="number" id="min_stock" name="min_stock" value="<?= esc(old('min_stock', $part['min_stock'] ?? '0')) ?>">
        </div>
        <div class="field">
            <label for="supplier_id">Proveedor</label>
            <select id="supplier_id" name="supplier_id">
                <option value="">Sin proveedor</option>
                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= (int) $supplier['id'] ?>" <?= (int) old('supplier_id', $part['supplier_id'] ?? 0) === (int) $supplier['id'] ? 'selected' : '' ?>><?= esc($supplier['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field" style="justify-content:flex-end">
            <label class="check">
                <input type="checkbox" name="active" value="1" <?= (int) old('active', $part['active'] ?? 1) === 1 ? 'checked' : '' ?>>
                Activo
            </label>
        </div>
        <div class="field span-2">
            <label for="notes">Notas</label>
            <textarea id="notes" name="notes"><?= esc(old('notes', $part['notes'] ?? '')) ?></textarea>
        </div>
    </div>
    <button class="btn btn-primary" type="submit">Guardar</button>
</form>
<?= $this->endSection() ?>
