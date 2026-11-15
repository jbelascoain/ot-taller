<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <h1>Nueva orden</h1>
        <p class="subtitle">Elegí una moto existente o creá la moto y el cliente.</p>
    </div>
    <a class="btn" href="<?= base_url('orders') ?>">Volver</a>
</div>

<form method="post" action="<?= base_url('orders') ?>" class="card card-pad">
    <?= csrf_field() ?>

    <div class="radio-row">
        <label><input type="radio" name="mode" value="existing" checked data-toggle-target="#existing-moto"> Moto existente</label>
        <label><input type="radio" name="mode" value="new" data-toggle-target="#new-moto"> Moto nueva</label>
    </div>

    <div id="existing-moto">
        <div class="field">
            <label for="motorcycle_search">Moto</label>
            <div class="combo" data-combo data-endpoint="<?= base_url('motorcycles/search') ?>" data-combo-empty="No hay motos cargadas.">
                <input type="hidden" id="motorcycle_id" name="motorcycle_id" value="<?= esc((string) old('motorcycle_id')) ?>" data-combo-value>
                <input type="text" id="motorcycle_search" name="motorcycle_search" class="combo-input" value="<?= esc((string) old('motorcycle_search')) ?>" placeholder="Buscar por patente, marca, modelo o cliente…" autocomplete="off" data-combo-input>
                <div class="combo-list" data-combo-list hidden></div>
            </div>
        </div>
    </div>

    <div id="new-moto" class="hidden">
        <div class="form-grid">
            <div class="field">
                <label for="plate">Patente (opcional)</label>
                <input type="text" id="plate" name="plate">
            </div>
            <div class="field">
                <label for="brand">Marca</label>
                <input type="text" id="brand" name="brand">
            </div>
            <div class="field">
                <label for="model">Modelo</label>
                <input type="text" id="model" name="model">
            </div>
            <div class="field">
                <label for="year">Año</label>
                <input type="number" id="year" name="year">
            </div>
            <div class="field">
                <label for="chassis">Número de chasis (opcional)</label>
                <input type="text" id="chassis" name="chassis">
            </div>
            <div class="field">
                <label for="engine">Número de motor (opcional)</label>
                <input type="text" id="engine" name="engine">
            </div>
        </div>

        <div class="field">
            <label for="customer_search">Cliente dueño</label>
            <div class="combo" data-combo data-endpoint="<?= base_url('customers/search') ?>" data-combo-empty="No hay clientes cargados.">
                <input type="hidden" id="customer_id" name="customer_id" value="<?= esc((string) old('customer_id')) ?>" data-combo-value>
                <input type="text" id="customer_search" name="customer_search" class="combo-input" value="<?= esc((string) old('customer_search')) ?>" placeholder="Buscar por nombre, teléfono o email…" autocomplete="off" data-combo-input>
                <div class="combo-list" data-combo-list hidden></div>
            </div>
        </div>
        <label class="check mb-1">
            <input type="checkbox" name="new_customer" value="1" data-toggle-target="#new-customer-fields">
            El cliente no existe, crearlo
        </label>
        <div id="new-customer-fields" class="form-grid hidden">
            <div class="field">
                <label for="new_customer_name">Nombre y apellido</label>
                <input type="text" id="new_customer_name" name="new_customer_name">
            </div>
            <div class="field">
                <label for="new_customer_phone">Teléfono</label>
                <input type="tel" id="new_customer_phone" name="new_customer_phone">
            </div>
            <div class="field span-2">
                <label for="new_customer_email">Email (opcional)</label>
                <input type="email" id="new_customer_email" name="new_customer_email">
            </div>
        </div>
    </div>

    <hr>
    <div class="form-grid">
        <div class="field">
            <label for="entry_date">Fecha de ingreso</label>
            <input type="date" id="entry_date" name="entry_date" value="<?= esc($today) ?>" required>
        </div>
        <div class="field">
            <label for="km">Kilometraje</label>
            <input type="number" id="km" name="km">
        </div>
        <div class="field span-2">
            <label for="problem">Detalle del problema</label>
            <textarea id="problem" name="problem" required placeholder="Qué trae la moto, síntomas, pedido del cliente"></textarea>
        </div>
        <div class="field span-2">
            <label for="diagnosis">Diagnóstico (opcional)</label>
            <textarea id="diagnosis" name="diagnosis"></textarea>
        </div>
    </div>

    <div class="actions mt-2">
        <button class="btn btn-primary" type="submit">Crear orden</button>
        <a class="btn" href="<?= base_url('orders') ?>">Cancelar</a>
    </div>
</form>
<?= $this->endSection() ?>
