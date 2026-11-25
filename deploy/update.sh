#!/usr/bin/env bash
# Deploy pembaruan (jalankan sebagai root): git pull, dependensi, migrasi, cache ulang.
set -euo pipefail
export COMPOSER_ALLOW_SUPERUSER=1
cd /var/www/si-poli
php artisan down || true
git pull --ff-only
composer install --no-dev --optimize-autoloader --no-interaction
# Aset Tailwind/Vite; server lama dipasangi Node.js 22 sekali saja
command -v node >/dev/null && [ "$(node -v | cut -d. -f1 | tr -d v)" -ge 22 ] || {
  curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && apt-get install -y nodejs; }
npm ci && npm run build
# Kunci .env yang ditambahkan setelah server dipasang
grep -q '^BCRYPT_LIMIT=' .env || echo 'BCRYPT_LIMIT=72' >> .env
grep -q '^APP_PREVIOUS_KEYS=' .env || echo 'APP_PREVIOUS_KEYS=' >> .env
php artisan migrate --force
php artisan optimize
chown -R www-data:www-data storage bootstrap/cache
# Scheduler Laravel (bot Telegram kontrol harian); ditimpa tiap deploy, jadi aman diulang
echo "* * * * * cd /var/www/si-poli && php artisan schedule:run >> /dev/null 2>&1" | crontab -u www-data -
systemctl reload php8.4-fpm
php artisan up
