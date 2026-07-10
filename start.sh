#!/bin/bash

set -e

echo "==> Starting NexStock Production Server..."

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

echo "==> Starting Apache..."
exec apache2-foreground
