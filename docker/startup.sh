#!/bin/bash

# Set proper permissions for Laravel
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

# .env file should already exist (copied from host)

# Generate application key
php artisan key:generate

# Install Node.js dependencies and build assets
RUN npm ci && npm run build

# Wait for database to be ready
echo "Waiting for database to be ready..."
until php -r "try { new PDO('mysql:host=mysql;dbname=digital_wallet', 'laravel', 'laravel'); echo 'Database is ready!'; exit(0); } catch (Exception \$e) { exit(1); }" > /dev/null 2>&1; do
    echo "Database is unavailable - sleeping"
    sleep 2
done
echo "Database is ready!"

# Run migrations
php artisan migrate --force
php artisan db:seed

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
