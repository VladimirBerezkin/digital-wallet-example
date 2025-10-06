#!/bin/bash

# Digital Wallet - MySQL Docker Setup Script

echo "🐳 Starting Digital Wallet with MySQL Docker container..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
fi

# Start MySQL service
echo "🏗️  Starting MySQL service..."
docker-compose up -d

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 15

# Check if MySQL is ready
echo "🔍 Checking MySQL connection..."
until docker exec digital-wallet-mysql mysqladmin ping -h"localhost" --silent; do
    echo "⏳ MySQL is not ready yet, waiting..."
    sleep 3
done

php artisan key:generate
php artisan migrate
php artisan db:seed

echo "✅ MySQL is ready!"

echo ""
echo "🎉 Setup complete!"
echo ""
echo "📊 Database Information:"
echo "   Host: 127.0.0.1 (localhost)"
echo "   Port: 3306"
echo "   Database: digital_wallet"
echo "   Username: laravel"
echo "   Password: laravel"
echo ""
echo "🛠️  Useful Commands:"
echo "   Start Laravel server: composer dev"
echo "   View MySQL logs: docker-compose logs -f mysql"
echo "   Stop MySQL: docker-compose down"
echo "   Restart MySQL: docker-compose restart"
echo "   Access MySQL: docker-compose exec mysql mysql -u laravel -p digital_wallet"
echo "   Run artisan commands: php artisan [command]"
echo ""
echo "🚀 To start your application, run: composer dev"

composer dev
