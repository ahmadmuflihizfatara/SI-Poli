#!/usr/bin/env bash
# Setup SI-Poli di Ubuntu 24.04 bersih (Nginx + PHP 8.3 + MariaDB + HTTPS). Jalankan sebagai root:
#   DOMAIN=poli.jembatanlayang.cloud EMAIL=kamu@gmail.com bash server-setup.sh
# Situs lain: salin /etc/nginx/sites-available/si-poli jadi file baru (ganti server_name & root), lalu `certbot --nginx -d domainbaru`.
# Update aplikasi: bash /var/www/si-poli/deploy/update.sh
set -euo pipefail

: "${DOMAIN:?isi DOMAIN}" "${EMAIL:?isi EMAIL}"
REPO="${REPO:-https://github.com/ahmadmuflihizfatara/SI-Poli.git}"
APP=/var/www/si-poli
DB=si_poli
DBPASS=$(openssl rand -hex 16)

export DEBIAN_FRONTEND=noninteractive COMPOSER_ALLOW_SUPERUSER=1
apt-get update
apt-get install -y nginx mariadb-server git unzip curl ufw composer certbot python3-certbot-nginx \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl

mysql -e "CREATE DATABASE IF NOT EXISTS $DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'sipoli'@'localhost' IDENTIFIED BY '$DBPASS';
GRANT ALL ON $DB.* TO 'sipoli'@'localhost'; FLUSH PRIVILEGES;"

[ -d "$APP/.git" ] || git clone "$REPO" "$APP"
cd "$APP"
composer install --no-dev --optimize-autoloader --no-interaction

if [ ! -f .env ]; then
  cp .env.example .env
  sed -i \
    -e "s|^APP_NAME=.*|APP_NAME=SI-Poli|" \
    -e "s|^APP_ENV=.*|APP_ENV=production|" \
    -e "s|^APP_DEBUG=.*|APP_DEBUG=false|" \
    -e "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" \
    -e "s|^APP_LOCALE=.*|APP_LOCALE=id|" \
    -e "s|^LOG_LEVEL=.*|LOG_LEVEL=error|" \
    -e "s|^DB_CONNECTION=.*|DB_CONNECTION=mysql|" \
    -e "s|^# DB_HOST=.*|DB_HOST=127.0.0.1|" \
    -e "s|^# DB_PORT=.*|DB_PORT=3306|" \
    -e "s|^# DB_DATABASE=.*|DB_DATABASE=$DB|" \
    -e "s|^# DB_USERNAME=.*|DB_USERNAME=sipoli|" \
    -e "s|^# DB_PASSWORD=.*|DB_PASSWORD=$DBPASS|" .env
  php artisan key:generate --force
fi
php artisan migrate --force
php artisan optimize
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

cat > /etc/nginx/sites-available/si-poli <<NGINX
server {
    listen 80;
    server_name $DOMAIN;
    root $APP/public;
    index index.php;
    client_max_body_size 10M;

    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location ~ /\.(?!well-known) { deny all; }
}
NGINX
ln -sf /etc/nginx/sites-available/si-poli /etc/nginx/sites-enabled/si-poli
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

# Firewall: port SSH dideteksi otomatis supaya tidak terkunci
SSHP=$(ss -tlnp | awk '/sshd/{n=split($4,a,":"); print a[n]; exit}')
ufw allow "${SSHP:-22}/tcp" && ufw allow 80,443/tcp && ufw --force enable

# HTTPS: A record $DOMAIN harus sudah mengarah ke IP VPS
certbot --nginx -d "$DOMAIN" -m "$EMAIL" --agree-tos --no-eff-email --redirect -n

echo "Selesai: https://$DOMAIN  (password DB ada di $APP/.env)"
