#!/bin/bash

# Digital Wallet Docker Clean Script
echo "Cleaning Digital Wallet Docker Environment..."

# Stop and remove containers
echo "Stopping and removing containers..."
docker-compose down -v

# Remove images
echo "Removing images..."
docker-compose down --rmi all

# Remove volumes
echo "Removing volumes..."
docker volume prune -f

# Remove unused networks
echo "Removing unused networks..."
docker network prune -f

echo "Cleanup completed!"
echo ""
echo "To start fresh: ./demo.sh"
