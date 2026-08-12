TrinityCore 安全账户管理系统 —— 系统说明文档
1. 系统概述
本系统是一套面向 TrinityCore（魔兽世界模拟器） 的 Web 账户安全与管理平台，支持 Battle.net 账户体系，提供注册、激活、登录、密码找回、安全设置、点券充值、积分商城等完整功能。系统与 TrinityCore 的 auth、characters、world 数据库深度集成，通过 SOAP 接口与游戏服务端通信，实现账户、角色、物品等数据的在线管理。

系统采用 PHP 8+ 开发，遵循 MVC 分层思想，内置完善的安全防护机制（CSRF、会话固定、IP 黑名单、限流、审计日志、密码强度等），并支持 多语言（中/英/法/俄等）、多支付网关（Stripe、YiPay 等），适用于中小型魔兽私服的运营。

2. 技术架构与目录结构
2.1 技术栈
组件	技术选型
后端语言	PHP 8.0+
数据库	MySQL / MariaDB（与 TrinityCore 共用 auth/characters/world 库）
通信协议	SOAP（与 worldserver 交互）
前端	HTML5 + CSS3 + 原生 JavaScript（无框架，适配移动端）
邮件服务	PHPMailer（SMTP）
支付网关	Stripe（信用卡）、YiPay（聚合支付）、预留 PayPal/微信/支付宝接口
加密扩展	OpenSSL、GMP（或 BC Math）
会话管理	PHP Session + 数据库持久化（支持多设备互踢）
2.2 目录结构（关键文件）
text
/
├── config/
│   └── config.php                  # 统一配置文件（数据库、SOAP、支付、邮件、安全参数等）
├── includes/
│   ├── Database.php                # 数据库单例类，自动创建扩展表
│   ├── Security.php                # 安全核心：CSRF、密码哈希、IP黑名单、登录锁定等
│   ├── Session.php                 # 会话管理：登录、登出、记住我、多会话控制
│   ├── AuditLogger.php             # 审计日志系统（写入 audit_logs 表）
│   ├── RateLimiter.php             # 请求频率限制（IP/操作维度）
│   ├── Recaptcha.php               # Google reCAPTCHA 集成
│   ├── EmailService.php            # 邮件服务（基于 PHPMailer）
│   ├── SOAPClient.php              # TrinityCore SOAP 客户端（执行 GM 命令）
│   ├── SRP6.php / TrinitySRP6.php  # SRP6 验证器（兼容 TrinityCore 密码体系）
│   ├── languages.php               # 多语言支持类
│   ├── functions.php               # 全局辅助函数（自动加载、配置加载等）
│   └── footer.php                      # 公共页脚（含页面统计）
├── vendor/                         # Composer 依赖（PHPMailer、Stripe SDK 等）
├── languages/                      # 多语言文件（cn/en/fr/... 子目录）
├── auth.sql                        # 额外表结构（点券、商品、激活码、会话等）
├── login.php                       # 登录页
├── register.php                    # 注册页（调用 SOAP 创建 Battle.net 账户）
├── activate.php                    # 激活账户（通过邮件 token）
├── resend_activation.php           # 重发激活邮件
├── forgot_password.php             # 密码找回（支持邮箱或密保问题）
├── reset_password.php              # 通过 token 重置密码
├── profile.php                     # 用户资料页（显示角色、在线时间、点券兑换）
├── security_settings.php           # 安全设置（改密码、管理会话、设置密保问题）
├── points_shop.php                 # 点券商店（物品、等级提升、金币、GM 权限兑换）
├── topup.php                       # 点券充值（多支付网关）
└── logout.php                      # 退出登录

3. 主要功能模块
3.1 账户注册与激活
注册流程：用户填写邮箱、密码 → 调用 SOAP bnetaccount create 创建 Battle.net 账户 → 系统自动关联游戏账户（account 表）并记录 email 字段 → 生成激活 token（有效期 24 小时）→ 通过 SMTP 发送激活邮件。

激活机制：用户点击邮件中的链接 → 验证 token 有效性 → 将临时存储的密码哈希写入 account.passwd → 标记 token 已用，账户正式启用。

3.2 登录与会话管理
登录：支持邮箱 + 密码（SHA1 验证，兼容 TrinityCore 的 sha_pass_hash 或 passwd 字段）。

记住我：基于 remember_me_tokens 表，自动续期 30 天。

会话安全：每个登录生成唯一 session_id，记录 IP、User-Agent、最后活动时间；支持查看和撤销其他设备会话。

账号锁定：连续失败登录超过阈值（默认 5 次）则锁定 30 分钟。

3.3 密码找回与重置
方法一：通过注册邮箱接收重置链接（有效 60 分钟，一次性使用）。

方法二：通过预置的 安全问答（至少 3 个）验证身份后直接设置新密码。

两种方式均调用 SOAP bnetaccount set password 更新密码，并同步更新 account.passwd。

3.4 安全设置页面
修改密码：需验证当前密码，通过 SOAP 更新。

管理活跃会话：列出所有登录设备，支持单设备下线或全部下线（除当前设备）。

设置安全问答：用户可自定义 3~5 个问题和答案（哈希存储），用于辅助密码找回。

3.5 用户资料与角色信息
展示 Battle.net 账户信息、GM 等级、关联游戏账户列表。

连接 characters 数据库，显示所有角色（姓名、种族、职业、等级、金钱、地图、在线状态、总在线时长等）。

提供 “Unstuck” 功能：将角色传送至种族/职业的初始出生点（通过直接更新数据库坐标，无需 SOAP）。

3.6 点券（积分）系统
获取途径：

通过在线时间兑换（totaltime 字段）—— 可配置每小时点数，最低兑换小时数。

通过充值购买（见下节）。

消耗途径：

物品兑换：从 points_shop_items 表读取商品（物品 ID、数量、价格），通过邮件发送给指定角色。

等级提升：将角色直接升至配置的目标等级（如 90 级），要求角色离线。

金币充值：为角色增加指定数量的金币（以铜币为单位，防溢出）。

GM 权限兑换：为游戏账户授予 GM 等级 1（通过 account_access 表，RealmID=-1 全服生效）。

所有交易记录在 points_transactions 表，支持状态追踪（pending/success/failed）。

3.7 点券充值（支付集成）
配置驱动：在 config.php 中启用和配置各支付网关。

支持网关：

Stripe：使用 PaymentIntent 流程，前端渲染 Stripe Elements，后端确认支付后自动加点数。

YiPay（聚合支付）：生成签名后跳转至支付平台，异步回调通知（notify）和同步返回（return）处理。

费率控制：每个网关可独立设置兑换比例（1 CNY = X 点数），全局默认 100。

安全：所有支付回调均验证签名，并校验订单与用户匹配，防止伪造。

3.8 审计与日志
审计日志：记录关键操作（登录、注册、密码修改、商品兑换、会话撤销等），写入 audit_logs 表，包含 IP、User-Agent、详情 JSON。

登录日志：单独记录每次登录尝试（成功/失败），便于安全分析。

限流：基于 IP 和操作类型（如注册、密码重置），防止暴力攻击。

4. 安全机制详解
防护层面	具体措施
传输层	强制 HTTPS（可配置），防止中间人攻击。
身份认证	密码使用 SHA1（兼容 TrinityCore 原生）或 SRP6；支持双因素认证预留接口。
会话安全	会话 ID 定期再生；绑定 IP 和 User-Agent；设置 HttpOnly、SameSite=Strict Cookie；会话数据库持久化，支持过期和踢出。
CSRF 防护	每个表单嵌入随机 token（Security::generateCSRFToken），提交时验证。
输入过滤	使用 htmlspecialchars 输出转义；SQL 查询使用预编译语句（mysqli）。
密码强度	强制至少 8 字符，含大小写、数字、特殊字符；内置弱密码黑名单。
限流	RateLimiter 基于 Redis 或数据库记录，对注册、密码重置等操作限制频次（默认每小时 5 次）。
IP 黑名单	自动将频繁违规（如密码错误超过 10 次）的 IP 加入黑名单，可设置过期时间。
登录保护	账户锁定（失败次数超阈值后锁定 30 分钟），防止暴力破解。
激活机制	注册后必须通过邮件激活才能登录 Web 面板，激活 token 一次性且 24 小时有效。
权限控制	所有受限页面（profile、points_shop 等）均检查会话有效性，未登录自动跳转。
审计追踪	所有敏感操作记录至 audit_logs，便于事后追溯。
SOAP 通信	使用独立凭证（用户名/密码）与 worldserver 通信，建议启用 TLS。
5. 数据库设计（扩展表）
除 TrinityCore 原生表外，系统新增以下表（见 auth.sql）：

表名	用途
account_activation_tokens	存储注册激活 token（含临时密码哈希）
password_reset_tokens	存储密码重置 token（一次性，60 分钟有效）
password_reset_limits	记录 IP/用户密码重置请求次数（限流）
user_security_questions	存储用户的安全问答（question_id 和 answer_hash）
user_2fa	存储 TOTP 密钥（预留）
remember_me_tokens	记住我 token（持久登录）
account_sessions	活跃会话记录（用于多设备管理）
login_logs	登录尝试日志
audit_logs	审计日志（JSON 格式详情）
rate_limits	通用限流记录
ip_blacklist	IP 黑名单（可设置过期时间）
user_points	用户点券余额及统计
points_shop_items	商品配置（物品 ID、价格、库存、分类等）
points_transactions	点券交易记录（含商品兑换、充值、时间兑换）
6. 配置说明（config.php）
配置文件包含以下主要区块：

6.1 数据库连接（database / characters_database / world_database）
分别连接 auth、characters、world 库，支持独立主机和端口。

6.2 SOAP 配置
php
$config['soap'] = [
    'host' => '127.0.0.1',      // worldserver SOAP 地址
    'port' => 7878,             // 默认端口
    'username' => '3#1',        // 格式 `account_id#realm_id`
    'password' => '...',        // SOAP 密码（与 worldserver.conf 一致）
    'timeout' => 30,
    'debug' => false,
];
6.3 支付网关（stripe / yipay / paypal / wechat / alipay）
每个网关独立开关、密钥、汇率、环境（sandbox）。

YiPay 支持 MD5 签名。

6.4 邮件服务
使用 SMTP 发送激活、重置密码、安全告警等邮件。

支持 Gmail、QQ 邮箱等（需应用专用密码）。

6.5 安全参数
min_password_length、max_login_attempts、lockout_duration_minutes、session_lifetime、remember_me_lifetime 等。

启用/禁用 reCAPTCHA、2FA（预留）。

6.6 点券与商城
points_per_hour（在线时间兑换比例）、min_exchange_hours。

level_boost_target（等级提升目标值）。

商品分类（category）：level_boost、gold、gm_level、普通物品。

7. 部署环境要求
7.1 服务器环境
PHP：版本= 8.2，开启以下扩展：
组件		要求
PHP		8.2 (免费版本必须8.2)
MySQL		8.0+ / MariaDB 12+
Web Server	Apache / Nginx
PHP 扩展		mysqli, session,curl, soap（必须）, gd, json, mbstring,gmp,sg11,Imagick
Composer	2.0+
composer 	require phpmailer/phpmailer
composer 	require stripe/stripe-php

Web 服务器：Apache / Nginx + PHP-FPM。

数据库：MySQL 5.7+ / MariaDB 10.3+，与 TrinityCore 共用。

7.2 TrinityCore 端配置
worldserver.conf 必须开启 SOAP 接口：

text
SOAP.Enabled = 1
SOAP.Port = 7878
SOAP.Redirect = 0
确保 auth 库中有 battlenet_accounts 表（TrinityCore 自带）。

系统使用的 account 表需包含 email 字段（若缺失，系统会自动添加）。

7.3 依赖安装
使用 Composer 安装：

bash
composer require phpmailer/phpmailer
composer require stripe/stripe-php  # 如启用 Stripe

7.4 文件权限
config/config.php 建议设置为 600 或 640，只读。

日志目录（若未使用数据库审计）需可写。

上传目录（若有）需权限控制。

7.5 网络与安全
建议启用 HTTPS（在 config 中设置 require_https = true）。

配置防火墙，仅开放 80/443 端口，限制 SOAP 端口（7878）仅允许本机访问。

定期更新 PHP 及扩展版本。

8. 使用流程示例
8.1 新用户注册
访问 /register.php，填写邮箱和密码。

系统调用 SOAP 创建 Battle.net 账户，生成游戏账户，并发送激活邮件。

用户点击邮件中的激活链接 → 账户激活，可登录 Web 面板。

8.2 登录与点券获取
访问 /login.php，输入邮箱密码，可勾选“记住我”。

登录后进入 /profile.php，查看角色列表和当前点券。

在“点券中心”区域，输入在线时间小时数，兑换为点券（消耗角色的 totaltime）。

8.3 点券消费
点击“点券商店”进入 /points_shop.php。

浏览商品列表（物品、等级提升、金币、GM 权限）。

选择商品和目标角色，点击兑换。

系统扣除点券，执行对应操作（发送物品邮件、更新等级、增加金币等），记录交易。

8.4 充值点券
访问 /topup.php，输入充值金额。

选择支付方式（Stripe/YiPay 等）。

完成支付后，系统自动添加点券到账户。

8.5 安全设置
在 /security_settings.php 中修改密码、管理会话、设置密保问题。

密保问题用于密码找回的备选验证方式。

9. 扩展与定制
新增支付网关：在 config.php 中添加相应配置，并在 topup.php 中实现路由和回调处理。

新增商品类型：在 points_shop.php 的 exchange 逻辑中增加新的 category 分支。

多语言：在 languages/ 目录下添加对应语言文件，并继承 Language 类。

2FA：系统已预留 user_2fa 表和界面接口，可集成 TOTP 库（如 robthree/twofactorauth）。

10. 维护与监控
日志查看：audit_logs 和 login_logs 表提供详细的操作历史，可结合后台管理界面（需自行开发）展示。

定期清理：系统内置 AuditLogger::cleanOldLogs($days) 可定时清理过期日志。

数据库维护：建议定期优化 session、token 相关表，删除过期记录。

安全更新：关注 PHP 和 Composer 依赖的安全补丁，及时更新。

11. 常见问题（FAQ）
Q：注册时提示“SOAP service unavailable”
A：检查 worldserver 是否运行，SOAP 配置是否正确，防火墙是否开放 7878 端口（建议仅本地访问）。

Q：激活邮件未收到
A：检查 SMTP 配置，查看邮件日志；用户也可通过“重发激活邮件”功能再次发送。

Q：登录后无法看到角色
A：确认 characters_database 配置正确，且该 Battle.net 账户下确实有游戏角色。

Q：点券兑换物品未收到
A：检查 characters 库的 mail 和 item_instance 表是否成功插入；确认角色未满邮件箱。

Q：密码重置失败
A：确保 SOAP 可用，且账户存在；若使用密保问题，检查答案哈希是否匹配（注意大小写）。

12. 版本与支持
当前版本：基于 TrinityCore 12.x（支持 11.0 Dragonflight 及更早版本）。

兼容性：理论上兼容所有使用 Battle.net 账户体系的 TrinityCore 分支（需微调字段名）。

技术支持：可参考 TrinityCore 官方论坛或本系统文档，根据具体错误日志排查。