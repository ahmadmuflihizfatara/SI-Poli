#!/usr/bin/env bash
# Deploy pembaruan (jalankan sebagai root): git pull, dependensi, migrasi, cache ulang.
set -euo pipefail
export COMPOSER_ALLOW_SUPERUSER=1
cd /var/www/si-poli
php artisan down || true
git pull --ff-only
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize
chown -R www-data:www-data storage bootstrap/cache
systemctl reload php8.3-fpm
php artisan up
