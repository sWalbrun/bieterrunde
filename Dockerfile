FROM dunglas/frankenphp:php8.3

COPY . /app/public

RUN apt-get update && apt-get install -y default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# The MariaDB client (>= 11.4) verifies the server certificate by default,
# which fails against the mysql container's self-signed one. TLS is not
# needed on the internal docker network — e.g. `artisan migrate` loads the
# schema dump through this client.
RUN mkdir -p /etc/mysql/conf.d \
    && printf '[client]\nskip-ssl\n' > /etc/mysql/conf.d/99-skip-ssl.cnf

RUN install-php-extensions \
    pdo_mysql \
    zip \
    intl \
    xdebug
