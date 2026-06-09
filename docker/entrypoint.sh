#!/bin/bash
set -e

# Copy .env if not exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate app key if not set
APP_KEY_VALUE=$(grep '^APP_KEY=' .env | cut -d '=' -f2-)
if [ -z "$APP_KEY_VALUE" ]; then
    php artisan key:generate --force
fi

# Wait for MySQL
until php -r "new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; do
    echo "Waiting for MySQL..."
    sleep 2
done
echo "MySQL is ready."

# Run migrations
php artisan migrate --force

# Seed if first time (check if roles table is empty)
ROLE_COUNT=$(php artisan tinker --execute="echo \App\Models\Role::count();" 2>/dev/null || echo "0")
if [ "$ROLE_COUNT" = "0" ]; then
    php artisan db:seed --force
fi

# Storage link
php artisan storage:link --force 2>/dev/null || true

# Cache config in production
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

# Start cron for scheduled tasks
service cron start 2>/dev/null || true

# Hand off to the CMD (php-fpm by default)
exec "$@"
