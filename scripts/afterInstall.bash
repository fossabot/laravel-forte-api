#!/usr/bin/env bash

sudo rsync --delete-before --verbose --archive /var/www/release/ /var/www/laravel-central-api/ > /var/log/deploy.log

cd /var/www/laravel-central-api
sudo php artisan aws:codedeploy starting
sudo composer install
sudo cp /var/www/release-files/.env /var/www/laravel-central-api
sudo cp /var/www/release-files/Controller.php /var/www/laravel-central-api/app/Http/Controllers
sudo cp /var/www/release-files/requests /var/www/laravel-central-api/storage/requests
sudo cp /var/www/release-files/backups /var/www/laravel-central-api/storage/backups
sudo php artisan key:generate
sudo php artisan config:cache
sudo chmod -R 777 storage bootstrap/cache storage/framework storage/logs
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache
sudo pkill -9 -ef bot.php
nohup php bot.php > /dev/null 2> /dev/null < /dev/null &
sudo php artisan aws:codedeploy finish

if [ -d /var/www/release ]; then
    sudo rm -rf /var/www/release
fi
