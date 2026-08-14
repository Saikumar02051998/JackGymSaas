#!/bin/sh

set -e

echo "Starting Laravel application..."

# Clear old cached configuration
php artisan optimize:clear

# Run database migrations
php artisan migrate --force

# Create storage symlink
php artisan storage:link || true

# Cache Laravel configuration
php artisan config:cache

# Cache routes
php artisan route:cache || true

# Cache views
php artisan view:cache || true

echo "Laravel initialization completed."

# Start PHP-FPM
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"