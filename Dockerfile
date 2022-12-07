FROM php:7.4-cli

RUN apt-get update && apt-get install -y git zip

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
COPY --from=composer:2.4.4 /usr/bin/composer /usr/bin/composer