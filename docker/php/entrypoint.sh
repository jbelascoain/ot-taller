#!/bin/sh
set -e

mkdir -p /var/www/html/writable/session \
         /var/www/html/writable/cache \
         /var/www/html/writable/logs \
         /var/www/html/storage/uploads \
         /var/www/html/public/uploads

chown -R www-data:www-data /var/www/html/writable /var/www/html/storage /var/www/html/public/uploads || true
chmod -R ug+rwX /var/www/html/writable /var/www/html/storage /var/www/html/public/uploads || true

exec "$@"
