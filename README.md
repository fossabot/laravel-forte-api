# 팀 크레센도 중앙 API

`팀 크레센도` 중앙 API 서버(이하 “API 서버”)는 팀 크레센도 홈페이지(이하 “홈페이지”), 결제 대행사(“엑솔라”), 디스코드 봇(이하 “봇”) 사이에서 통용되는 데이터를 통합해서 관리하는 API 서버입니다.

### Developers
- 김민근 ([@getsolaris](https://github.com/getsolaris)) kevin@mingeun.com
- 이정민 ([@GBS-Skile](https://github.com/GBS-Skile)) skile@team-crescendo.me

### relationship with Xsolla

엑솔라는 홈페이지에 결제 모듈을 제공하여, 홈페이지에 가입한 사용자가 상품(가상 화폐/가상 아이템)을 구매할 수 있도록 합니다. 그러나 사용자 데이터(사용자의 보유 가상 화폐/가상 아이템 목록 등)는 엑솔라 서버가 아닌 API 서버에 저장됩니다. 따라서 본 API 서버는 사용자 유효성 검사 등 결제 대행 과정에서 필요한 엑솔라의 요청에 응답합니다. 또한 엑솔라에서 결제가 완료되었을 경우 그 정보를 수신하여 사용자 데이터를 갱신합니다. 이 과정은 웹훅 방식으로 이루어집니다.
<br>
 또한 엑솔라는 “스토어” 기능을 통해 팀 크레센도에 필요한 가상 화폐(“포인트”), 가상 아이템의 목록을 관리합니다. 필요한 경우 엑솔라에서 제공하는 API를 호출하여 가상 화폐 패키지 목록 등의 관련 정보를 불러올 수 있습니다.
 
### relationship with Website

홈페이지는 사용자와 엑솔라 사이를 매개하여 실제로 결제가 이루어지는 공간입니다. 사용자는 홈페이지에 가입하고 필요에 따라 Discord ID를 연동할 수 있습니다. API 서버는 홈페이지에 사용자 계정 정보(사용자 UUID, 연동된 디스코드 ID, 보유 포인트/가상 아이템, 구매 기록)를 제공합니다. 홈페이지는 API 서버에 새로운 사용자를 생성하거나 기존 사용자를 삭제, Discord 연동 정보를 수정하는 요청을 보낼 수 있습니다.
<br>
현금으로 가상 화폐 또는 가상 아이템을 구매하는 과정은 엑솔라가 대행하지만, 가상 화폐로 가상 아이템을 구매하는 과정은 홈페이지에서 API 서버에 요청을 보내 수행할 수 있습니다.

### relationship with Bot

봇은 사용자가 구매한 가상 아이템이 실제로 사용되는 장소입니다. 현재 봇마다(SklleBot / 배추봇) 독자적으로 아이템 DB를 구축하고 있기 때문에, API 서버의 아이템 DB와 각각의 봇의 아이템 DB를 동기화시키는 과정이 필요합니다. 봇이 API 서버에 아이템 목록을 요청하면 갱신되지 않은 아이템(홈페이지를 통해 새로 구매 요청이 들어온 아이템 목록)으로 응답하는 방식으로 상호작용합니다. 이때 API 서버에서 먼저 봇에게 웹훅 등의 방식으로 알림을 보내지 않는다는 점에 유의해야 합니다.
<br>
또 필요에 따라 포인트를 사용하여 아이템을 구매하는 과정을 홈페이지를 거치지 않고 봇에서 바로 진행할 수도 있습니다.


### Server Architecture
- AWS EC2

### Links
- URL
    - api endpoint: [https://crescendo.mingeun.com/api/v1](https://crescendo.mingeun.com/api/v1)
    - xsolla endpoint: [https://crescendo.mingeun.com/api/xsolla](https://crescendo.mingeun.com/api/xsolla)
- Docs
    - API Document: [https://crescendo.mingeun.com/api/documentation](https://crescendo.mingeun.com/api/documentation)

### Commit Rules
default commit
- Added
- Modify
- Removed

issue tracking commit
- Feature #1 - Title
- Fix #1 - Title

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
