# ---------- Stage 1: Vendor (Composer) ----------
FROM php:8.3-cli-alpine AS vendor

ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /app

# Build deps & libs untuk ekstensi (MySQL + PostgreSQL + GD + ZIP)
RUN apk add --no-cache \
    git unzip icu-dev oniguruma-dev \
    libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
    mariadb-connector-c-dev postgresql-dev \
    libzip-dev zlib-dev zip \
    $PHPIZE_DEPS

# Ekstensi yang dibutuhkan saat composer (tidak perlu pgsql di tahap ini, tapi aman dipasang)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" intl mbstring gd zip \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql pdo_pgsql

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 1) Copy composer.* dulu agar cache efektif
COPY composer.json composer.lock ./
# 2) Install vendor tanpa scripts (belum ada artisan)
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts
# 3) Copy source code
COPY . .
# 4) Optimize autoload (tanpa scripts)
RUN composer dump-autoload -o --no-scripts



# ---------- Stage 2: Runtime (FrankenPHP + Caddy) ----------
FROM dunglas/frankenphp:1-php8.3-alpine

WORKDIR /app

# Runtime deps & ekstensi (kedua driver DB dipasang)
RUN apk add --no-cache \
    git unzip icu-dev oniguruma-dev \
    libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
    mariadb-connector-c-dev postgresql-dev \
    libzip-dev zlib-dev zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd intl mbstring opcache zip \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql pdo_pgsql

# (opsional) client tools jika perlu debugging
# RUN apk add --no-cache postgresql-client mariadb-client

# Copy app + vendor dari stage vendor
COPY --from=vendor /app /app

# Caddyfile (pastikan listen :8000 di file ini)
COPY ./deploy/Caddyfile /etc/caddy/Caddyfile

# Permission folder writable Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port yang dipakai Caddyfile (:8000)
EXPOSE 8000

# Jalankan FrankenPHP/Caddy
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]
