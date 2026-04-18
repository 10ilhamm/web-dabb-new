FROM php:8.3-fpm
WORKDIR /var/www

# 1. Install system dependencies & libraries
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    git \
    unzip \
    libzip-dev

# 2. Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip

# 3. Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 4. KUNCI PERBAIKAN: Copy seluruh source code Anda SEBELUM menjalankan Composer
# Ini memastikan file 'artisan' dan struktur Laravel sudah ada saat dibutuhkan
COPY . .

# 5. Jalankan composer install (dengan bypass memory limit)
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction --prefer-dist --optimize-autoloader

CMD ["php-fpm"]
