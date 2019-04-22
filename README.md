# 팀 크레센도 중앙 API

`팀 크레센도` 중앙 API 서버(이하 “API 서버”)는 팀 크레센도 홈페이지(이하 “홈페이지”), 결제 대행사(“엑솔라”), 디스코드 봇(이하 “봇”) 사이에서 통용되는 데이터를 통합해서 관리하는 API 서버입니다.

[![Build Status](https://travis-ci.com/team-crescendo/laravel-forte.svg?branch=master)](https://travis-ci.com/team-crescendo/laravel-forte)

## Developers
- 김민근 ([@getsolaris](https://github.com/getsolaris)) kevin@mingeun.com
- 이정민 ([@GBS-Skile](https://github.com/GBS-Skile)) skile@team-crescendo.me

## relationship with Xsolla

엑솔라는 홈페이지에 결제 모듈을 제공하여, 홈페이지에 가입한 사용자가 상품(가상 화폐/가상 아이템)을 구매할 수 있도록 합니다. 그러나 사용자 데이터(사용자의 보유 가상 화폐/가상 아이템 목록 등)는 엑솔라 서버가 아닌 API 서버에 저장됩니다. 따라서 본 API 서버는 사용자 유효성 검사 등 결제 대행 과정에서 필요한 엑솔라의 요청에 응답합니다. 또한 엑솔라에서 결제가 완료되었을 경우 그 정보를 수신하여 사용자 데이터를 갱신합니다. 이 과정은 웹훅 방식으로 이루어집니다.
<br>
 또한 엑솔라는 “스토어” 기능을 통해 팀 크레센도에 필요한 가상 화폐(“포인트”), 가상 아이템의 목록을 관리합니다. 필요한 경우 엑솔라에서 제공하는 API를 호출하여 가상 화폐 패키지 목록 등의 관련 정보를 불러올 수 있습니다.
 
## relationship with Website

홈페이지는 사용자와 엑솔라 사이를 매개하여 실제로 결제가 이루어지는 공간입니다. 사용자는 홈페이지에 가입하고 필요에 따라 Discord ID를 연동할 수 있습니다. API 서버는 홈페이지에 사용자 계정 정보(사용자 UUID, 연동된 디스코드 ID, 보유 포인트/가상 아이템, 구매 기록)를 제공합니다. 홈페이지는 API 서버에 새로운 사용자를 생성하거나 기존 사용자를 삭제, Discord 연동 정보를 수정하는 요청을 보낼 수 있습니다.
<br>
현금으로 가상 화폐 또는 가상 아이템을 구매하는 과정은 엑솔라가 대행하지만, 가상 화폐로 가상 아이템을 구매하는 과정은 홈페이지에서 API 서버에 요청을 보내 수행할 수 있습니다.

## relationship with Bot

봇은 사용자가 구매한 가상 아이템이 실제로 사용되는 장소입니다. 현재 봇마다(SklleBot / 배추봇) 독자적으로 아이템 DB를 구축하고 있기 때문에, API 서버의 아이템 DB와 각각의 봇의 아이템 DB를 동기화시키는 과정이 필요합니다. 봇이 API 서버에 아이템 목록을 요청하면 갱신되지 않은 아이템(홈페이지를 통해 새로 구매 요청이 들어온 아이템 목록)으로 응답하는 방식으로 상호작용합니다. 이때 API 서버에서 먼저 봇에게 웹훅 등의 방식으로 알림을 보내지 않는다는 점에 유의해야 합니다.
<br>
또 필요에 따라 포인트를 사용하여 아이템을 구매하는 과정을 홈페이지를 거치지 않고 봇에서 바로 진행할 수도 있습니다.


## Server Architecture
- AWS EC2 (Ubuntu 18.06)
- AWS Elastic Beanstalk
- Docker

## Links
- URL
    - api endpoint: [https://forte.team-crescendo.me/api/v1](https://forte.team-crescendo.me/api/v1)
    - xsolla endpoint: [https://forte.team-crescendo.me/api/xsolla](https://forte.team-crescendo.me/api/xsolla)
- Docs
    - API Document: [https://forte.team-crescendo.me/api/documentation](https://forte.team-crescendo.me/api/documentation)
    
## Directory Structure
중앙API 구조 (자세한 라라벨 관련 구조는 [여기](https://laravel.com/docs/5.8/structure)를 참고해주세요.)

- app
    - Http
        - Controllers (컨트롤러가 모여있습니다.)
            - Auth (컨트롤러 구조 안에 인증이 있으나 사용하지 않습니다.)
        - Middleware (사용자의 요청을 필터링합니다. 중앙API 토큰, Xsolla 인증 로직이 있습니다.)
        - Requests (해당 요청의 유효성을 판단합니다. 유저 회원가입 유효성 로직이 있습니다.)
    - Services (서비스 관련된 로직이 구현되어있습니다.)
- config (설정 파일이 모여있습니다. l5-swagger, sentry, xsolla)
- database
    - migrations (마이그레이션 파일이 모여있습니다.)
    - sql (DB 백업 파일이 있습니다.)
- routes (라우팅 관련된 파일이 모여있으며 오직 `api.php` 만 수정합니다.)

## Synchronization
Xsolla 와 Forte(중앙 API)의 아이템 동기화는 매일 02시에 진행됩니다. `app/Console/Kernal.php` (라라벨 스케쥴러) 참고해주세요.

## Error Tracking
에러트래킹은 `Sentry`를 사용합니다. 그외 디스코드 중앙API - 로깅 채널에서 확인 가능합니다.

## Architecture Concept
### Http Request
- 요청의 유효성을 판단합니다.

### Middleware
- 사용자의 요청을 필터링/검증 합니다.

### Controller
- 직접적으로 서비스 로직을 포함하지 않습니다.
    - 관련 로직은 서비스에서 작성합니다.
    
### Model
- 모델은 생각보다 무거운 존재가 됩니다.
- 가장 기본이 되는 객체
- 해당 객체가 가져야하는 역할 및 속성을 작성합니다.

### Services
- 컨트롤러에서 요청받은 로직을 서비스단에서 수행합니다.
- 데이터관련된 로직은 모델을 호출 후 처리합니다.

## Working Process
- 워킹 프로세스는 깃허브를 기반으로 진행합니다.
- 깃허브의 `Projects` 를 이용(Todo 기반 협업툴)하여 할일을 정리합니다.
- 할일은 프로젝트와 이슈를 생성합니다.

## Issue Information 
- 이슈 라벨
    - hotfix: 버그 발생 시 이슈 생성 후 해당 라벨을 추가합니다.
    - database: 데이터베이스 관련된 추가/오류는 해당 라벨을 추가합니다.
    - develop: 개발을 포함한 이슈에는 해당 라벨을 추가합니다.
    - doc: 문서를 수정할때 요청하는 라벨입니다.
    - feature: 신규 개발 시 해당 라벨을 추가합니다.
    - help: 다른 개발자에게 해당 이슈의 도움을 받고싶을 때 해당 라벨을 추가합니다.
    - question: 봇 개발자가 중앙API 개발자에게 질문 할때 해당 라벨을 추가합니다.
    - Test: 테스팅 작업시 해당 라벨을 추가합니다
    - xsolla: 엑솔라 관련 작업 시 해당 라벨을 추가합니다.
    
## Issue Tracking
이슈 트래킹은 `feature`(Feature) 와 `hotfix`(Fix) 라벨이 존재할 경우에만 적용됩니다.
<br>
- 사용 예) feature #2 - Issue Title
자세한 내용은 아래 `Commit Rules` 에서 계속됩니다.

## Commit Rules
default commit
- Added
- Modify
- Removed

issue tracking commit
- Feature #1 - Title
- Fix #1 - Title

## Lara (Forte Support Bot)
Lara 는 포르테 프로젝트를 도와주는 봇입니다. 
<br>
코어단은 `bot.php` 에 존재합니다.
<br>
기능
- `@Lara uptime` (서버 구동 시간)
- `@Lara xsolla:sync` (엑솔라와 포르테 아이템 동기화)
- `@Lara forte users` (포르테 유저 목록)
- `@Lara forte users <id>` (포르테 유저 검색)
- `@Lara forte items` (포르테 아이템 목록)
- `@Lara forte items <id>` (포르테 유저 보유중인 아이템 검색)
- `@Lara forte users ban <id>` (포르테 유저 정지)
- `@Lara forte users unban <id>` (포르테 유저 정지 해제)

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

2. PHP 7.3 Install
```bash
$ sudo add-apt-repository ppa:ondrej/php 
$ sudo apt-get update
# $ sudo apt-get install php7.1 php7.1-mcrypt php7.1-xml php7.1-gd php7.1-opcache php7.1-mbstring php7.1-curl php7.1-zip
$ sudo apt install php7.3 php7.3-common php7.3-cli
$ apt install php7.3-bcmath php7.3-bz2 php7.3-curl php7.3-gd php7.3-intl php7.3-json php7.3-mbstring php7.3-readline php7.3-xml php7.3-zip
```

3. Apache Install (Apache 와 PHP를 연결하기 위해 libapache2-mod-php7.1 패키지를 설치해야함)
```bash
# $ sudo apt-get install apache2 libapache2-mod-php7.1
$ sudo apt install php7.3-fpm
$ sudo apt install libapache2-mod-php7.3

# enable PHP 7.3 and restart Apache2
$ sudo a2enmod php7.3

# If old PHP Version
$ sudo apt purge php7.1 php7.1-common
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
$ sudo apt-get install mysql-server php7.3-mysql
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
$ chmod -R 775 storage bootstrap/cache storage/framework storage/logs
$ sudo chown -R $USER:www-data storage
$ sudo chown -R $USER:www-data bootstrap/cache
$ cp .env.example .env
$ php artisan key:generate
```

8. MySQL Setting
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
$ nohup php bot.php &
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
