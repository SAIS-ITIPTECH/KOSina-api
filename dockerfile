FROM dunglas/frankenphp:php8.4.20-bookworm

RUN apt-get update && apt-get install -y git unzip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader
COPY php.ini /usr/local/etc/php/conf.d/custom.ini