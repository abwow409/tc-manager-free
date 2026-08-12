TrinityCore 보안 계정 관리 시스템 – 시스템 문서

1. 시스템 개요
본 시스템은 TrinityCore(World of Warcraft 에뮬레이터)용 웹 기반 계정 보안 및 관리 플랫폼으로, Battle.net 계정 체계를 지원하며 등록, 활성화, 로그인, 비밀번호 찾기, 보안 설정, 포인트 충전, 포인트 상점 등 완전한 기능을 제공합니다. 이 시스템은 TrinityCore의 auth, characters, world 데이터베이스와 깊이 통합되며 SOAP 인터페이스를 통해 게임 서버와 통신하여 계정, 캐릭터, 아이템 등을 온라인으로 관리할 수 있습니다.

PHP 8+로 개발되었으며 MVC 계층 구조를 따르고, 강력한 보안 메커니즘(CSRF, 세션 고정 방지, IP 블랙리스트, 요청 제한, 감사 로그, 비밀번호 강도 등)을 내장하고 있습니다. 또한 다국어(중국어, 영어, 프랑스어, 러시아어 등)와 다중 결제 게이트웨이(Stripe, YiPay 등)를 지원하여 중소형 사설 WoW 서버 운영에 적합합니다.

2. 기술 아키텍처 및 디렉터리 구조

2.1 기술 스택

구성 요소	기술 선택
백엔드 언어	PHP 8.0+
데이터베이스	MySQL / MariaDB (TrinityCore의 auth/characters/world와 공유)
통신 프로토콜	SOAP (worldserver와 상호작용)
프론트엔드	HTML5 + CSS3 + 순수 JavaScript (모바일 대응, 프레임워크 없음)
이메일 서비스	PHPMailer (SMTP)
결제 게이트웨이	Stripe(신용카드), YiPay(통합결제), PayPal/WeChat/Alipay 인터페이스 예약
암호화 확장	OpenSSL, GMP (또는 BC Math)
세션 관리	PHP 세션 + 데이터베이스 영속화 (다중 기기 동시 접속 차단 지원)
2.2 디렉터리 구조 (주요 파일)

text
/
├── config/
│   └── config.php                  # 통합 설정 (데이터베이스, SOAP, 결제, 메일, 보안 매개변수 등)
├── includes/
│   ├── Database.php                # 데이터베이스 싱글톤 클래스, 확장 테이블 자동 생성
│   ├── Security.php                # 보안 핵심: CSRF, 비밀번호 해싱, IP 블랙리스트, 로그인 잠금 등
│   ├── Session.php                 # 세션 관리: 로그인, 로그아웃, 기억하기, 다중 세션 제어
│   ├── AuditLogger.php             # 감사 로깅 시스템 (audit_logs 테이블에 기록)
│   ├── RateLimiter.php             # 요청 빈도 제한 (IP/작업 기준)
│   ├── Recaptcha.php               # Google reCAPTCHA 통합
│   ├── EmailService.php            # 이메일 서비스 (PHPMailer 기반)
│   ├── SOAPClient.php              # TrinityCore SOAP 클라이언트 (GM 명령 실행)
│   ├── SRP6.php / TrinitySRP6.php  # SRP6 검증기 (TrinityCore 비밀번호 체계 호환)
│   ├── languages.php               # 다국어 지원 클래스
│   ├── functions.php               # 전역 헬퍼 함수 (자동 로드, 설정 로드 등)
│   └── footer.php                  # 공통 바닥글 (페이지 통계 포함)
├── vendor/                         # Composer 의존성 (PHPMailer, Stripe SDK 등)
├── languages/                      # 다국어 파일 (cn/en/fr/... 하위 디렉터리)
├── auth.sql                        # 추가 테이블 구조 (포인트, 상점 아이템, 활성화 토큰, 세션 등)
├── login.php                       # 로그인 페이지
├── register.php                    # 등록 페이지 (SOAP 호출로 Battle.net 계정 생성)
├── activate.php                    # 계정 활성화 (이메일 토큰)
├── resend_activation.php           # 활성화 메일 재전송
├── forgot_password.php             # 비밀번호 찾기 (이메일 또는 보안 질문)
├── reset_password.php              # 토큰으로 비밀번호 재설정
├── profile.php                     # 사용자 프로필 (캐릭터, 접속 시간, 포인트 교환 표시)
├── security_settings.php           # 보안 설정 (비밀번호 변경, 세션 관리, 보안 질문 설정)
├── points_shop.php                 # 포인트 상점 (아이템, 레벨 업, 골드, GM 권한 교환)
├── topup.php                       # 포인트 충전 (다중 결제 게이트웨이)
└── logout.php                      # 로그아웃
3. 주요 기능 모듈

3.1 계정 등록 및 활성화

등록 흐름: 사용자가 이메일과 비밀번호 입력 → SOAP bnetaccount create 호출로 Battle.net 계정 생성 → 시스템이 자동으로 게임 계정(account 테이블)과 연결하고 email 필드 저장 → 활성화 토큰 생성(24시간 유효) → SMTP로 활성화 메일 발송.

활성화 메커니즘: 사용자가 메일의 링크 클릭 → 토큰 유효성 검증 → 임시 저장된 비밀번호 해시를 account.passwd에 기록 → 토큰 사용 처리, 계정 공식 활성화.

3.2 로그인 및 세션 관리

로그인: 이메일 + 비밀번호(SHA1 검증, TrinityCore의 sha_pass_hash 또는 passwd 필드 호환).

기억하기: remember_me_tokens 테이블 기반, 30일 자동 갱신.

세션 보안: 각 로그인마다 고유 세션 ID 생성, IP, User‑Agent, 마지막 활동 시간 기록; 다른 기기의 세션 조회 및 해제 지원.

계정 잠금: 연속 로그인 실패 횟수가 임계값(기본 5회) 초과 시 30분간 잠금.

3.3 비밀번호 찾기 및 재설정

방법 1: 등록된 이메일로 재설정 링크 수신(60분 유효, 1회용).

방법 2: 사전에 설정한 보안 질문(최소 3개)으로 본인 확인 후 새 비밀번호 직접 설정.

두 방법 모두 SOAP bnetaccount set password를 호출하여 비밀번호를 업데이트하고 account.passwd도 동기화.

3.4 보안 설정 페이지

비밀번호 변경: 현재 비밀번호 확인 후 SOAP으로 업데이트.

활성 세션 관리: 로그인된 모든 기기 목록 표시, 개별 세션 또는 현재 세션 외 모두 종료 지원.

보안 질문 설정: 사용자 정의 3~5개 질문과 답변(해시 저장)을 통해 비밀번호 찾기 보조 수단 제공.

3.5 사용자 프로필 및 캐릭터 정보

Battle.net 계정 정보, GM 레벨, 연결된 게임 계정 목록 표시.

characters 데이터베이스 연결하여 모든 캐릭터(이름, 종족, 직업, 레벨, 골드, 맵, 접속 상태, 총 접속 시간 등) 표시.

"Unstuck" 기능 제공: 캐릭터를 종족/직업 기본 시작 위치로 순간이동(데이터베이스 좌표 직접 업데이트, SOAP 미사용).

3.6 포인트(적립금) 시스템

획득 방법:

접속 시간(totaltime 필드) 교환 – 시간당 포인트 구성 가능, 최소 교환 시간 설정.

충전 구매(다음 섹션 참조).

사용 방법:

아이템 교환: points_shop_items 테이블에서 상품(아이템 ID, 수량, 가격)을 읽어 지정 캐릭터에게 우편 발송.

레벨 업: 캐릭터를 설정된 목표 레벨(예: 90)로 직접 상승, 캐릭터 오프라인 필요.

골드 충전: 캐릭터에 지정된 양의 골드(구리 단위, 오버플로 방지) 추가.

GM 권한 교환: 게임 계정에 GM 레벨 1 부여(account_access 테이블, RealmID=-1 전역 적용).

모든 거래 내역은 points_transactions 테이블에 상태(pending/success/failed)와 함께 기록.

3.7 포인트 충전 (결제 연동)

설정 기반: config.php에서 각 게이트웨이 활성화 및 설정.

지원 게이트웨이:

Stripe: PaymentIntent 흐름 사용, 프론트엔드에서 Stripe Elements 렌더링, 백엔드에서 결제 확인 후 자동 포인트 추가.

YiPay(통합결제): 서명 생성 후 결제 플랫폼으로 리디렉션, 비동기 콜백(notify) 및 동기 리턴(return) 처리.

환율 제어: 각 게이트웨이마다 독립적인 환율 설정 가능(1 CNY = X 포인트), 전역 기본값 100.

보안: 모든 결제 콜백은 서명을 검증하고 주문과 사용자 일치 여부를 확인하여 위조 방지.

3.8 감사 및 로깅

감사 로그: 주요 작업(로그인, 등록, 비밀번호 변경, 상품 교환, 세션 해제 등)을 audit_logs 테이블에 IP, User‑Agent, 상세 JSON과 함께 기록.

로그인 로그: 각 로그인 시도(성공/실패)를 별도 기록하여 보안 분석에 활용.

요청 제한: IP 및 작업 유형(등록, 비밀번호 재설정 등) 기반으로 무차별 대입 공격 방지.

4. 상세 보안 메커니즘

보안 계층	구체적 조치
전송 계층	HTTPS 강제(설정 가능)로 중간자 공격 방지.
인증	비밀번호는 SHA1(TrinityCore 기본 호환) 또는 SRP6 해싱; 2FA 예약 인터페이스.
세션 보안	세션 ID 주기적 재생성; IP 및 User‑Agent 바인딩; HttpOnly, SameSite=Strict 쿠키; 데이터베이스 영속화, 만료 및 강제 로그아웃 지원.
CSRF 보호	모든 폼에 랜덤 토큰(Security::generateCSRFToken) 포함, 제출 시 검증.
입력 필터링	출력 시 htmlspecialchars 이스케이프; SQL 쿼리는 준비된 문장(mysqli) 사용.
비밀번호 강도	최소 8자, 대소문자, 숫자, 특수문자 포함 강제; 내장 약한 비밀번호 블랙리스트.
요청 제한	RateLimiter가 Redis 또는 데이터베이스 기록을 사용하여 등록, 비밀번호 재설정 등 제한(기본 시간당 5회).
IP 블랙리스트	반복 위반(예: 비밀번호 오류 10회 초과) IP를 자동 블랙리스트 추가, 만료 시간 설정 가능.
로그인 보호	실패 횟수 초과 시 계정 잠금(30분)으로 무차별 대입 방지.
활성화 메커니즘	등록 후 반드시 이메일 활성화를 거쳐야 웹 패널 로그인 가능; 활성화 토큰 1회용, 24시간 유효.
접근 제어	모든 제한 페이지(프로필, 포인트 상점 등)는 세션 유효성을 확인하고 미인증 사용자는 리디렉션.
감사 추적	모든 민감 작업을 audit_logs에 기록하여 사후 추적 가능.
SOAP 통신	worldserver와 통신 시 별도 자격 증명(사용자명/비밀번호) 사용, TLS 권장.
5. 데이터베이스 설계 (확장 테이블)
TrinityCore 기본 테이블 외에 시스템이 추가하는 테이블(auth.sql 참조):

테이블 명	용도
account_activation_tokens	등록 활성화 토큰 저장(임시 비밀번호 해시 포함)
password_reset_tokens	비밀번호 재설정 토큰 저장(1회용, 60분 유효)
password_reset_limits	IP/사용자별 비밀번호 재설정 요청 횟수 기록(요청 제한용)
user_security_questions	사용자 보안 질문 저장(질문 ID 및 답변 해시)
user_2fa	TOTP 비밀키 저장(예약)
remember_me_tokens	"기억하기" 토큰(지속적 로그인)
account_sessions	활성 세션 기록(다중 기기 관리용)
login_logs	로그인 시도 로그
audit_logs	감사 로그(JSON 형식 상세)
rate_limits	범용 요청 제한 기록
ip_blacklist	IP 블랙리스트(만료 시간 설정 가능)
user_points	사용자 포인트 잔액 및 통계
points_shop_items	상점 상품 구성(아이템 ID, 가격, 재고, 카테고리 등)
points_transactions	포인트 거래 내역(상품 교환, 충전, 시간 교환)
6. 설정 설명 (config.php)
설정 파일은 다음 주요 섹션으로 구성됩니다.

6.1 데이터베이스 연결 (database / characters_database / world_database)

각각 auth, characters, world 데이터베이스에 연결하며, 독립적인 호스트와 포트를 지원합니다.

6.2 SOAP 설정

php
$config['soap'] = [
    'host' => '127.0.0.1',      // worldserver SOAP 주소
    'port' => 7878,             // 기본 포트
    'username' => '3#1',        // 형식 `account_id#realm_id`
    'password' => '...',        // SOAP 비밀번호 (worldserver.conf와 일치)
    'timeout' => 30,
    'debug' => false,
];
6.3 결제 게이트웨이 (stripe / yipay / paypal / wechat / alipay)

각 게이트웨이는 개별 활성화 스위치, 키, 환율, 환경(sandbox)을 가집니다.

YiPay는 MD5 서명을 지원합니다.

6.4 이메일 서비스

SMTP를 사용하여 활성화, 비밀번호 재설정, 보안 경고 등 이메일 발송.

Gmail, QQ 메일 등 지원(앱 전용 비밀번호 필요).

6.5 보안 매개변수

min_password_length, max_login_attempts, lockout_duration_minutes, session_lifetime, remember_me_lifetime 등.

reCAPTCHA, 2FA(예약) 활성화/비활성화.

6.6 포인트 및 상점

points_per_hour(접속 시간 교환 비율), min_exchange_hours.

level_boost_target(레벨 업 목표값).

상품 카테고리: level_boost, gold, gm_level, 일반 아이템.

7. 배포 환경 요구사항

7.1 서버 환경

PHP: 버전 8.2 (무료 버전은 정확히 8.2 필요)

MySQL: 8.0+ / MariaDB 12+

웹 서버: Apache / Nginx

필수 PHP 확장: mysqli, session, curl, soap(필수), gd, json, mbstring, gmp, sg11, Imagick

Composer: 2.0+

의존성 설치:

bash
composer require phpmailer/phpmailer
composer require stripe/stripe-php   # Stripe 사용 시
7.2 TrinityCore 설정

worldserver.conf에서 SOAP 활성화:

text
SOAP.Enabled = 1
SOAP.Port = 7878
SOAP.Redirect = 0
auth 데이터베이스에 battlenet_accounts 테이블이 있어야 함(TrinityCore 기본 제공).

account 테이블에 email 필드가 있어야 함(없으면 시스템이 자동 추가).

7.3 의존성 설치
위의 Composer 명령어를 사용합니다.

7.4 파일 권한

config/config.php는 600 또는 640(읽기 전용) 권장.

로그 디렉터리(데이터베이스 감사 미사용 시) 쓰기 권한 필요.

업로드 디렉터리(있는 경우) 접근 제어 필수.

7.5 네트워크 및 보안

HTTPS 활성화 강력 권장(config에서 require_https = true 설정).

방화벽은 80/443 포트만 개방, SOAP 포트(7878)는 로컬호스트로 제한.

PHP 및 확장 버전 정기 업데이트.

8. 사용 흐름 예시

8.1 신규 사용자 등록

/register.php 방문, 이메일과 비밀번호 입력.

시스템이 SOAP을 호출하여 Battle.net 계정 생성, 게임 계정 생성 및 활성화 메일 발송.

사용자가 메일의 활성화 링크 클릭 → 계정 활성화, 웹 패널 로그인 가능.

8.2 로그인 및 포인트 획득

/login.php 방문, 이메일과 비밀번호 입력, "기억하기" 선택 가능.

로그인 후 /profile.php에서 캐릭터 목록과 현재 포인트 확인.

"포인트 센터" 영역에서 접속 시간(시간)을 입력하여 포인트로 교환(캐릭터의 totaltime 소비).

8.3 포인트 사용

"포인트 상점" 클릭 → /points_shop.php.

상품 목록(아이템, 레벨 업, 골드, GM 권한) 확인.

상품과 대상 캐릭터 선택 후 "교환" 클릭.

시스템이 포인트를 차감하고 해당 작업(아이템 우편 발송, 레벨 업데이트, 골드 추가 등)을 수행하며 거래 기록.

8.4 포인트 충전

/topup.php 방문, 충전 금액 입력.

결제 수단 선택(Stripe/YiPay 등).

결제 완료 후 시스템이 자동으로 포인트를 계정에 추가.

8.5 보안 설정

/security_settings.php에서 비밀번호 변경, 세션 관리, 보안 질문 설정.

보안 질문은 비밀번호 찾기 시 대체 인증 수단으로 사용됨.

9. 확장 및 커스터마이징

새 결제 게이트웨이 추가: config.php에 설정 추가, topup.php에서 라우팅 및 콜백 처리 구현.

새 상품 유형 추가: points_shop.php의 교환 로직에 새로운 category 분기 추가.

다국어: languages/ 디렉터리에 해당 언어 파일 추가 후 Language 클래스 상속.

2FA: 시스템이 이미 user_2fa 테이블과 인터페이스 예약 공간을 마련했으므로 TOTP 라이브러리(예: robthree/twofactorauth)를 통합하여 활성화 가능.

10. 유지보수 및 모니터링

로그 확인: audit_logs 및 login_logs 테이블에서 상세 작업 내역 확인 가능, 관리자 인터페이스(별도 개발)로 표시 가능.

정기 정리: 시스템에 AuditLogger::cleanOldLogs($days) 함수가 내장되어 있어 오래된 로그를 주기적으로 삭제할 수 있음.

데이터베이스 관리: 세션, 토큰 관련 테이블에서 만료된 레코드 정기 삭제 및 최적화 권장.

보안 업데이트: PHP 및 Composer 의존성 보안 패치를 정기적으로 적용.

11. 자주 묻는 질문(FAQ)

Q: 등록 시 "SOAP service unavailable" 오류가 발생합니다.
A: worldserver가 실행 중인지, SOAP 설정이 올바른지, 방화벽에서 7878 포트를 허용하는지(로컬 접근 권장) 확인하세요.

Q: 활성화 메일이 도착하지 않습니다.
A: SMTP 설정을 확인하고 메일 로그를 점검하세요. 사용자는 "활성화 메일 재전송" 기능을 사용할 수 있습니다.

Q: 로그인 후 캐릭터가 보이지 않습니다.
A: characters_database 설정이 올바른지, 해당 Battle.net 계정에 실제 캐릭터가 있는지 확인하세요.

Q: 포인트로 아이템을 교환했지만 받지 못했습니다.
A: characters 데이터베이스의 mail 및 item_instance 테이블에 정상 삽입되었는지 확인하고, 캐릭터의 우편함이 가득 차지 않았는지 점검하세요.

Q: 비밀번호 재설정이 실패합니다.
A: SOAP가 사용 가능하고 계정이 존재하는지 확인하세요. 보안 질문을 사용하는 경우 답변 해시가 일치하는지(대소문자 구분) 확인하세요.

12. 버전 및 지원

현재 버전: TrinityCore 12.x 기반(11.0 Dragonflight 및 이전 버전 지원).

호환성: Battle.net 계정 체계를 사용하는 모든 TrinityCore 브랜치와 이론상 호환(일부 필드명 조정 필요 가능).

기술 지원: TrinityCore 공식 포럼 또는 본 문서를 참조하고, 구체적인 오류 로그를 기반으로 문제를 해결하시기 바랍니다.