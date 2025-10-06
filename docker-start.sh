#!/bin/bash

# Digital Wallet Docker Setup Script
echo "Starting Digital Wallet Docker Environment..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "Docker is not running. Please start Docker Desktop and try again."
    exit 1
fi

# Check if .env file exists
if [ ! -f .env ]; then
    echo "Error: .env file not found!"
    echo ""
    echo "Please create a .env file by copying from the example:"
    echo "   cp .env.docker.example .env"
    echo ""
    echo "Then add your Pusher credentials and other required settings."
    echo "The .env file will be copied into the Docker container."
    exit 1
fi

echo ".env file found - will be copied into Docker container"

# Build and start containers
echo "Building and starting containers..."
docker-compose up -d --build

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
until docker-compose exec mysql mysqladmin ping -h localhost --silent; do
    echo "Waiting for MySQL..."
    sleep 2
done

echo ""
echo "Digital Wallet is now running!"
echo ""
echo "Access Points:"
echo "   • Application: http://localhost:8080"
echo "   • MySQL: localhost:3306"
echo ""
echo "Useful Commands:"
echo "   • View logs: docker-compose logs -f"
echo "   • Stop: ./docker-stop.sh"
echo "   • Restart: ./docker-restart.sh"
echo "   • Clean: ./docker-clean.sh"
echo ""
