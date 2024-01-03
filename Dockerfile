FROM dunglas/frankenphp

COPY . /app/public

RUN install-php-extensions \
    pdo_mysql \
    zip
