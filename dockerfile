FROM dunglas/frankenphp:php8.4.20-bookworm

RUN install-php-extensions pdo pdo_mysql

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

COPY php.ini /usr/local/etc/php/conf.d/custom.ini