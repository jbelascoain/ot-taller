#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/ot-taller-release.zip"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

echo "==> Copiando archivos"
mkdir -p "$TMP/ot-taller"
tar -C "$ROOT" -cf - \
  --exclude=.git \
  --exclude=vendor \
  --exclude=writable/cache \
  --exclude=writable/logs \
  --exclude=writable/session \
  --exclude=public/uploads \
  --exclude=tests \
  --exclude=phpunit.dist.xml \
  --exclude=builds \
  --exclude=preload.php \
  --exclude=ot-taller-release.zip \
  --exclude=.env \
  . | tar -C "$TMP/ot-taller" -xf -

mkdir -p "$TMP/ot-taller/writable/cache" \
         "$TMP/ot-taller/writable/logs" \
         "$TMP/ot-taller/writable/session" \
         "$TMP/ot-taller/public/uploads" \
         "$TMP/ot-taller/storage/uploads"
touch "$TMP/ot-taller/public/uploads/.gitkeep" "$TMP/ot-taller/storage/uploads/.gitkeep"
cp "$ROOT/public/uploads/.htaccess" "$TMP/ot-taller/public/uploads/.htaccess"

echo "==> Instalando dependencias de producción"
if command -v composer >/dev/null 2>&1; then
  (cd "$TMP/ot-taller" && composer install --no-dev --optimize-autoloader --no-interaction)
else
  docker run --rm -u "$(id -u):$(id -g)" \
    -v "$TMP/ot-taller":/app -w /app \
    composer:2 install --no-dev --optimize-autoloader \
    --ignore-platform-req=ext-intl --no-interaction
fi

echo "==> Generando ZIP"
python3 - "$TMP/ot-taller" "$OUT" <<'PY'
import os, sys, zipfile
src, out = sys.argv[1], sys.argv[2]
base = os.path.dirname(src)
with zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as z:
    for root, _, files in os.walk(src):
        for name in files:
            path = os.path.join(root, name)
            z.write(path, os.path.relpath(path, base))
print(out)
PY

echo "==> Listo: $OUT"
