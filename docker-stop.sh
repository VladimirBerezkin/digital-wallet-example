#!/bin/bash

# Digital Wallet - MySQL Docker Stop Script

echo "🛑 Stopping Digital Wallet MySQL container..."

# Stop and remove all containers
docker-compose down

echo "✅ MySQL container stopped and removed."
echo ""
echo "💾 Your data is preserved in Docker volumes:"
echo "   - mysql_data (MySQL database)"
echo "   - Application files remain in your project directory"
echo ""
echo "🔄 To start again, run: ./docker-start.sh"
echo "🗑️  To remove all data and start fresh, run: ./docker-clean.sh"