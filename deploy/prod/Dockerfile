# FROM php:8.4-cli

# # Install dependencies
# RUN apt-get update && apt-get install -y \
# 	build-essential \
# 	libpng-dev \
# 	libjpeg62-turbo-dev \
# 	libfreetype6-dev \
# 	locales \
# 	zip \
# 	jpegoptim optipng pngquant gifsicle \
# 	vim \
# 	unzip \
# 	git \
# 	curl \
# 	libonig-dev \
# 	libzip-dev \
# 	libicu-dev \
# 	wget

# # Install extensions
# RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl intl sockets
# RUN docker-php-ext-configure gd --with-freetype --with-jpeg
# RUN docker-php-ext-install gd

# # Install Redis
# RUN pecl install redis && docker-php-ext-enable redis

# # Install Swoole
# RUN pecl install swoole && docker-php-ext-enable swoole

# # Install Composer
# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# # Set working directory
# WORKDIR /var/www

# # Copy application code
# COPY . /var/www

# # Install composer dependencies
# RUN composer install --no-dev --optimize-autoloader

# # Set permissions
# RUN chown -R www-data:www-data /var/www

# # Expose port 8000 (Octane default)
# EXPOSE 8000

# COPY deploy/prod/entrypoint.sh /usr/local/bin/entrypoint.sh
# RUN chmod +x /usr/local/bin/entrypoint.sh

# ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
# CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]
FROM php:8.4-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libzip-dev \
    libicu-dev \
    wget \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl intl sockets
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Install Redis
RUN pecl install redis && docker-php-ext-enable redis

# Install Swoole
RUN pecl install swoole && docker-php-ext-enable swoole

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application code
COPY . /var/www

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Install frontend dependencies & build assets
RUN npm install
RUN npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www

# Expose port 8000 (Octane default)
EXPOSE 8000

COPY deploy/prod/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]