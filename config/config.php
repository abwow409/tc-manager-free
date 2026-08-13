<?php
// config/config.php - Unified Configuration System

$config = array();

// ============================================
// Site Basic Configuration
// ============================================
$config['site'] = array(
    'name' => 'TrinityCore Security System',
    'url' => 'https://yourdomain.com',
    'timezone' => 'Asia/Shanghai',
    'charset' => 'UTF-8'
);

// ============================================
// Game Server Configuration
// ============================================
$config['game'] = array(
    'name' => 'Azeroth',
    'expansion' => 9,
    'max_level' => 70,
    'realm_id' => 1
);
// ============================================
// Database Configuration
// ============================================

// Auth Database (Account Authentication)
$config['database'] = array(
    'host' => 'localhost',
    'port' => 3306,
    'username' => 'trinity',
    'password' => 'trinity',
    'database' => 'auth',
    'charset' => 'utf8mb4'
);

// Characters Database (Character Data)
$config['characters_database'] = array(
    'host' => 'localhost',
    'port' => 3306,
    'username' => 'trinity',
    'password' => 'trinity',
    'database' => 'characters',
    'charset' => 'utf8mb4'
);

$config['world_database'] = array(
    'host'     => 'localhost',
    'username' => 'trinity',
    'password' => 'trinity',
    'database' => 'world',   // Or your world database name
    'port'     => 3306,
);

// ============================================
// SOAP Configuration - First check whether soap is enabled in worldserver.conf
// ============================================
$config['soap'] = array(
    'host' => 'localhost',
    'port' => 7878,
    'username' => '3#1', // Usually in format 1#1, 2#2 from auth.account table; requires GM level=3 (set in account_access)
    'password' => 'Waasthdf$zdg',
    'timeout' => 30,
    'debug' => false,   // Recommended to disable in production
);

// ============================================
// Points Shop Character Level Boost Target Level (New)
// ============================================
$config['level_boost_target'] = 90;   // All level boost items will raise character to this level

// Points Recharge Configuration
$config['topup'] = array(
    'rate'        => 100,   // 1 CNY = 100 points
    'min_amount'  => 1,     // Minimum recharge 1 CNY
    'max_amount'  => 1000   // Maximum recharge 1000 CNY
);

// ============================================
// Payment Gateway Configuration (Supports multiple payment methods; enable/disable and parameters set here)
// ============================================
$config['payment_gateways'] = array(
    // ---- Stripe Payment ----
    'stripe' => array(
        'enabled' => true,                     // true = enabled, false = disabled
        'publishable_key' => 'pk_test_xxxx',   // Test public key (starts with pk_test_)
        'secret_key' => 'sk_test_xxxx',        // Test secret key (starts with sk_test_)
        'currency' => 'cny',                   // Settlement currency
        'rate' => 100,                         // 1 CNY = 100 points (can override global rate)
    ),
    // ---- YiPay (Aggregated Payment, supports Alipay/WeChat) ----
    'yipay' => array(
        'enabled' => true,                      // Enable YiPay
        'merchant_id' => '1000',                // Merchant ID
        'merchant_key' => 'your_md5_key',       // MD5 key
        'gateway_url' => 'https://pay.example.com/pay/', // Payment gateway URL
        'rate' => 100,                          // 1 CNY = 100 points
    ),
    // ---- PayPal (Extended) ----
    'paypal' => array(
        'enabled' => true,
        'client_id' => 'your_client_id',
        'secret' => 'your_secret',
        'rate' => 100,
        'sandbox' => true,                      // Sandbox mode
    ),
    // ---- WeChat Pay (Official, Extended) ----
    'wechat' => array(
        'enabled' => false,
        'app_id' => 'wx_appid',
        'mch_id' => 'your_mch_id',
        'key' => 'your_api_key',
        'cert_path' => '/path/to/apiclient_cert.pem',
        'key_path' => '/path/to/apiclient_key.pem',
        'rate' => 100,
    ),
    // ---- Alipay (Official, Extended) ----
    'alipay' => array(
        'enabled' => false,
        'app_id' => 'your_app_id',
        'private_key' => 'your_private_key',
        'public_key' => 'your_public_key',
        'gateway_url' => 'https://openapi.alipay.com/gateway.do',
        'rate' => 100,
    ),
);
// ============================================
// reCAPTCHA Configuration
// ============================================
$config['recaptcha'] = array(
    'site_key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
    'secret_key' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
    'enabled' => false
);

// ============================================
// Email Configuration (Important: Must be filled correctly)
// ============================================
$config['email'] = array(
    'enabled' => true,   // Must be true
    'smtp_host' => 'smtp.gmail.com',      // Your SMTP server
    'smtp_port' => 587,                   // 587 (TLS) or 465 (SSL)
    'smtp_username' => 'youremail@gmail.com',
    'smtp_password' => 'abcd efgh ijkl mnop', // App-specific SMTP password
    'from_email' => 'youremail@gmail.com',
    'from_name' => 'TrinityCore Security'
);

// ============================================
// SMS Configuration
// ============================================
$config['sms'] = array(
    'enabled' => false,
    'provider' => 'aliyun',
    'access_key' => '',
    'access_secret' => '',
    'sign_name' => 'TrinityCore',
    'template_code' => 'SMS_XXXXXX'
);

// ============================================
// Security Configuration
// ============================================
$config['security'] = array(
    'min_password_length' => 8,
    'token_expiry_minutes' => 60,
    'max_login_attempts' => 5,
    'lockout_duration_minutes' => 30,
    'max_requests_per_hour' => 3,
    'require_https' => false,
    'session_lifetime' => 3600,
    'remember_me_lifetime' => 2592000,
    
    'verification_methods' => array(
        'email' => true,
        'phone' => false,
        'security_questions' => true
    )
);

// ============================================
// Security Questions List
// ============================================
$config['security_questions'] = array(
    '1' => 'What was the name of your elementary school?',
    '2' => 'What is your mother\'s name?',
    '3' => 'What is your father\'s name?',
    '4' => 'Where were you born?',
    '5' => 'What was the brand of your first car?',
    '6' => 'What was the name of your favorite pet?',
    '7' => 'What is the name of your best friend?',
    '8' => 'What was the name of your junior high school homeroom teacher?'
);

// ============================================
// Points Exchange Configuration
// ============================================
$config['points'] = array(
    'points_per_hour' => 20,     // Points exchangeable per 1 hour online
    'min_exchange_hours' => 2,   // Minimum exchange hours (must be >= 1)
);
// ============================================
// 2FA Configuration
// ============================================
$config['2fa'] = array(
    'enabled' => false,
    'issuer' => 'TrinityCore',
    'period' => 30,
    'digits' => 6,
    'algorithm' => 'sha1'
);

// ============================================
// Logging Configuration
// ============================================
$config['logging'] = array(
    'enabled' => true,
    'level' => 'info',
    'retention_days' => 90
);

// ============================================
// Character Race Mapping
// ============================================
$config['races'] = array(
    1 => 'Human', 2 => 'Orc', 3 => 'Dwarf', 4 => 'Night Elf',
    5 => 'Undead', 6 => 'Tauren', 7 => 'Gnome', 8 => 'Troll',
    9 => 'Goblin', 10 => 'Blood Elf', 11 => 'Draenei', 22 => 'Worgen',
    24 => 'Pandaren', 25 => 'Pandaren', 26 => 'Pandaren',
    27 => 'Nightborne', 28 => 'Highmountain Tauren', 29 => 'Void Elf',
    30 => 'Lightforged Draenei', 31 => 'Zandalari Troll', 32 => 'Kul Tiran',
    34 => 'Mechagnome', 35 => 'Vulpera', 36 => 'Dark Ranger',
    37 => 'Dark Iron Dwarf', 38 => 'Mag\'har Orc', 39 => 'Defias Brotherhood'
);

// ============================================
// Character Class Mapping
// ============================================
$config['classes'] = array(
    1 => 'Warrior', 2 => 'Paladin', 3 => 'Hunter', 4 => 'Rogue',
    5 => 'Priest', 6 => 'Death Knight', 7 => 'Shaman', 8 => 'Mage',
    9 => 'Warlock', 10 => 'Monk', 11 => 'Druid', 12 => 'Demon Hunter',
    13 => 'Evoker'
);

// ============================================
// Class Colors
// ============================================
$config['class_colors'] = array(
    1 => '#C79C6E', 2 => '#F58CBA', 3 => '#ABD473', 4 => '#FFF569',
    5 => '#FFFFFF', 6 => '#C41F3B', 7 => '#0070DE', 8 => '#69CCF0',
    9 => '#9482C9', 10 => '#00FF96', 11 => '#FF7D0A', 12 => '#A330C9',
    13 => '#33937F'
);

// ============================================
// Character Race Icons
// ============================================
$config['race_icons'] = array(
    1 => '🧑', 2 => '👹', 3 => '🪨', 4 => '🌙',
    5 => '💀', 6 => '🐮', 7 => '🤖', 8 => '🗡️',
    9 => '⚙️', 10 => '🧝', 11 => '🧛', 22 => '🐺',
    24 => '🐼'
);

// ============================================
// Map Name Mapping (Extended)
// ============================================
$config['map_names'] = array(
    // ---- Azeroth (Classic) ----
    0 => 'Azeroth',
    1 => 'Kalimdor',
    13 => 'Test Map',
    30 => 'Alterac Valley',
    33 => 'Shadowfang Keep',
    34 => 'Stormwind Stockade',
    36 => 'Deadmines',
    43 => 'Wailing Caverns',
    47 => 'Razorfen Kraul',
    48 => 'Blackfathom Deeps',
    70 => 'Uldaman',
    90 => 'Gnomeregan',
    109 => 'Sunken Temple',
    129 => 'Razorfen Downs',
    189 => 'Scarlet Monastery',
    209 => 'Zul\'Farrak',
    229 => 'Blackrock Spire',
    230 => 'Blackrock Depths',
    249 => 'Onyxia\'s Lair',
    269 => 'Dark Portal',
    289 => 'Scholomance',
    309 => 'Zul\'Gurub',
    329 => 'Stratholme',
    349 => 'Maraudon',
    389 => 'Dire Maul',
    409 => 'Ruins of Ahn\'Qiraj',
    429 => 'Temple of Ahn\'Qiraj',
    469 => 'Blackwing Lair',
    489 => 'Molten Core',
    509 => 'Arathi Basin',
    529 => 'Warsong Gulch',
    539 => 'The Temple of Atal\'Hakkar',
    559 => 'Naxxramas',
    569 => 'Eastern Plaguelands',
    589 => 'Winterspring',

    // ---- Outland (The Burning Crusade) ----
    530 => 'Outland',
    532 => 'Karazhan',
    540 => 'Hellfire Ramparts',
    542 => 'Blood Furnace',
    543 => 'Hellfire Peninsula',
    544 => 'Shattered Halls',
    545 => 'Steamvault',
    546 => 'Shadow Labyrinth',
    547 => 'The Arcatraz',
    548 => 'The Botanica',
    549 => 'The Mechanar',
    550 => 'Mana-Tombs',
    552 => 'Auchenai Crypts',
    553 => 'Sethekk Halls',
    554 => 'Old Hillsbrad Foothills',
    555 => 'Black Morass',
    556 => 'Tempest Keep',
    557 => 'Serpentshrine Cavern',
    558 => 'Coilfang Reservoir',
    560 => 'Battle for Mount Hyjal',
    564 => 'Black Temple',
    565 => 'Gruul\'s Lair',
    566 => 'Magtheridon\'s Lair',
    568 => 'Zul\'Aman',
    580 => 'Sunwell Plateau',
    585 => 'Magister\'s Terrace',
    596 => 'Blade\'s Edge Mountains',

    // ---- Northrend (Wrath of the Lich King) ----
    571 => 'Northrend',
    574 => 'Utgarde Keep',
    575 => 'Utgarde Pinnacle',
    576 => 'Naxxramas',
    578 => 'The Eye of Eternity',
    585 => 'The Oculus',
    595 => 'Ulduar',
    599 => 'The Culling of Stratholme',
    600 => 'Drak\'Tharon Keep',
    601 => 'Gundrak',
    602 => 'Azjol-Nerub',
    603 => 'Ahn\'kahet: The Old Kingdom',
    604 => 'Violet Hold',
    608 => 'Trial of the Crusader',
    615 => 'Trial of the Champion',
    616 => 'Icecrown Citadel',
    619 => 'The Forge of Souls',
    620 => 'Pit of Saron',
    621 => 'Halls of Reflection',
    624 => 'Twin Val\'kyr',
    631 => 'Icecrown Glacier',
    632 => 'Crystalsong Forest',
    638 => 'Howling Fjord',
    639 => 'Grizzly Hills',
    641 => 'Zul\'Drak',
    642 => 'Dalaran',
    643 => 'Dragonblight',
    644 => 'Borean Tundra',
    645 => 'Sholazar Basin',
    646 => 'Storm Peaks',
    647 => 'Wintergrasp',
    648 => 'Icecrown',
    649 => 'Himtoga',
    650 => 'Azjol-Nerub',
    651 => 'Oulgar',
    652 => 'Grizzly Hills',

    // ---- Cataclysm ----
    0 => 'Azeroth', // Azeroth updated after Cataclysm, ID unchanged
    1 => 'Kalimdor',
    646 => 'Deepholm',
    647 => 'Uldum',
    648 => 'Twilight Highlands',
    649 => 'Vashj\'ir',
    650 => 'Mount Hyjal',
    651 => 'Tol Barad',
    720 => 'Blackrock Caverns',
    725 => 'The Stonecore',
    726 => 'The Vortex Pinnacle',
    727 => 'Throne of the Tides',
    728 => 'Halls of Origination',
    729 => 'Grim Batol',
    730 => 'Lost City of the Tol\'vir',
    731 => 'Blackwing Descent',
    732 => 'Throne of the Four Winds',
    733 => 'Bastion of Twilight',
    734 => 'Nefarian\'s Lair',
    735 => 'Rise of the Zandalari',
    736 => 'Zul\'Aman (revamped)',
    737 => 'Zul\'Gurub (revamped)',
    738 => 'Firelands',
    739 => 'Dragon Soul',
    740 => 'Well of Eternity',
    741 => 'Twilight of the Aspects',
    742 => 'End Time',
    743 => 'Hour of Twilight',

    // ---- Mists of Pandaria ----
    870 => 'Pandaria',
    806 => 'Jade Forest',
    807 => 'Valley of the Four Winds',
    808 => 'Wandering Isle',
    809 => 'Kun-Lai Summit',
    810 => 'Townlong Steppes',
    811 => 'Vale of Eternal Blossoms',
    857 => 'Dread Wastes',
    858 => 'Townlong Steppes', // duplicate, adjust if needed
    859 => 'Krasarang Wilds',
    860 => 'Isle of Thunder',
    861 => 'Timeless Isle',
    862 => 'Vale of Eternal Blossoms (revamped)',
    863 => 'Mogu\'shan Palace',
    864 => 'Stormstout Brewery',
    865 => 'Shado-Pan Monastery',
    866 => 'Scarlet Halls',
    867 => 'Scarlet Monastery (revamped)',
    868 => 'Scholomance (revamped)',
    869 => 'Siege of Orgrimmar',

    // ---- Warlords of Draenor ----
    1116 => 'Draenor',
    1117 => 'Frostfire Ridge',
    1118 => 'Shadowmoon Valley',
    1119 => 'Gorgrond',
    1120 => 'Talador',
    1121 => 'Spires of Arak',
    1122 => 'Nagrand',
    1123 => 'Blade\'s Edge Forest',
    1124 => 'Tanaan Jungle',
    1125 => 'Shattrath City',
    1126 => 'Dark Portal',
    1127 => 'Iron Docks',
    1128 => 'Auchindoun',
    1129 => 'Skyreach',
    1130 => 'Everbloom',
    1131 => 'Grimrail Depot',
    1132 => 'Blackrock Foundry',
    1133 => 'Highmaul',
    1134 => 'Hellfire Citadel',

    // ---- Legion ----
    1220 => 'Broken Isles',
    1221 => 'Azsuna',
    1222 => 'Val\'sharah',
    1223 => 'Highmountain',
    1224 => 'Stormheim',
    1225 => 'Suramar',
    1226 => 'Broken Shore',
    1227 => 'Tomb of Sargeras',
    1228 => 'Nighthold',
    1229 => 'Trial of Valor',
    1230 => 'Emerald Nightmare',
    1231 => 'The Arcway',
    1232 => 'Court of Stars',
    1233 => 'Black Rook Hold',
    1234 => 'Halls of Valor',
    1235 => 'Neltharion\'s Lair',
    1236 => 'Eye of Azshara',
    1237 => 'Vault of the Wardens',
    1238 => 'Violet Hold (revamped)',
    1239 => 'Karazhan (revamped)',
    1240 => 'Return to Karazhan',
    1241 => 'Cathedral of Eternal Night',

    // ---- Battle for Azeroth ----
    1669 => 'Zandalar',
    1670 => 'Kul Tiras',
    1671 => 'Zuldazar',
    1672 => 'Nazmir',
    1673 => 'Vol\'dun',
    1674 => 'Tiragarde Sound',
    1675 => 'Drustvar',
    1676 => 'Stormsong Valley',
    1677 => 'Uldum (revamped)',
    1678 => 'Vale of Eternal Blossoms (revamped)',
    1679 => 'Ny\'alotha, the Waking City',
    1680 => 'Stormwind Stockade (revamped)',
    1681 => 'Tol Dagor',
    1682 => 'Freehold',
    1683 => 'Waycrest Manor',
    1684 => 'The Motherlode!!',
    1685 => 'Temple of Sethraliss',
    1686 => 'Underrot',
    1687 => 'Atal\'Dazar',
    1688 => 'King\'s Rest',
    1689 => 'Shrine of the Storm',
    1690 => 'Siege of Boralus',
    1691 => 'Eternal Palace',
    1692 => 'Mechagon',
    1693 => 'Arathi Basin (revamped)',
    1694 => 'Arathi Highlands (revamped)',

    // ---- Shadowlands ----
    1718 => 'Shadowlands',
    1719 => 'Bastion',
    1720 => 'Maldraxxus',
    1721 => 'Ardenweald',
    1722 => 'Revendreth',
    1723 => 'The Maw',
    1724 => 'Oribos',
    1725 => 'Sanctum of Domination',
    1726 => 'Sepulcher of the First Ones',
    1727 => 'Torghast, Tower of the Damned',
    1728 => 'The Other Side',
    1729 => 'Plaguefall',
    1730 => 'Theater of Pain',
    1731 => 'Necrotic Wake',
    1732 => 'Halls of Atonement',
    1733 => 'Sanguine Depths',
    1734 => 'Spires of Ascension',
    1735 => 'Mists of Tirna Scithe',

    // ---- Dragonflight ----
    1722 => 'Dragon Isles', // Note: 1722 was Revendreth in Shadowlands, now overwritten for Dragonflight
    1723 => 'Waking Shores',
    1724 => 'Ohn\'ahran Plains',
    1725 => 'Azure Span',
    1726 => 'Thaldraszus',
    1727 => 'Forbidden Reach',
    1728 => 'Crimson Glade',
    1729 => 'Obsidian Throne',
    1730 => 'Ruby Life Pools',
    1731 => 'Neltharion\'s Lair (revamped)',
    1732 => 'Algeth\'ar Academy',
    1733 => 'Halls of Infusion',
    1734 => 'Uldaman (revamped)',
    1735 => 'Brackenhide Hollow',
    1736 => 'Nokhud Offensive',
    1737 => 'Azure Vault',
    1738 => 'Shadowmoon Burial Grounds (revamped)',
    1739 => 'Dragon Soul (revamped)',
    1740 => 'Emerald Dream (revamped)',
);

return $config;