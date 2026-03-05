FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    zip \
    git \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_pgsql pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite

COPY apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html

RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd