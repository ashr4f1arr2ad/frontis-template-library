#!/bin/bash
set -e

echo "Starting Production Environment..."

# Fix permissions
echo "Fixing permissions..."
mkdir -p /var/www/storage/framework/{cache,data,sessions,views}
mkdir -p /var/www/storage/logs
mkdir -p /var/www/storage/app/public
mkdir -p /var/www/storage/app/private
mkdir -p /var/www/bootstrap/cache

chown -R www-data:www-data /var/www/storage
chown -R www-data:www-data /var/www/bootstrap/cache
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache

# Wait for database
echo "Waiting for database..."
until php artisan db:show >/dev/null 2>&1; do
    echo "Database not ready - waiting..."
    sleep 3
done
echo "Database connected!"

# Migrate
echo "Running migrations..."
php artisan migrate --force

# Optimize Laravel for production
echo "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Octane..."
exec "$@"
