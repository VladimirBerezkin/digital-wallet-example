#!/bin/bash

# Digital Wallet Docker Setup Script
echo "Starting Laravel Sail Docker Environment..."

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

until ./vendor/bin/sail up -d; do
    echo "Starting sail.."
    sleep 2
done

echo "Installing composer deps..."
./vendor/bin/sail composer install --no-interaction

echo "Generating app key..."
./vendor/bin/sail artisan key:generate

echo "Installing frontend deps..."
./vendor/bin/sail npm install
./vendor/bin/sail npm run build

echo "Preparing db..."
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
