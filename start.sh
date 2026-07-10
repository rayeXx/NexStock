#!/bin/bash

set -e

echo "==> Starting NexStock Production Server..."

# Railway provides a dynamic PORT — configure Apache to use it
PORT="${PORT:-80}"
echo "==> Configuring Apache on port $PORT..."
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/*.conf

# Generate APP_KEY if not already set
if [ -z "$APP_KEY" ]; then
    echo "==> Generating APP_KEY..."
    php artisan key:generate --force
fi

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Clearing & caching config/routes/views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Creating storage symlink..."
php artisan storage:link || true

echo "==> Starting Apache on port $PORT..."
exec apache2-foreground
