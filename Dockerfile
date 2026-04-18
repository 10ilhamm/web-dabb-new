FROM php:8.3-fpm
WORKDIR /var/www

# 1. Install system dependencies & libraries (Tambahkan libzip-dev)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    git \
    unzip \
    libzip-dev

# 2. Install PHP extensions (Tambahkan zip)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip

# 3. Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 4. COPY HANYA file composer terlebih dahulu (Untuk optimasi Cache Docker)
COPY composer.json composer.lock ./

# 5. Jalankan composer install dengan bypass limit memory
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction --prefer-dist --optimize-autoloader

# 6. Copy sisa source code aplikasi
COPY . .

CMD ["php-fpm"]
