#!/bin/bash

# Digital Wallet Docker Stop Script
echo "Stopping Digital Wallet Docker Environment..."

# Stop all containers
docker-compose down

echo "All containers stopped."
echo ""
echo "To start again: ./docker-start.sh"
echo "To clean everything: ./docker-clean.sh"
