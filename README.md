# 팀 크레센도 FORTE API v2

`팀 크레센도` 포르테 API 서버(이하 “API 서버”)는 팀 크레센도 홈페이지(이하 “홈페이지”), 결제 대행사(“엑솔라”), 디스코드 봇(이하 “봇”) 사이에서 통용되는 데이터를 통합해서 관리하는 API 서버입니다.

[![Build Status](https://travis-ci.com/team-crescendo/laravel-forte-api.svg?branch=refactoring/master)](https://travis-ci.com/team-crescendo/laravel-forte-api)
[![StyleCI](https://github.styleci.io/repos/169996002/shield)](https://github.styleci.io/repos/169996002)

## Infra Architecture
- AWS EC2 (Ubuntu 18.06)
- AWS Elastic Load Balancing
- ~~AWS RDS (Aurora via MySQL)~~
- AWS CodeDeploy
- AWS CloudWatch

## API Documentation
- [https://forte.team-crescendo.me/api/documentation](https://forte.team-crescendo.me/api/documentation)
