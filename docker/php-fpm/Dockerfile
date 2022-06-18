FROM php:8.1-fpm

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions amqp mongodb pdo_mysql @composer

# Copy existing app directory
COPY . /var/www
WORKDIR /var/www

# Run composer
RUN composer install --no-interaction

EXPOSE 8080

CMD ["php-fpm"]