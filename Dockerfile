FROM php:8.3-cli-alpine3.22

# NGINX Unit
RUN apk add --no-cache \
    bash \
    unit \
    unit-php83

# PHP ext
RUN apk add --no-cache \
    php83-session \
    php83-pdo_mysql \
    php83-openssl \
    php83-mbstring \
    php83-tokenizer \
    php83-opcache

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# COPY
RUN mkdir -p /var/www/html
WORKDIR /var/www/html
COPY qsiq qsiq
COPY app app
COPY bootstrap bootstrap
COPY config config
COPY public public
COPY resources resources
COPY routes routes
COPY storage storage
COPY [ \
  "artisan", \
  "composer.json", \
  "composer.lock", \
  "entrypoint.sh", \
  "unit-config.json", \
  "./" \
]

RUN chown -R unit:unit /var/www/html

# install
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Unit config
COPY unit-config.json /etc/unit/unit-config.json

# entrypoint.sh
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/entrypoint.sh"]
