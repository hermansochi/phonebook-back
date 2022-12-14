FROM php:8.1-cli-alpine AS builder

#RUN apk add --no-cache postgresql-dev \
#    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
#    && docker-php-ext-install pdo_pgsql opcache

#RUN apk add --no-cache \
#      freetype \
#      libjpeg-turbo \
#      libpng \
#      freetype-dev \
#      libjpeg-turbo-dev \
#      libpng-dev \
#    && docker-php-ext-configure gd \
#      --with-freetype=/usr/include/ \
#      --with-jpeg=/usr/include/ \
#    && docker-php-ext-install -j$(nproc) gd \
#    && docker-php-ext-enable gd \
#    && apk del --no-cache \
#      freetype-dev \
#      libjpeg-turbo-dev \
#      libpng-dev \
#    && rm -rf /tmp/*

#RUN apk add --no-cache unzip postgresql-dev bash coreutils \
#    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
#    && docker-php-ext-install pdo_pgsql

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet

WORKDIR /app

COPY ./composer.json ./composer.lock ./
COPY ./.env.prod ./.env

RUN composer install --quiet --no-dev --prefer-dist --no-progress --optimize-autoloader --no-scripts \
    && rm -rf /root/.composer/cache

### CLI ###

FROM php:8.1-cli-alpine

RUN apk add --no-cache postgresql-dev bash coreutils \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_pgsql opcache

RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

COPY ./docker/common/php/conf.d /usr/local/etc/php/conf.d
COPY ./docker/production/php/conf.d /usr/local/etc/php/conf.d

COPY ./docker/common/wait-for-it.sh /usr/local/bin/wait-for-it
RUN chmod 555 /usr/local/bin/wait-for-it

RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/sh -D app 

WORKDIR /app

COPY --from=builder /app ./
COPY ./ ./
COPY ./.env.prod ./.env

RUN chmod -R ug+rwx storage bootstrap/cache

USER app