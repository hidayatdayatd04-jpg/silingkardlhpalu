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

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# --- Build Stage ---
FROM base AS build

# Install npm dependencies first (for caching)
COPY package.json package-lock.json* ./
RUN npm ci

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

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
