# central api server

### Developers
- 김민근 (kevin@mingeun.com)
- 이정민 (skile@team-crescendo.me)

### Server Architecture
- AWS EC2

### Plan

### Installation
- Ubuntu 기준

1. Update Check and Timezone
```bash
$ sudo apt-get update
$ sudo apt-get upgrade

$ dpkg-reconfigure tzdata
$ tzselect
4 - 23 - 1 

# ~/.profile
TZ='Asia/Seoul'; export TZ 

$ source ~/.profile
```

2. PHP 7.1 Install
```bash
$ sudo add-apt-repository ppa:ondrej/php 
$ sudo apt-get update
$ sudo apt-get install php7.1 php7.1-mcrypt php7.1-xml php7.1-gd php7.1-opcache php7.1-mbstring php7.1-curl
```

3. Apache Install (Apache 와 PHP를 연결하기 위해 libapache2-mod-php7.1 패키지를 설치해야함)
```bash
$ sudo apt-get install apache2 libapache2-mod-php7.1
```

4. Laravel Install
```bash
$ curl -sS https://getcomposer.org/installer | php
$ sudo mv composer.phar /usr/local/bin/composer
```

5. Lets Encrypt Install
```bash
$ sudo apt-get update
$ sudo apt-get install software-properties-common
$ sudo add-apt-repository ppa:certbot/certbot
$ sudo apt-get update
$ sudo apt-get install python-certbot-apache
$ sudo certbot --apache
$ sudo certbot --expand -d domain.com
$ sudo service apache2 restart
```

6. MySQL & PHPMyAdmin Install
```bash
$ sudo apt-get install mysql-server php7.1-mysql
$ service mysql restart
$ service apache2 restart
$ sudo apt-get install phpmyadmin php-mbstring php-gettext
$ sudo phpenmod mcrypt
$ sudo phpenmod mbstring
$ service apache2 restart

```

7. Clone
```
$ git clone https://github.com/Team-Crescendo/laravel-central-api
$ composer global require "laravel/installer"
$ composer install
$ php artisan key:generate
$ chmod -R 775 storage bootstrap/cache storage/framework storage/logs
$ sudo chown -R $USER:www-data storage
$ sudo chown -R $USER:www-data bootstrap/cache
$ cp .env.example .env
```