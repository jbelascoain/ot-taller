<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1>Configuración</h1><p class="subtitle">Branding, formato, PDF, WhatsApp y email</p></div>
    <a class="btn" href="<?= base_url('admin/users') ?>">Administrar usuarios</a>
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
                    <div class="mt-1"><img class="logo-preview" src="<?= base_url('uploads/' . $settings['logo_path']) ?>" alt="Logo" style="max-height:100px"></div>
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
        </div>
    </div>

    <div class="card" style="grid-column:1/-1">
        <div class="card-head"><span class="card-title">WhatsApp</span></div>
        <div class="card-body">
            <p class="muted text-sm mb-2">
                Mensajes que se abren con WhatsApp desde la orden. Variables:
                <code>{cliente}</code>, <code>{orden}</code>, <code>{moto}</code>, <code>{total}</code> y <code>{taller}</code>.
            </p>
            <div class="form-grid">
                <div class="field span-2">
                    <label for="whatsapp_template">Mensaje general</label>
                    <textarea id="whatsapp_template" name="whatsapp_template"><?= esc($settings['whatsapp_template'] ?? '') ?></textarea>
                </div>
                <div class="field span-2">
                    <label for="whatsapp_quote_template">Presupuesto listo para aprobar</label>
                    <textarea id="whatsapp_quote_template" name="whatsapp_quote_template"><?= esc($settings['whatsapp_quote_template'] ?? '') ?></textarea>
                </div>
                <div class="field span-2">
                    <label for="whatsapp_ready_template">Moto lista para retirar</label>
                    <textarea id="whatsapp_ready_template" name="whatsapp_ready_template"><?= esc($settings['whatsapp_ready_template'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="grid-column:1/-1">
        <div class="card-head"><span class="card-title">Email / SMTP</span></div>
        <div class="card-body">
            <p class="muted text-sm mb-2">
                Para Gmail: host <code>smtp.gmail.com</code>, puerto <code>587</code>, cifrado <code>TLS</code> y una
                <strong>App Password</strong> de Google (no la contraseña normal de la cuenta).
            </p>
            <div class="form-grid">
                <div class="field">
                    <label for="smtp_host">Host SMTP</label>
                    <input type="text" id="smtp_host" name="smtp_host" value="<?= esc($settings['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
                </div>
                <div class="field">
                    <label for="smtp_port">Puerto</label>
                    <input type="number" id="smtp_port" name="smtp_port" value="<?= esc($settings['smtp_port'] ?? '587') ?>" placeholder="587">
                </div>
                <div class="field">
                    <label for="smtp_crypto">Cifrado</label>
                    <select id="smtp_crypto" name="smtp_crypto">
                        <?php foreach (['tls' => 'TLS', 'ssl' => 'SSL', '' => 'Sin cifrado'] as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= ($settings['smtp_crypto'] ?? 'tls') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="smtp_user">Usuario</label>
                    <input type="text" id="smtp_user" name="smtp_user" value="<?= esc($settings['smtp_user'] ?? '') ?>" placeholder="tucuenta@gmail.com">
                </div>
                <div class="field">
                    <label for="smtp_pass">Contraseña / App Password</label>
                    <input type="password" id="smtp_pass" name="smtp_pass" value="" autocomplete="new-password" placeholder="Dejar en blanco para mantener la actual">
                    <span class="help">
                        <?= ! empty($settings['smtp_pass']) ? 'Hay una contraseña guardada.' : 'Gmail requiere una App Password de 16 caracteres.' ?>
                    </span>
                </div>
                <div class="field">
                    <label for="smtp_from_email">Email remitente</label>
                    <input type="email" id="smtp_from_email" name="smtp_from_email" value="<?= esc($settings['smtp_from_email'] ?? '') ?>" placeholder="tucuenta@gmail.com">
                </div>
                <div class="field">
                    <label for="smtp_from_name">Nombre remitente</label>
                    <input type="text" id="smtp_from_name" name="smtp_from_name" value="<?= esc($settings['smtp_from_name'] ?? '') ?>" placeholder="<?= esc($settings['workshop_name'] ?? 'Taller') ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card card-pad" style="grid-column:1/-1">
        <button class="btn btn-primary" type="submit">Guardar configuración</button>
    </div>
</form>

<div class="card mt-2">
    <div class="card-head"><span class="card-title">Probar SMTP</span></div>
    <div class="card-body">
        <form method="post" action="<?= base_url('admin/settings/test-email') ?>" class="flex gap wrap center">
            <?= csrf_field() ?>
            <input type="email" name="test_email" placeholder="Email de destino" required style="max-width:320px">
            <button class="btn" type="submit">Enviar prueba</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
