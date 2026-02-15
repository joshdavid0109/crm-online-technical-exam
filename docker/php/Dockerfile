# ==============================
# Stage 1 — Composer Builder
# ==============================
FROM php:8.2-cli-alpine AS builder

# Install system deps
RUN apk add --no-cache \
    git \
    unzip \
    curl \
    libzip-dev \
    oniguruma-dev \
    icu-dev

# Install PHP extensions needed by Laravel
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    intl \
    mbstring

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files first
COPY api/composer.json api/composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts

# ==============================
# Stage 2 — Production PHP
# ==============================
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    bash \
    curl \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    mysql-client

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    intl \
    mbstring

RUN docker-php-ext-enable opcache

# OPcache tuning
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www

COPY api/ .
COPY --from=builder /app/vendor ./vendor

RUN chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
