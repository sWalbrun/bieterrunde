FROM dunglas/frankenphp:php8.3

COPY . /app/public

RUN apt-get update && apt-get install -y default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    pdo_mysql \
    zip \
    intl \
    xdebug
