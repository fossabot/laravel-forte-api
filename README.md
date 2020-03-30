# 팀 크레센도 포르테 API

`팀 크레센도` 포르테 API 서버(이하 “API 서버”)는 팀 크레센도 홈페이지(이하 “홈페이지”), 결제 대행사(“엑솔라”), 디스코드 봇(이하 “봇”) 사이에서 통용되는 데이터를 통합해서 관리하는 API 서버입니다.

[![Build Status](https://travis-ci.com/team-crescendo/laravel-forte-api.svg?branch=master)](https://travis-ci.com/team-crescendo/laravel-forte-api)
[![StyleCI](https://github.styleci.io/repos/169996002/shield)](https://github.styleci.io/repos/169996002)

## Developers
- 김민근 ([@getsolaris](https://github.com/getsolaris)) mingeun.k.k@gmail.com
- 이정민 ([@GBS-Skile](https://github.com/GBS-Skile)) skile@team-crescendo.me
- 강희원 ([@Kang-Heewon](https://github.com/Kang-Heewon)) heewon.dev@gmail.com
- 모훈 ([@Mo-hun](https://github.com/Mo-hun)) twicemomo@kakao.com

## Infra Architecture
- AWS EC2 (Ubuntu 18.06)
- AWS Elastic Load Balancing
- ~~AWS RDS (Aurora via MySQL)~~
- AWS CodeDeploy
- AWS CloudWatch

## API Documentation
- [https://forte.team-crescendo.me/api/documentation](https://forte.team-crescendo.me/api/documentation)
    
## Directory Structure
Directory 구조 (자세한 라라벨 관련 구조는 [여기](https://laravel.com/docs/5.8/structure)를 참고해주세요.)

- app
    - Http
        - Controllers (컨트롤러가 모여있습니다.)
        - Middleware (사용자의 요청을 필터링합니다. API 토큰, Xsolla 인증 로직이 있습니다.)
        - Requests (해당 요청의 유효성을 판단합니다. 유저 회원가입 유효성 로직이 있습니다.)
        - Models (모델 파일이 모여있습니다.)
    - Services (서비스 관련된 로직이 구현되어있습니다.)
- config (설정 파일이 모여있습니다. l5-swagger, sentry, xsolla)
- database
    - migrations (마이그레이션 파일이 모여있습니다.)
- resources (views 파일이 포함되어있습니다.)
- routes (라우팅 관련된 파일이 모여있습니다.)

## Schedule
### Xsolla Synchronization
Xsolla 와 Forte(중앙 API)의 아이템 동기화는 매일 02시에 진행됩니다.

### DB Backup
DB 백업은 매일 자정에 진행됩니다.

### Clear RequestLog
RequestLog 백업 후 삭제는 매일 자정에 진행됩니다.

### STAFF Deposit point
매월 1일 00:30 에 진행됩니다.

## Error Tracking
API 에러트래킹은 `Sentry`를 사용합니다. 디스코드 로깅 채널에서 확인 가능합니다.

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

## Git Rules
### Issue Information 
- 이슈 라벨
    - hotfix: 버그 발생 시 이슈 생성 후 해당 라벨을 추가합니다.
    - database: 데이터베이스 관련된 추가/오류는 해당 라벨을 추가합니다.
    - develop: 개발을 포함한 이슈에는 해당 라벨을 추가합니다.
    - feature: 신규 개발 시 해당 라벨을 추가합니다.
    
### Commit Message Rules
1. 커밋 메시지는 *한글*로 작성합니다.
2. 메세지 본문에는 3자가 쉽게 이해할 수 있도록 풀어서 작성합니다.
3. 커밋 메시지에는 이모지를 넣지 않습니다.

### Commit Message Type
- 기능: 기능 추가, 삭제, 변경
- 버그: 버그 수정
- 리팩토링: 코드 리팩토링
- 형식: 코드 형식, 정렬, 주석 등의 변경 (코드 수정, 하지만 동작에 영향은 없는 코드)
- 테스트: 테스트 코드 추가, 삭제, 변경 등
- 문서: 문서 추가, 삭제, 변경
- 기타: 위 여섯가지에 해당되지 않는 모든 변경 (ex: 배포 스크립트 변경)을 포함

### Commit Message Configuration

```
Type: Title

ex) 기능: 포르테 스토어 서비스단 구현
ex) 기능: 포르테 API 요청에 관련된 로그 출력 기능 구현
ex) 형식: UserController store 메서드 주석 수정
ex) 문서: 리드미 인프라 부분 수정 
```

### Issue Tracking Commit Type
- 해결: 이슈 해결 시 사용
- 관련: 이슈 해결되지 않은 경우
- 참고: 참고할 이슈가 있을 때 사용

### Issue Tracking Commit Configuration

```
Type: #n Title
ex) 해결: #54 포르테 스토어 청약철회 에러 수정
```

### Collaboration
- 이슈 작성 시 `Merge when green` 라벨이 붙으면 TravisCI 및 StyleCI 가 정상 작동하면 자동으로 Merge
- 이슈 제목 앞에 `WIP (work in progress)` 가 붙으면 해당 이슈는 잠김 처리

## INSTALL (Setup)
INSTALL.md 를 참고해주세요.
