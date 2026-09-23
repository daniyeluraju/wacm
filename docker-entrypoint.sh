#!/bin/bash
set -e

PORT="${PORT:-80}"
echo "===> Configuring Apache to listen on port ${PORT}..."
sed -i "s/Listen [0-9]\+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]\+>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Clean up any duplicate MPMs in mods-enabled — enforce single mpm_prefork
rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf
ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

# Setup Apache environment variables and runtime directories
[ -f /etc/apache2/envvars ] && . /etc/apache2/envvars
mkdir -p /var/run/apache2 /var/lock/apache2 /var/log/apache2
rm -f /var/run/apache2/apache2.pid

# Create necessary storage directories and permissions
mkdir -p /var/www/html/storage/logs \
         /var/www/html/storage/temporary \
         /var/www/html/storage/exports \
         /var/www/html/storage/uploads \
         /var/www/html/gateway/auth_info_baileys

chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# If database connection is configured, wait for DB and run migrations
if [ -n "$DATABASE_URL" ] || [ -n "$MYSQL_URL" ] || [ -n "$DB_HOST" ]; then
    echo "===> Checking database connection and running migrations..."
    for i in {1..30}; do
        if php /var/www/html/database/migrate.php; then
            echo "===> Database migrations executed successfully!"
            break
        fi
        echo "===> Waiting for database to become ready (attempt $i/30)..."
        sleep 2
    done
fi

echo "===> Starting WACM Web Server and WhatsApp Gateway Daemon..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
