FROM dunglas/frankenphp:1.3-php8.3-bookworm

WORKDIR /app

COPY composer.json composer.lock ./

RUN apt-get update \
    && apt-get install -y locales apt-utils git libicu-dev g++ libpng-dev libxml2-dev libzip-dev libonig-dev libxslt-dev unzip libpq-dev librabbitmq-dev \
    && rm -rf /var/lib/apt/lists/*

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen \
    && echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen \
    && locale-gen

RUN curl -sS https://getcomposer.org/installer | php -- \
    && mv composer.phar /usr/local/bin/composer

RUN composer install --no-scripts --no-interaction

COPY . .

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

RUN install-php-extensions pdo_pgsql redis amqp

EXPOSE 80

CMD ["frankenphp", "php-server", "-r", "/app/public"]