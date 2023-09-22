FROM composer:latest as builder

WORKDIR /app

COPY bin /app/bin
COPY config /app/config
COPY migrations /app/migrations
COPY public /app/public
COPY src /app/src
COPY templates /app/templates
COPY .env /app/.env
COPY composer.json /app/composer.json

RUN composer install --no-dev --optimize-autoloader

FROM alpine:3.18

RUN apk upgrade --no-cache
RUN apk add --no-cache \
    bash \
    caddy \
    php82 \
    php82-ctype \
    php82-dom \
    php82-fpm \
    php82-iconv \
    php82-pdo_sqlite \
    php82-session \
    php82-tokenizer \
    php82-simplexml \
    php82-openssl \
    supervisor

RUN apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/v3.13/community/ gnu-libiconv=1.15-r3
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php

COPY --from=builder /app /opt/app

COPY docker/entrypoint.sh /opt/app/entrypoint.sh
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile
COPY docker/supvervisord/supervisord.ini /etc/supervisor.d/supervisord.ini
COPY docker/supvervisord/caddy.ini /etc/supervisor.d/caddy.ini
COPY docker/supvervisord/php-fpm.ini /etc/supervisor.d/php-fpm.ini

ENTRYPOINT ["/opt/app/entrypoint.sh"]

CMD ["supervisord", "-n", "-c", "/etc/supervisord.conf"]
