#!/usr/bin/env sh
set -e

cd /var/www

if [ ! -f .env ]; then
    cp .env.example .env
fi

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/testing \
    storage/logs \
    bootstrap/cache

php artisan key:generate --force
php artisan migrate --force --no-interaction
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Crear symlink si no existe (idempotente, sin enmascarar errores)
if [ ! -L public/storage ]; then
    php artisan storage:link
fi

echo "Deploy completado."