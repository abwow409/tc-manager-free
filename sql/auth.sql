-- Remember me tokens table
CREATE TABLE IF NOT EXISTS `remember_me_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `account_id` INT NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    UNIQUE KEY `unique_token` (`token`),
    INDEX (`account_id`),
    INDEX (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Active sessions table (for multi-server synchronization)
CREATE TABLE IF NOT EXISTS `account_sessions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `account_id` INT NOT NULL,
    `session_id` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT,
    `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    UNIQUE KEY `unique_session` (`session_id`),
    INDEX (`account_id`),
    INDEX (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Account activation tokens table
CREATE TABLE IF NOT EXISTS `account_activation_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `account_id` INT NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(64) NOT NULL UNIQUE,
    `password` VARCHAR(40) NOT NULL,  -- Stores encrypted password
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    INDEX (`account_id`),
    INDEX (`token`),
    INDEX (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User points table
CREATE TABLE IF NOT EXISTS `user_points` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `battlenet_id` INT NOT NULL UNIQUE COMMENT 'Associated with battlenet_accounts.id',
    `points` BIGINT NOT NULL DEFAULT 0 COMMENT 'Current points',
    `total_earned` BIGINT NOT NULL DEFAULT 0 COMMENT 'Total earned points historically',
    `total_spent` BIGINT NOT NULL DEFAULT 0 COMMENT 'Total spent points historically',
    `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`battlenet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Points shop items table
CREATE TABLE IF NOT EXISTS `points_shop_items` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `item_name` VARCHAR(100) NOT NULL COMMENT 'Item name',
    `item_id` INT NOT NULL COMMENT 'Game item ID',
    `item_count` INT NOT NULL DEFAULT 1 COMMENT 'Quantity per exchange',
    `points_cost` BIGINT NOT NULL COMMENT 'Points required',
    `icon` VARCHAR(50) DEFAULT 'default' COMMENT 'Icon name',
    `description` TEXT COMMENT 'Item description',
    `stock` INT DEFAULT -1 COMMENT 'Stock, -1 means unlimited',
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`item_id`),
    INDEX (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exchange records table
CREATE TABLE IF NOT EXISTS `points_transactions` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `battlenet_id` INT NOT NULL,
    `account_id` INT NOT NULL COMMENT 'Associated account.id (game account)',
    `character_guid` INT NOT NULL COMMENT 'Character GUID',
    `character_name` VARCHAR(12) NOT NULL,
    `item_id` INT NOT NULL,
    `item_name` VARCHAR(100) NOT NULL,
    `item_count` INT NOT NULL,
    `points_spent` BIGINT NOT NULL,
    `status` ENUM('pending','success','failed') DEFAULT 'pending',
    `soap_response` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `processed_at` TIMESTAMP NULL,
    INDEX (`battlenet_id`),
    INDEX (`account_id`),
    INDEX (`character_guid`),
    INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for password_reset_limits
-- ----------------------------
DROP TABLE IF EXISTS `password_reset_limits`;
CREATE TABLE `password_reset_limits`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `username` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `attempts` int NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `ip_address`(`ip_address` ASC) USING BTREE,
  INDEX `username`(`username` ASC) USING BTREE,
  INDEX `created_at`(`created_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;