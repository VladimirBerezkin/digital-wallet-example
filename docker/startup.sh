#!/bin/bash

# Digital Wallet Demo Startup Script
# Handles all initialization inside the container

echo "🚀 Starting Digital Wallet Demo..."

# Function to detect platform and set user IDs
detect_platform() {
    UNAMEOUT="$(uname -s)"
    case "${UNAMEOUT}" in
        Linux*)     MACHINE=linux;;
        Darwin*)    MACHINE=mac;;
        CYGWIN*)    MACHINE=windows;;
        MINGW*)     MACHINE=windows;;
        *)          MACHINE="UNKNOWN"
    esac

    # Detect WSL
    if [ "$MACHINE" = "linux" ] && grep -q Microsoft /proc/version 2>/dev/null; then
        MACHINE=wsl
    fi

    echo "Platform detected: $MACHINE"
}

# Function to update container user to match host user
update_user_mapping() {
    echo "Using default container user (www-data)"
}

# Function to fix permissions (container-only)
#fix_permissions() {
#    echo "Setting up permissions..."
#
#    # Set ownership to www-data (default container user)
#    chown -R www-data:www-data /var/www/html
#
#    # Set proper permissions
#    find /var/www/html -type f -exec chmod 644 {} \;
#    find /var/www/html -type d -exec chmod 755 {} \;
#    chmod +x /var/www/html/artisan
#
#    # Special permissions for Laravel directories
#    chmod -R 775 /var/www/html/storage
#    chmod -R 775 /var/www/html/bootstrap/cache
#
#    echo "✅ Permissions set correctly"
#}

# Function to install dependencies
install_dependencies() {
    echo "Installing dependencies..."

    # Install PHP dependencies
    echo "Installing PHP dependencies..."
    composer install --no-interaction

    # Install Node.js dependencies
    echo "Installing Node.js dependencies..."
    npm ci

    # Build frontend assets
    echo "Building frontend assets..."
    npm run build

    echo "✅ Dependencies installed and built"
}

# Function to initialize application
initialize_application() {
    echo "Initializing application..."

    # Generate app key if needed
    echo "Generating application key..."
    php artisan key:generate

    # Wait for database
    echo "Waiting for database to be ready..."
    until php -r "try { new PDO('mysql:host=mysql;dbname=digital_wallet', 'laravel', 'password'); echo 'Database is ready!'; exit(0); } catch (Exception \$e) { exit(1); }" > /dev/null 2>&1; do
        echo "Database is unavailable - sleeping"
        sleep 2
    done

    # Run migrations
    echo "Running database migrations..."
    php artisan migrate:fresh --force

    # Run seeders
    echo "Running database seeders..."
    php artisan db:seed

    echo "✅ Application initialized"
}

# Main execution
echo "🔧 Platform detection and user mapping..."

# Detect platform
detect_platform

# Update user mapping
update_user_mapping

# Install dependencies
install_dependencies

# Initialize application
initialize_application

# Start nginx and php-fpm
echo "🚀 Starting web server..."

# Start PHP-FPM in the background
php-fpm -D

# Start nginx in the foreground
nginx -g "daemon off;"

php artisan queue:work
