# ==============================
# Stage 1 — Composer Builder
# ==============================
FROM composer:2 AS builder

WORKDIR /app

# Copy only composer files first (better layer caching)
COPY api/composer.json api/composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --prefer-dist

# ==============================
# Stage 2 — Production PHP
# ==============================
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    mysql-client \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        zip \
        intl

# Enable OPcache (production performance)
RUN docker-php-ext-enable opcache

# Configure OPcache
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www

# Copy Laravel app
COPY api/ .

# Copy vendor from builder stage
COPY --from=builder /app/vendor ./vendor

# Set correct permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Switch to non-root user
USER www-data

EXPOSE 9000

CMD ["php-fpm"]
