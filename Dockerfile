FROM php:8.2-fpm AS base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev libpq-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions for Laravel
RUN docker-php-ext-install opcache

# Install Node.js 22 (Vite 7 butuh >=20.19)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest \
    && node -v && npm -v

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# --- Build Stage ---
FROM base AS build

# Install npm dependencies first (for caching)
# --include=optional wajib untuk @tailwindcss/oxide native binding
COPY package.json package-lock.json* ./
RUN npm ci --include=optional || npm install --include=optional

# Install composer dependencies
COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy application code
COPY . .

# Build frontend assets
RUN npm run build

# Run post-autoload-dump scripts
RUN composer dump-autoload --optimize

# --- Production Stage ---
FROM base AS production

# NB: user www-data sudah tersedia di image php:8.2-fpm (uid 33),
# tidak perlu dibuat ulang.

# Copy built application
COPY --from=build --chown=www-data:www-data /var/www /var/www

# Set working directory
WORKDIR /var/www

# Create storage directories
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions \
    storage/framework/views storage/app/public storage/app/private bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# PHP-FPM configuration
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

# Samakan batas upload PHP dengan batas aplikasi (restore backup maks 500MB,
# file GIS 50MB, dokumen/foto 5MB). Default php.ini-production hanya 2M.
RUN printf 'upload_max_filesize = 512M\npost_max_size = 512M\n' \
    > /usr/local/etc/php/conf.d/uploads.ini

# Railway: expose 8080 dan jalankan artisan serve (bukan php-fpm 9000)
# php-fpm butuh nginx terpisah, di Railway cukup artisan serve agar 502 hilang
# Queue worker & scheduler dijalankan sebagai background loop dengan auto-restart
# agar backup/restore (timeout 1900) tidak stuck 0% saat worker mati.
EXPOSE 8080
CMD ["sh", "-c", "echo \"=== Starting on PORT=${PORT:-8080} ===\"; php artisan migrate --force --no-interaction || echo \"migrate failed, continue\"; php artisan config:clear; php artisan storage:link || true; echo \"=== Starting queue worker (tries=1 timeout=1900) in background ===\"; (while true; do php artisan queue:work --queue=default --sleep=3 --tries=1 --timeout=1900 --max-time=3600 -v; echo \"queue worker exited code $? - restarting in 5s\"; sleep 5; done) & echo \"=== Starting scheduler in background ===\"; (while true; do php artisan schedule:run --verbose --no-interaction; sleep 60; done) & exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
