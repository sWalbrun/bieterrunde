#!/bin/sh

if [ ! -f ".env" ];
then
    echo "Copying .env.example"
    cp .env.example .env
fi;

if [ ! -f ".env.testing" ];
then
    echo "Copying .env.testing.example"
    cp .env.testing.example .env.testing
fi;

if [ "$1" = '--fresh' ]
then
    REFRESH='y'
else
    echo "Do you want to fresh the whole setup? All dependencies and databases will be deleted! (n/y)"
    read -r REFRESH
fi;

sudo chown -R "$(id -u)":"$(id -g)" .

docker compose stop

# We have to export the REFRESH to access it via the docker compose
export REFRESH="$REFRESH"
docker compose up -d --build

# #2 Double Tap - Just to make sure
sudo chown -R "$(id -u)":"$(id -g)" .

