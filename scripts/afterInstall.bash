#!/usr/bin/env bash

sudo rsync --delete-before --verbose --archive /var/www/release/ /var/www/laravel-central-api/ > /var/log/deploy.log

cd /var/www/laravel-central-api
sudo php artisan aws:codedeploy
sudo cp -r /var/www/release-files/vendor /var/www/laravel-central-api
sudo cp /var/www/release-files/.env /var/www/laravel-central-api
sudo cp /var/www/release-files/Controller.php /var/www/laravel-central-api/app/Http/Controllers
sudo php artisan key:generate
sudo php artisan config:cache
sudo chmod -R 777 storage bootstrap/cache storage/framework storage/logs
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache
sudo php artisan queue:listen database --queue=xsolla-recharge --delay=600 --timeout=300 --tries=2 &

if [ -d /var/www/release ]; then
    sudo rm -rf /var/www/release
fi
