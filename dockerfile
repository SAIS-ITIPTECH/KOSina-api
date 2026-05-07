FROM dunglas/frankenphp:php8.4.20-bookworm

# Install git and composer
RUN apt-get update && apt-get install -y git unzip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP extensions
RUN install-php-extensions pdo pdo_mysql

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

COPY php.ini /usr/local/etc/php/conf.d/custom.ini