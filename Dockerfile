FROM php:8.3-fpm-bookworm

WORKDIR /var/www

# 1. Install system dependencies & libraries
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    git \
    unzip \
    zip \
    curl \
    && docker-php-ext-install -j$(nproc) bcmath intl mbstring xml

# 2. Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql gd zip

# 3. Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# 4. Copy seluruh source code SEBELUM composer install
COPY . .

# 5. Jalankan composer install (bypass memory limit, verbose untuk debug)
RUN COMPOSER_MEMORY_LIMIT=-1 composer install \
    --no-interaction --prefer-dist --optimize-autoloader -vvv

# 6. Set user permissions (Laravel storage/logs)
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

CMD ["php-fpm"]
