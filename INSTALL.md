## Installation
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

2. PHP 7.4 Install
```bash
$ sudo add-apt-repository ppa:ondrej/php 
$ sudo apt-get update
$ sudo apt install php7.4 php7.4-common php7.4-cli
$ sudo apt install php7.4-mysqlnd php7.4-curl php7.4-json php7.4-xml php7.4-gd php7.4-mbstring php7.4-intl php7.4-bcmath php7.4-bz2 php7.4-readline php7.4-zip
```

3. Apache Install (Apache 와 PHP를 연결하기 위해 libapache2-mod-php7.4 패키지를 설치해야함)
```bash
$ sudo apt-get install apache2 libapache2-mod-php7.4
# $ sudo apt install php7.4-fpm

# enable PHP 7.4 and restart Apache2
$ sudo a2enmod php7.4

# If old PHP Version
$ sudo apt purge php7.3 libapache2-mod-php7.3
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

6. MySQL (Deprecated: use AWS RDS) & only PHPMyAdmin Install
```bash
$ sudo apt-get install mysql-server php7.3-mysql
$ service mysql restart
$ service apache2 restart
$ sudo apt-get install phpmyadmin php-mbstring php-gettext
$ sudo phpenmod mcrypt
$ sudo phpenmod mbstring
$ service apache2 restart
$ cd /etc/phpmyadmin/
$ sudo vi config-db.php // edit dbname, dbpass, dbserver
$ cd /usr/share/phpmyadmin
$ sudo cp config.sample.inc.php config.inc.php
```

6-1. PHPMyAdmin RDS Setup
```php
// /usr/share/phpmyadmin/config.inc.php
$i++;
$cfg['Servers'][$i]['host'] = 'RDS HOST';
$cfg['Servers'][$i]['port'] = '3306';
$cfg['Servers'][$i]['socket'] = '';
$cfg['Servers'][$i]['extension'] = 'mysql';
$cfg['Servers'][$i]['compress'] = FALSE;
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['AllowNoPassword'] = false;
$cfg['Servers'][$i]['connect_type'] = 'tcp';
$cfg['Servers'][$i]['extension'] = 'mysqli';
```

7. Clone
```
$ git clone https://github.com/team-crescendo/laravel-forte
$ composer global require "laravel/installer"
$ composer install
$ chmod -R 775 storage bootstrap/cache storage/framework storage/logs
$ sudo chown -R $USER:www-data storage
$ sudo chown -R $USER:www-data bootstrap/cache
$ cp .env.example .env
$ php artisan key:generate
```

8. MySQL Setting (Deprecated: use AWS RDS)
```bash
$ sudo mysql -uroot -p
mysql> create user 'sqluser'@'%' identified by 'password';
mysql> grant all privileges on *.* to sqluser@'%' identified by 'password' with grant option;
mysql> FLUSH PRIVILEGES;
```

9. Apache2 Setting
```bash
# /etc/apache2/apache2.conf
<Directory /var/www/>
    AllowOverride All
</Directory>

# /etc/apache2/sites-enabled
DocumentRoot /var/www/project/public
```

10. Laravel Scheduler
```bash
# cron
$ crontab -e

* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

11. Forte Bot
```bash
$ cd ~/larabot
$ pyenv activate lara
$ nohup python src/bot.py &
```

12. Redis
```bash
$ sudo apt-get install build-essential tcl
$ sudo apt-get install redis-server
$ sudo systemctl enable redis-server.service
$ redis-server --version
$ vmstat -s
$ sudo vi /etc/redis/redis.conf

# redis.conf
maxmemory 1g
maxmemory-policy allkeys-lru

$ sudo systemctl restart redis-server.service
$ sudo apt-get install php-redis

$ redis-cli
127.0.0.1:6379> ping
PONG
127.0.0.1:6379>
```

13. AWS CodeDeploy
```bash
# ruby install
sudo apt-get install ruby

ruby --version
```

13. PHP Insight
```bash
$ composer require nunomaduro/phpinsights --dev
$ php artisan vendor:publish --provider="NunoMaduro\PhpInsights\Application\Adapters\Laravel\InsightsServiceProvider"

$ php artisan insights
```
