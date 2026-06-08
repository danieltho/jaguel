#!/usr/bin/env sh
# Primera emisión del certificado Let's Encrypt (método webroot).
# Ejecutar UNA sola vez en el servidor, después del primer despliegue.
# Las renovaciones posteriores las hace el servicio "certbot" automáticamente.
#
#   Uso:   sh docker/init-letsencrypt.sh
#   Prueba sin gastar rate-limit:   STAGING=1 sh docker/init-letsencrypt.sh
set -e

COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

# Lee DOMAIN y CERTBOT_EMAIL del .env
DOMAIN=$(grep -E '^DOMAIN=' .env | cut -d= -f2- | tr -d '"'\' | tr -d ' ')
EMAIL=$(grep -E '^CERTBOT_EMAIL=' .env | cut -d= -f2- | tr -d '"'\' | tr -d ' ')
STAGING=${STAGING:-0}

[ -z "$DOMAIN" ] && { echo "ERROR: falta DOMAIN= en .env"; exit 1; }
[ -z "$EMAIL" ]  && { echo "ERROR: falta CERTBOT_EMAIL= en .env"; exit 1; }

cert_path="/etc/letsencrypt/live/$DOMAIN"

echo "### 1/5 Descargando parámetros TLS recomendados ..."
$COMPOSE run --rm --entrypoint "\
  sh -c 'mkdir -p /etc/letsencrypt && \
  wget -qO /etc/letsencrypt/options-ssl-nginx.conf https://raw.githubusercontent.com/certbot/certbot/main/certbot-nginx/src/certbot_nginx/_internal/tls_configs/options-ssl-nginx.conf && \
  openssl dhparam -out /etc/letsencrypt/ssl-dhparams.pem 2048'" certbot

echo "### 2/5 Creando certificado dummy (para que nginx pueda arrancar) ..."
$COMPOSE run --rm --entrypoint "\
  sh -c 'mkdir -p $cert_path && \
  openssl req -x509 -nodes -newkey rsa:2048 -days 1 \
    -keyout $cert_path/privkey.pem \
    -out    $cert_path/fullchain.pem \
    -subj /CN=localhost'" certbot

echo "### 3/5 Arrancando nginx ..."
$COMPOSE up -d nginx

echo "### 4/5 Borrando dummy y pidiendo el certificado real ..."
$COMPOSE run --rm --entrypoint "\
  rm -rf /etc/letsencrypt/live/$DOMAIN \
         /etc/letsencrypt/archive/$DOMAIN \
         /etc/letsencrypt/renewal/$DOMAIN.conf" certbot

staging_arg=""
[ "$STAGING" != "0" ] && staging_arg="--staging"

# Para incluir www agregá: -d www.$DOMAIN  (y sumá www al server_name del template)
$COMPOSE run --rm --entrypoint "\
  certbot certonly --webroot -w /var/www/certbot \
    $staging_arg \
    --email $EMAIL \
    -d $DOMAIN \
    --rsa-key-size 4096 \
    --agree-tos --no-eff-email --force-renewal" certbot

echo "### 5/5 Recargando nginx con el certificado real ..."
$COMPOSE exec nginx nginx -s reload

echo ""
echo "Listo. SSL activo en https://$DOMAIN"
