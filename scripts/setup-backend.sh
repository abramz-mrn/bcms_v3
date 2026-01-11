#!/bin/bash
# Script to setup complete BCMS Laravel backend structure
# Run this from the project root: bash scripts/setup-backend.sh

set -e

echo "🚀 Starting BCMS Backend Setup..."

API_PATH="apps/api"

# Check if Laravel exists
if [ ! -d "$API_PATH" ]; then
    echo "❌ Laravel app not found at $API_PATH"
    exit 1
fi

cd $API_PATH

echo "📦 Installing Laravel packages..."
docker run --rm -v $(pwd):/app composer:latest composer require laravel/sanctum laravel/horizon laravel/octane --no-interaction
docker run --rm -v $(pwd):/app composer:latest composer require spiral/roadrunner --no-interaction

echo "📝 Publishing package configs..."
docker run --rm -v $(pwd):/app -w /app php:8.3-cli php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --tag="sanctum-migrations"
docker run --rm -v $(pwd):/app -w /app php:8.3-cli php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider"
docker run --rm -v $(pwd):/app -w /app php:8.3-cli php artisan vendor:publish --provider="Laravel\Octane\OctaneServiceProvider"

echo "✅ Laravel packages installed successfully!"

# Create directory structure
echo "📁 Creating directory structure..."
mkdir -p database/migrations
mkdir -p database/seeders
mkdir -p app/Models
mkdir -p app/Http/Controllers/Api
mkdir -p app/Http/Requests
mkdir -p app/Http/Resources
mkdir -p app/Http/Middleware
mkdir -p app/Services/Mikrotik
mkdir -p app/Services/Payment
mkdir -p app/Services/Notification
mkdir -p app/Jobs
mkdir -p app/Events
mkdir -p app/Listeners
mkdir -p app/Policies

echo "✅ Directory structure created!"

echo "🎉 Backend setup script completed!"
echo ""
echo "Next steps:"
echo "1. Run: cd $API_PATH"
echo "2. Generate migrations with artisan"
echo "3. Run migrations: php artisan migrate"
echo "4. Run seeders: php artisan db:seed"
