#!/bin/bash

# Function to fix permissions (container-only)
fix_permissions() {
    echo "Fixing permissions inside Docker container..."
    
    # Use the dedicated container-only permission fix script
    /usr/local/bin/fix-permissions-container.sh
}

# Fix permissions
fix_permissions

# Ensure .env file exists
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env file from example..."
    cp /var/www/html/.env.docker.example /var/www/html/.env
    chown www-data:www-data /var/www/html/.env
fi

# Generate application key if not set
if ! grep -q "APP_KEY=" /var/www/html/.env || grep -q "APP_KEY=$" /var/www/html/.env; then
    echo "Generating application key..."
    php artisan key:generate
fi

# Install Node.js dependencies and build assets
echo "Installing Node.js dependencies and building assets..."
npm ci && npm run build

# Wait for database to be ready
echo "Waiting for database to be ready..."
until php -r "try { new PDO('mysql:host=mysql;dbname=digital_wallet', 'laravel', 'laravel'); echo 'Database is ready!'; exit(0); } catch (Exception \$e) { exit(1); }" > /dev/null 2>&1; do
    echo "Database is unavailable - sleeping"
    sleep 2
done
echo "Database is ready!"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force
php artisan db:seed

# Fix permissions again after migrations (in case new files were created)
fix_permissions

# Start supervisor
echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
