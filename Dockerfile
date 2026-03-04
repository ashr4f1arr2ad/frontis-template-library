# --- Build stage: Node.js assets ---
FROM node:22-alpine AS asset-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# --- App stage: PHP environment ---
FROM php:8.4-fpm-alpine

# Install system dependencies + build deps
RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    libxml2-dev \
    libpng-dev \
    nginx \
    supervisor \
    bash \
    mysql-client \
    $PHPIZE_DEPS

# Install PHP extensions + Redis
RUN docker-php-ext-install \
        intl \
        pdo_mysql \
        bcmath \
        zip \
        opcache \
        mbstring \
        exif \
        pcntl \
        gd \
        xml \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Configure PHP-FPM
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy Nginx config
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Set working directory
WORKDIR /var/www/html

# Copy app code
COPY . .

# Copy compiled assets from node stage
COPY --from=asset-builder /app/public/build ./public/build

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Prep folders
RUN mkdir -p storage/framework/{sessions,views,cache} bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Entrypoint/Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

# Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
