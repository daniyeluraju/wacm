#!/bin/bash
set -e

# Support cloud platform dynamic PORT (Railway / Render / Fly.io / Heroku)
PORT=${PORT:-80}
sed -i "s/80/${PORT}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

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
