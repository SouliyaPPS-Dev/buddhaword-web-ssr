FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip curl libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libzip-dev libcurl4-openssl-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql mbstring curl gd intl zip exif \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

RUN echo '<VirtualHost *:8080>' > /etc/apache2/sites-available/000-default.conf \
    && echo '  DocumentRoot /var/www/html/public' >> /etc/apache2/sites-available/000-default.conf \
    && echo '  <Directory /var/www/html/public>' >> /etc/apache2/sites-available/000-default.conf \
    && echo '    Options -Indexes +FollowSymLinks' >> /etc/apache2/sites-available/000-default.conf \
    && echo '    AllowOverride All' >> /etc/apache2/sites-available/000-default.conf \
    && echo '    Require all granted' >> /etc/apache2/sites-available/000-default.conf \
    && echo '  </Directory>' >> /etc/apache2/sites-available/000-default.conf \
    && echo '</VirtualHost>' >> /etc/apache2/sites-available/000-default.conf

RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .

RUN mkdir -p storage/cache storage/tmp storage/tts && chmod -R 777 storage

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
