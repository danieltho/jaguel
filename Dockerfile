FROM node:20-alpine AS assets
WORKDIR /var/www
COPY package.json package-lock.json* vite.config.* ./
COPY resources ./resources
COPY public ./public
RUN npm ci && npm run build

FROM php:8.3-fpm

# Arguments
ARG user=jaguelweb
ARG uid=1000

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    rsync \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    webp \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure intl
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www
RUN chown -R $user:$user /var/www

# Copy application files
COPY --chown=$user:$user . /var/www

# Copy built assets from node stage
COPY --from=assets --chown=$user:$user /var/www/public/build /var/www/public/build

# Install dependencies
USER $user
RUN composer install --no-interaction --optimize-autoloader

USER root
RUN chmod +x /var/www/docker/deploy.sh /var/www/docker/entrypoint.sh

# Snapshot del código para sincronizar en runtime
RUN cp -a /var/www /opt/app-image

EXPOSE 9000
ENTRYPOINT ["/var/www/docker/entrypoint.sh"]
CMD ["php-fpm"]
