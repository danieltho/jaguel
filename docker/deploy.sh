#!/usr/bin/env sh
set -e

cd /var/www

composer install --no-interaction --prefer-dist --optimize-autoloader

if [ ! -f .env ]; then
    cp .env.example .env
fi

php artisan key:generate --force
php artisan migrate --force --no-interaction
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link || true

echo "Deploy completado."
