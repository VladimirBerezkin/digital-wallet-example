#!/bin/bash

# Digital Wallet - MySQL Docker Clean Script

echo "🧹 Cleaning Digital Wallet MySQL Docker environment..."

# Stop and remove all containers
echo "🛑 Stopping containers..."
docker-compose down

# Remove all containers, networks, and volumes
echo "🗑️  Removing containers, networks, and volumes..."
docker-compose down -v --remove-orphans

# Remove any dangling images
echo "🖼️  Removing unused images..."
docker image prune -f

# Remove any dangling volumes
echo "💾 Removing unused volumes..."
docker volume prune -f

echo ""
echo "✅ Cleanup complete!"
echo ""
echo "🔄 To start fresh, run: ./docker-start.sh"
echo "⚠️  All data has been removed. You'll need to run migrations again."