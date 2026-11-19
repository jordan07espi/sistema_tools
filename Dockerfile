FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libicu-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql zip intl mbstring gd xml \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY composer.json ./
RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader
COPY . .
COPY Config/uploads.ini /usr/local/etc/php/conf.d/zz-uploads.ini
RUN mkdir -p storage/uploads storage/exports \
    && chown -R www-data:www-data storage \
    && sed -ri 's!DocumentRoot /var/www/html!DocumentRoot /var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && printf 'upload_max_filesize=30M\npost_max_size=32M\nmemory_limit=1024M\nmax_execution_time=420\nsession.use_strict_mode=1\nexpose_php=Off\n' >/usr/local/etc/php/conf.d/ejecuttrans.ini
