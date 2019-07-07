#!/usr/bin/env bash

# I want to make sure that the directory is clean and has nothing left over from
# previous deployments. The servers auto scale so the directory may or may not
# exist.

if [ -d /var/www/laravel-central-api/storage/requests ]; then
    sudo cp /var/www/laravel-central-api/storage/requests /var/www/release-files
fi

if [ -d /var/www/release ]; then
    sudo rm -rf /var/www/release
fi

sudo mkdir -vp /var/www/release
