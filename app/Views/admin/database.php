<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <h1>Base de datos</h1>
        <p class="subtitle">Respaldo y restauración de la base completa</p>
    </div>
    <a class="btn" href="<?= base_url('admin/settings') ?>">Volver a Configuración</a>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-head"><span class="card-title">Exportar</span></div>
        <div class="card-body">
            <p class="muted text-sm mb-2">
                Descarga un archivo <code>.sql</code> con la estructura y todos los datos:
                órdenes, clientes, motos, repuestos, usuarios y configuración.
            </p>
            <p class="text-xs muted mb-2">
                El respaldo incluye hashes de contraseñas y configuración SMTP. Guardalo en un lugar seguro.
            </p>
            <a class="btn btn-primary" href="<?= base_url('admin/database/export') ?>">Descargar respaldo .sql</a>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><span class="card-title">Importar</span></div>
        <div class="card-body">
            <div class="alert error">
                Restaurar un respaldo <strong>reemplaza toda la base actual</strong>. Descargá un respaldo antes de continuar.
            </div>
            <form method="post" action="<?= base_url('admin/database/import') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="sql_file">Archivo .sql (máximo 20 MB)</label>
                    <input type="file" id="sql_file" name="sql_file" accept=".sql,application/sql,text/plain" required>
                </div>
                <label class="check mb-2">
                    <input type="checkbox" name="confirm_import" value="1" required>
                    Confirmo que este respaldo reemplazará la base actual.
                </label>
                <button class="btn btn-danger" type="submit" data-confirm="¿Restaurar este respaldo? Se reemplaza toda la base actual.">Importar respaldo</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
