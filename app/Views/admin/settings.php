<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1>Configuración</h1><p class="subtitle">Branding, formato, PDF y WhatsApp</p></div>
</div>
<form method="post" action="<?= base_url('admin/settings') ?>" enctype="multipart/form-data" class="grid grid-2">
    <?= csrf_field() ?>
    <div class="card">
        <div class="card-head"><span class="card-title">Taller y logo</span></div>
        <div class="card-body">
            <div class="field">
                <label for="workshop_name">Nombre del taller</label>
                <input type="text" id="workshop_name" name="workshop_name" value="<?= esc($settings['workshop_name'] ?? 'Taller') ?>" required>
            </div>
            <div class="field">
                <label for="workshop_contact">Contacto / dirección</label>
                <input type="text" id="workshop_contact" name="workshop_contact" value="<?= esc($settings['workshop_contact'] ?? '') ?>" placeholder="Teléfono, dirección, web">
            </div>
            <div class="field">
                <label for="logo">Logo (PNG, JPG o WEBP, máximo 2 MB)</label>
                <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp">
                <?php if (! empty($settings['logo_path'])): ?>
                    <div class="mt-1"><img src="<?= base_url('uploads/' . $settings['logo_path']) ?>" alt="Logo" style="max-height:60px"></div>
                    <label class="check mt-1"><input type="checkbox" name="remove_logo" value="1"> Quitar logo</label>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><span class="card-title">Formato</span></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="field">
                    <label for="currency_symbol">Símbolo de moneda</label>
                    <input type="text" id="currency_symbol" name="currency_symbol" value="<?= esc($settings['currency_symbol'] ?? '$') ?>">
                </div>
                <div class="field">
                    <label for="locale">Locale</label>
                    <input type="text" id="locale" name="locale" value="<?= esc($settings['locale'] ?? 'es-AR') ?>">
                </div>
            </div>
            <div class="field">
                <label for="pdf_footer">Texto al pie del PDF</label>
                <input type="text" id="pdf_footer" name="pdf_footer" value="<?= esc($settings['pdf_footer'] ?? '') ?>">
                <span class="help">Podés usar <code>{workshop}</code> para el nombre del taller.</span>
            </div>
            <div class="field">
                <label for="whatsapp_template">Plantilla de WhatsApp</label>
                <textarea id="whatsapp_template" name="whatsapp_template"><?= esc($settings['whatsapp_template'] ?? '') ?></textarea>
                <span class="help">Variables: <code>{cliente}</code>, <code>{orden}</code>, <code>{moto}</code>, <code>{total}</code>.</span>
            </div>
        </div>
    </div>

    <div class="card card-pad" style="grid-column:1/-1">
        <button class="btn btn-primary" type="submit">Guardar configuración</button>
    </div>
</form>
<?= $this->endSection() ?>
