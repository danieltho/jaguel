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

# Generar APP_KEY SOLO si no existe. NUNCA rotarla en cada deploy:
# rotaría la clave de cifrado e invalidaría todo lo encriptado (settings SMTP,
# tokens) y las sesiones. La clave debe ser estable en producción.
if ! grep -qE '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

php artisan migrate --force --no-interaction

if [ "${DEPLOY_MODE:-prod}" = "prod" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    # Local: sin cache para que .env/rutas/vistas se relean en vivo.
    # Limpiamos por si quedó cache de una corrida previa.
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
fi

# Crear symlink si no existe (idempotente, sin enmascarar errores)
if [ ! -L public/storage ]; then
    php artisan storage:link
fi

echo "Deploy completado."