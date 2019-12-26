#!/usr/bin/env bash

# I want to make sure that the directory is clean and has nothing left over from
# previous deployments. The servers auto scale so the directory may or may not
# exist.

if [ -d /var/www/release ]; then
    sudo rm -rf /var/www/release
fi

sudo cp -r /var/www/laravel-central-api/storage/backups /var/www/release-files
sudo cp -r /var/www/laravel-central-api/storage/requests /var/www/release-files
sudo cp -r /var/www/laravel-central-api/storage/logs /var/www/release-files
sudo mkdir -vp /var/www/release
