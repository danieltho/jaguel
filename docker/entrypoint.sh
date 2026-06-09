#!/usr/bin/env sh
set -e

# Sincronizar código de la imagen al volumen montado (refresca el código en prod,
# donde /var/www es un named volume). En local /var/www es un bind mount del repo
# del host: el override pone SKIP_IMAGE_SYNC=true para NO correr rsync --delete y
# así no borrar .git, node_modules, etc. (preserva storage, .env, cache)
if [ "${SKIP_IMAGE_SYNC:-false}" != "true" ]; then
  rsync -a --delete \
    --exclude='/.git' \
    --exclude='/storage' \
    --exclude='/bootstrap/cache' \
    --exclude='/.env' \
    /opt/app-image/ /var/www/
fi

cd /var/www

# Permisos del volumen (corremos como root acá)
chown -R jaguelweb:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "Esperando a la base de datos..."
until php -r "try { new PDO('mysql:host=' . (getenv('DB_HOST') ?: 'db') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME') ?: 'laravel', getenv('DB_PASSWORD') ?: 'secret'); exit(0); } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
    sleep 2
done
echo "Base de datos lista."

if [ -f .env ]; then
    export $(grep -E '^(DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME|DB_PASSWORD)=' .env | xargs)
fi

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_USERNAME="${DB_USERNAME:-laravel}"
DB_PASSWORD="${DB_PASSWORD:-secret}"

if [ -n "$DB_DATABASE" ]; then
    echo "Asegurando que la base de datos '$DB_DATABASE' exista..."
    php -r "
        \$pdo = new PDO('mysql:host=$DB_HOST;port=$DB_PORT', '$DB_USERNAME', '$DB_PASSWORD');
        \$pdo->exec('CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    "
fi

# Correr deploy como jaguelweb (no como root)
su jaguelweb -c "sh /var/www/docker/deploy.sh"

# Arrancar php-fpm como root (php-fpm hace su propio drop a www-data según su config)
exec "$@"