#!/bin/sh

if [ "$REFRESH" = "y" ];
then
    echo "Deleting the vendor/ directory"
    rm -rf vendor/
fi

echo "Installing dependencies"
composer config gitlab-token.gitlab.consolinno-it.de "$GITLAB_TOKEN"
composer i

php artisan optimize:clear

if [ "$REFRESH" = "y" ];
then
    echo "Migrating and seeding"
    php artisan key:generate --force
    php artisan migrate:fresh --seed
    php artisan migrate:fresh --env=testing
    php artisan tenants:run db:seed
else
    echo "Migrating without seeding"
    php artisan migrate
    php artisan tenants:run migrate
fi
