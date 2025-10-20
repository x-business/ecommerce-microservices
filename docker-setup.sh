#!/bin/bash

# Docker setup script for E-Commerce Store

echo "Setting up Docker environment for E-Commerce Store..."

# Copy environment file
if [ ! -f .env ]; then
    cp docker.env .env
    echo "Created .env file from docker.env"
fi

# Generate application key
php artisan key:generate

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install

# Build frontend assets
npm run build

# Start Docker containers
docker-compose up -d

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 30

# Run migrations and seeders
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force

echo "Docker setup complete!"
echo "Application is available at: http://localhost:8080"
echo "MailHog is available at: http://localhost:8025"
echo "MySQL is available at: localhost:3306"
echo "Redis is available at: localhost:6379"
