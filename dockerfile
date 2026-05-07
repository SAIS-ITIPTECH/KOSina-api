FROM dunglas/frankenphp:1-php8.4-bookworm

# 1. Install system dependencies (libpq-dev is required for Postgres)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. Use the built-in helper to install extensions (cleaner than docker-php-ext-install)
RUN install-php-extensions \
    pdo_mysql \
    pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 3. Copy only composer files first for better caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 4. Copy the rest of the app
COPY . .

# 5. Load your custom ini
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

RUN php -v

CMD ["frankenphp", "php-server", "--listen", ":8080", "--disable-auto-https"]