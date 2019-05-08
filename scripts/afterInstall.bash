#!/usr/bin/env bash

sudo rsync --delete-before --verbose --archive /var/www/release/ /var/www/laravel-central-api/ > /var/log/deploy.log

cd /var/www/laravel-central-api
sudo composer install
sudo cp /var/www/release-files/.env /var/www/laravel-central-api
sudo cp /var/www/release-files/Controller.php /var/www/laravel-central-api/app/Http/Controllers
sudo php artisan key:generate
sudo php artisan config:cache
sudo chmod -R 775 storage bootstrap/cache storage/framework storage/logs
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache

if [ -d /var/www/release ]; then
    sudo rm -rf /var/www/release
fi
