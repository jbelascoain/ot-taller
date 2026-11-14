# Deploy en shared hosting (sin Composer)

Requisitos del hosting:

- PHP 8.3 con `intl`, `gd`, `mbstring`, `pdo_mysql`, `mysqli`, `zip`.
- MySQL 8 o MariaDB 10.x.
- Apache con `mod_rewrite` y `.htaccess`.
- Permisos de escritura en `writable/` y `public/uploads/`.

No hace falta Node, Composer ni SSH.

## 1. Generar el release con vendor

En tu máquina (con Docker):

```bash
bash bin/build-release.sh
```

Genera `ot-taller-release.zip` con el código y `vendor/` incluido.

También podés hacerlo a mano si tenés PHP/Composer:

```bash
composer install --no-dev --optimize-autoloader
zip -r ot-taller-release.zip . -x ".git/*" ".env" "writable/cache/*" "writable/logs/*"
```

## 2. Crear la base

1. En cPanel creá una base MySQL y un usuario con permisos.
2. Entrá a phpMyAdmin, seleccioná la base e importá `database/schema.sql`.

## 3. Subir archivos

Opción recomendada: **docroot a `public/`**.

- Subí la carpeta de la app a `/home/USUARIO/ot-taller`.
- Configurá el dominio/addon domain para que apunte a
  `/home/USUARIO/ot-taller/public`.

Si el hosting no permite cambiar el docroot, usá `public_html`:

1. Subí la app a `/home/USUARIO/ot-taller`.
2. Copiá el contenido de `public/` a `public_html/`.
3. Editá `public_html/index.php` y reemplazá las rutas por absolutas:

```php
$paths->appDirectory      = '/home/USUARIO/ot-taller/app';
$paths->writableDirectory = '/home/USUARIO/ot-taller/writable';
$paths->systemDirectory   = '/home/USUARIO/ot-taller/vendor/codeigniter4/framework/system';
```

4. Asegurate de que `public_html/.htaccess` sea el de `public/.htaccess`.

## 4. Configurar `.env`

Creá `/home/USUARIO/ot-taller/.env`:

```ini
CI_ENVIRONMENT = production
APP_URL = https://tu-dominio.com/

DB_HOST = localhost
DB_PORT = 3306
DB_NAME = usuario_ot_taller
DB_USER = usuario_ot_taller
DB_PASS = tu_password

SETUP_TOKEN = un-token-largo-y-unico
```

## 5. Permisos

```bash
chmod -R 775 writable public/uploads
```

En cPanel, desde el administrador de archivos, asegurate de que esas carpetas
sean escribibles por el usuario de PHP.

## 6. Instalar

Abrí `https://tu-dominio.com/setup`:

- Cargá el token de `SETUP_TOKEN`.
- Creá el administrador y el nombre del taller.
- Opcional: cargar datos de demo.

Después del setup, `/setup` queda deshabilitado automáticamente cuando ya existe
un usuario. Podés vaciar `SETUP_TOKEN` en `.env`.

## 7. Backups

- Base: exportación desde phpMyAdmin o `mysqldump`.
- Archivos: `public/uploads/` (logo) y `.env`.

## Notas

- El PDF se genera con Dompdf; no necesita Chromium ni binarios externos.
- No hay límite de 1.000 registros: el límite es el de tu MySQL/hosting.
- Si usás HTTPS, `APP_URL` debe ser `https://...` para que los links y el PDF
  usen el dominio correcto.
