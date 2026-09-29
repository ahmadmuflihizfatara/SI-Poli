#!/usr/bin/env bash
# Setup awal VPS Ubuntu 22.04/24.04 untuk SI-Poli (Nginx + PHP 8.3 + MariaDB + HTTPS).
# Jalankan sebagai root:  DOMAIN=poli.contoh.ac.id EMAIL=kamu@gmail.com bash server-setup.sh
# Update berikutnya:      bash /var/www/si-poli/deploy/update.sh
set -euo pipefail

: "${DOMAIN:?isi DOMAIN}" "${EMAIL:?isi EMAIL}"
REPO="${REPO:-https://github.com/ahmadmuflihizfatara/SI-Poli.git}"
APP=/var/www/si-poli
DB=si_poli
DBPASS=$(openssl rand -hex 16)

export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y software-properties-common curl git unzip ufw nginx mariadb-server certbot python3-certbot-nginx
add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Database
mysql -e "CREATE DATABASE IF NOT EXISTS $DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'sipoli'@'localhost' IDENTIFIED BY '$DBPASS';
GRANT ALL ON $DB.* TO 'sipoli'@'localhost'; FLUSH PRIVILEGES;"

# Kode
[ -d "$APP/.git" ] || git clone "$REPO" "$APP"
cd "$APP"
export COMPOSER_ALLOW_SUPERUSER=1
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
php artisan storage:link || true
php artisan optimize

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

# Nginx
cat > /etc/nginx/sites-available/si-poli <<EOF
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
EOF
ln -sf /etc/nginx/sites-available/si-poli /etc/nginx/sites-enabled/si-poli
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

# Firewall (SSH tetap terbuka)
ufw allow OpenSSH && ufw allow 'Nginx Full' && ufw --force enable

# HTTPS (DNS A record domain harus sudah mengarah ke IP VPS)
certbot --nginx -d "$DOMAIN" -m "$EMAIL" --agree-tos --no-eff-email --redirect -n

echo "Selesai. https://$DOMAIN  |  password DB tersimpan di $APP/.env"
