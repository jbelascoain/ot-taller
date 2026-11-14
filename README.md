# OT Taller

Aplicación web self-hosted para talleres de motos: clientes, motos, órdenes de trabajo,
repuestos, proveedores, tablero kanban, presupuestos en PDF y envío por WhatsApp.

Hecha para correr en **Docker** durante el desarrollo y desplegarse en un **shared web hosting**
con PHP 8.3 + MySQL, sin Node ni Composer en el servidor.

## Stack

- PHP 8.3 + CodeIgniter 4
- MySQL 8 / MariaDB 10.x (SQL compatible con ambos)
- Dompdf para los PDF (PHP puro, sin Chromium)
- Docker Compose para desarrollo
- Sin build step de frontend: CSS y JS propios

## Funcionalidades

- Login con roles **Admin** y **Staff**.
- Tablero kanban por estado de la orden, con filtro **“Esperando cliente”**
  (órdenes Para entregar o con ítems presupuestados).
- Órdenes de trabajo con:
  - moto existente o nueva, cliente existente o nuevo;
  - fecha de ingreso, kilometraje, problema y diagnóstico;
  - ítems del catálogo o creación de repuesto/proveedor en el momento;
  - mano de obra, forma de pago, factura y fecha de entrega;
  - totales de repuestos, aprobado y total.
- Catálogo de repuestos con precio de compra, precio de venta, stock y stock mínimo.
- Clientes, motos y proveedores con búsqueda e historial.
- PDF A4 de la orden (logo, cliente, moto, diagnóstico, ítems y totales).
- Botón de WhatsApp con mensaje prellenado y PDF descargable/compartible.
- Panel de administración: nombre del taller, logo, contacto, moneda, locale,
  texto del PDF, plantilla de WhatsApp y usuarios.

## Inicio rápido con Docker

```bash
cp .env.example .env
docker compose up -d --build
```

Abrí <http://localhost:8080/setup> y creá el usuario administrador.

- Token de instalación por defecto: `local-setup` (variable `SETUP_TOKEN`).
- Podés tildar **“Cargar datos de demo”** para arrancar con clientes, motos,
  repuestos y órdenes ficticias.
- Usuario de demo (si cargás los datos): `staff@example.com` / `demo1234`.

Otros puertos:

- App: <http://localhost:8080>
- phpMyAdmin: <http://localhost:8081>
- MySQL: `localhost:3307`

Para reiniciar la base desde cero:

```bash
docker compose down -v
docker compose up -d
```

## Estructura

```
app/            Código CodeIgniter (controllers, modelos, vistas)
database/       schema.sql (el demo se carga desde /setup)
docker/         Dockerfile y configuración de Apache
public/         Document root (index.php, assets, uploads)
writable/       Logs, sesiones y caché (no se versiona)
bin/            Script para generar el release de shared hosting
docs/DEPLOY.md  Guía de despliegue sin Composer
```

## Configuración

Todo se configura desde **Admin → Configuración** una vez instalado:

- Nombre y contacto del taller.
- Logo (PNG/JPG/WEBP, hasta 2 MB).
- Símbolo de moneda y locale.
- Texto al pie del PDF (acepta `{workshop}`).
- Plantilla de WhatsApp: `{cliente}`, `{orden}`, `{moto}`, `{total}`.

Variables de entorno principales (`.env` o entorno del contenedor):

| Variable | Descripción |
| --- | --- |
| `APP_URL` | URL pública de la app |
| `APP_PORT` | Puerto local de la app (default 8080) |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` | Conexión MySQL |
| `DB_ROOT_PASS` | Password root de MySQL en Docker |
| `SETUP_TOKEN` | Token de la pantalla `/setup` |
| `CI_ENVIRONMENT` | `development` o `production` |

## Shared hosting

El repo está preparado para subir a cPanel o similar **sin Composer**:
`bin/build-release.sh` genera un ZIP con `vendor/` incluido. Ver
[docs/DEPLOY.md](docs/DEPLOY.md).

## Seguridad

- Cambiá el password del admin y `SETUP_TOKEN` después de instalar.
- Usá HTTPS en producción (el hosting lo provee).
- No subas `.env` al repo.
- Hacé backups de la base desde phpMyAdmin o con `mysqldump`.
- El acceso a `/admin` está restringido al rol Admin.

## Licencia

MIT. Ver [LICENSE](LICENSE).
