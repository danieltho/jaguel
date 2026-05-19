#!/usr/bin/env sh
set -e

cd /var/www

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

sh /var/www/docker/deploy.sh

exec "$@"
