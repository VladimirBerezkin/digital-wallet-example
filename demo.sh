#!/bin/bash

# Digital Wallet Demo Setup Script
# One command to run the entire demo

echo "🚀 Digital Wallet Demo Setup"
echo "=============================="

# Detect platform and set user IDs
echo "🔍 Detecting platform..."

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

# Set user IDs based on platform
if [ "$MACHINE" = "windows" ]; then
    export WWWUSER=1000
    export WWWGROUP=1000
else
    export WWWUSER=$(id -u)
    export WWWGROUP=$(id -g)
fi

echo "User ID: $WWWUSER, Group ID: $WWWGROUP"

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker Desktop and try again."
    exit 1
fi

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    if [ -f .env.docker.example ]; then
        cp .env.docker.example .env
        echo "✅ .env file created from .env.docker.example"
    else
        echo "❌ .env.docker.example not found. Please create a .env file manually."
        exit 1
    fi
else
    echo "✅ .env file found"
fi

# Build and start containers
echo "🐳 Building and starting containers..."
docker-compose -f docker-compose.yml up -d --build

# Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 20

# Check if app is running
echo ""
echo "✅ Digital Wallet Demo is running successfully!"
echo ""
echo "🌐 Access the application at: http://localhost:8080"
echo "🗄️  Database: localhost:3306"
echo ""
echo "📋 Useful commands:"
echo "   • View logs: docker-compose -f docker-compose.yml logs -f"
echo "   • Stop: docker-compose -f docker-compose.yml down"
echo "   • Restart: docker-compose -f docker-compose.yml restart"
echo ""
echo "🔧 Development commands:"
echo "   • Run artisan: docker-compose -f docker-compose.yml exec app php artisan [command]"
echo "   • Run composer: docker-compose -f docker-compose.yml exec app composer [command]"
echo "   • Run npm: docker-compose -f docker-compose.yml exec app npm [command]"
echo "   • Access shell: docker-compose -f docker-compose.yml exec app bash"
echo ""
echo "🎉 Demo is ready! Open http://localhost:8080 in your browser."
