# Simple Dockerfile for demo - copies all files and handles permissions
FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev \
    zip unzip nodejs npm supervisor nginx \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip sockets \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create application user (will be mapped to host user at runtime)
RUN groupadd --force -g 1000 app
RUN useradd -ms /bin/bash --no-user-group -g 1000 -u 1000 app

# Copy all application files
COPY . .

# Create necessary directories
RUN mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache

# Copy configurations
COPY docker/nginx/default.conf /etc/nginx/sites-available/default

# Copy startup script
COPY docker/startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

# Expose port
EXPOSE 80

# Start with startup script
CMD ["/usr/local/bin/startup.sh"]
