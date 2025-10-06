#!/bin/bash

# Container-only permission fix script
# This script only affects files INSIDE the Docker container
# It does NOT affect the host machine in any way

echo "Fixing permissions inside Docker container..."

# Function to fix permissions for Laravel directories
fix_laravel_permissions() {
    echo "Setting up Laravel directory permissions..."
    
    # Create directories if they don't exist
    mkdir -p /var/www/html/storage/framework/views
    mkdir -p /var/www/html/storage/framework/cache
    mkdir -p /var/www/html/storage/framework/sessions
    mkdir -p /var/www/html/storage/logs
    mkdir -p /var/www/html/bootstrap/cache
    
    # Set ownership to www-data (container user)
    chown -R www-data:www-data /var/www/html/storage
    chown -R www-data:www-data /var/www/html/bootstrap/cache
    
    # Set proper permissions for Laravel
    chmod -R 775 /var/www/html/storage
    chmod -R 775 /var/www/html/bootstrap/cache
    
    # Ensure group write permissions
    chmod -R g+w /var/www/html/storage
    chmod -R g+w /var/www/html/bootstrap/cache
    
    echo "Laravel permissions fixed inside container"
}

# Function to fix permissions for application files
fix_app_permissions() {
    echo "Setting up application file permissions..."
    
    # Set ownership for the entire application directory
    chown -R www-data:www-data /var/www/html
    
    # Set proper permissions for application files
    find /var/www/html -type f -exec chmod 644 {} \;
    find /var/www/html -type d -exec chmod 755 {} \;
    
    # Make scripts executable
    find /var/www/html -name "*.sh" -exec chmod +x {} \;
    
    # Special permissions for Laravel directories
    chmod -R 775 /var/www/html/storage
    chmod -R 775 /var/www/html/bootstrap/cache
    
    echo "Application permissions fixed inside container"
}

# Function to fix permissions for specific file types
fix_file_type_permissions() {
    echo "Setting up file type specific permissions..."
    
    # Make sure artisan is executable
    chmod +x /var/www/html/artisan
    
    # Set proper permissions for .env file
    if [ -f /var/www/html/.env ]; then
        chown www-data:www-data /var/www/html/.env
        chmod 644 /var/www/html/.env
    fi
    
    # Set proper permissions for public directory
    chmod -R 755 /var/www/html/public
    
    echo "File type permissions fixed inside container"
}

# Main execution
echo "Starting container-only permission fix..."
echo "This will NOT affect your host machine"

# Fix Laravel specific permissions
fix_laravel_permissions

# Fix application permissions
fix_app_permissions

# Fix file type specific permissions
fix_file_type_permissions

echo "All permissions fixed inside Docker container!"
echo "Host machine permissions remain unchanged"