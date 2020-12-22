FROM alpine:edge

# Install packages
RUN apk add --no-cache php7 php7-pear \
    php7-pdo php7-pdo_mysql php7-pdo_pgsql php7-pdo_sqlite php7-mysqli \
    php7-mbstring php7-tokenizer php7-xml php7-simplexml \
    php7-zip php7-opcache php7-iconv php7-intl php7-pcntl \
    php7-json php7-gd php7-ctype php7-phar \
    php7-redis php7-pecl-apcu \
    php7-memcached php7-pecl-igbinary \
    php7-exif php7-curl php7-bcmath php7-dom php7-fileinfo php7-xmlwriter \
    openssl-dev supervisor curl tzdata iputils

RUN apk add --no-cache -X http://dl-cdn.alpinelinux.org/alpine/edge/testing  \
    php7-pecl-mongodb php7-pecl-swoole

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# Clear lib not used & clean apk cache
# RUN apk del build-base
RUN rm -f /var/cache/apk/*

# Configure PHP modules
COPY ./deploy/php/jwt.so.ext /usr/lib/php7/modules/jwt.so
COPY ./deploy/php/php.ini /etc/php7/conf.d/custom.ini

# Configure supervisord
COPY ./deploy/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Setup document root
RUN mkdir -p /var/www/app

# Copy all file
WORKDIR /var/www/app
COPY . /var/www/app/

# keep vendor uptodate
RUN cd /var/www/app && composer install --no-dev --no-cache

# run with nobody user
RUN chown -R nobody.nobody /var/www/app && \
    chown -R nobody.nobody /run

# Switch to use a non-root user from here on
USER nobody

# Expose the port app is reachable on
EXPOSE 8080

# Let supervisord start swoole app
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/ping
