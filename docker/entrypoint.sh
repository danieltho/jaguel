#!/usr/bin/env sh
set -e

cd /var/www

echo "Esperando a la base de datos..."
until php -r "try { new PDO('mysql:host=' . (getenv('DB_HOST') ?: 'db') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME') ?: 'laravel', getenv('DB_PASSWORD') ?: 'secret'); exit(0); } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
    sleep 2
done
echo "Base de datos lista."

sh /var/www/docker/deploy.sh

exec "$@"
