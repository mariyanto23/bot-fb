

CREATE TABLE `admins` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_admins_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(120) NOT NULL,
  `setting_value` TEXT NULL,
  `is_encrypted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `body` TEXT NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_used_at` DATETIME NULL,
  `used_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_comments_active` (`is_active`),
  KEY `idx_comments_last_used` (`last_used_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `target_groups` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(160) NOT NULL,
  `facebook_group_id` VARCHAR(80) NULL,
  `source_url` VARCHAR(600) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_fetched_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_target_groups_active` (`is_active`),
  KEY `idx_target_groups_fb_id` (`facebook_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `posts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `target_group_id` BIGINT UNSIGNED NULL,
  `facebook_post_id` VARCHAR(120) NOT NULL,
  `post_url` VARCHAR(700) NOT NULL,
  `post_hash` CHAR(64) NOT NULL,
  `author_name` VARCHAR(180) NULL,
  `caption` TEXT NULL,
  `status` ENUM('pending','commented','skipped','failed') NOT NULL DEFAULT 'pending',
  `comment_id` BIGINT UNSIGNED NULL,
  `commented_at` DATETIME NULL,
  `last_error` TEXT NULL,
  `attempt_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `next_attempt_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_posts_hash` (`post_hash`),
  KEY `idx_posts_status_next` (`status`, `next_attempt_at`),
  KEY `idx_posts_fb_id` (`facebook_post_id`),
  KEY `idx_posts_target_group` (`target_group_id`),
  CONSTRAINT `fk_posts_target_group` FOREIGN KEY (`target_group_id`) REFERENCES `target_groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_posts_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `level` ENUM('debug','info','warning','error') NOT NULL DEFAULT 'info',
  `status` VARCHAR(60) NOT NULL,
  `message` TEXT NOT NULL,
  `related_post_id` BIGINT UNSIGNED NULL,
  `context` JSON NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_logs_level_created` (`level`, `created_at`),
  KEY `idx_logs_post` (`related_post_id`),
  CONSTRAINT `fk_logs_post` FOREIGN KEY (`related_post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `telegram_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` ENUM('success','failed') NOT NULL,
  `message` TEXT NOT NULL,
  `response_body` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_telegram_logs_status_created` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bot_statuses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `status_key` VARCHAR(80) NOT NULL,
  `status_value` TEXT NULL,
  `locked_until` DATETIME NULL,
  `last_run_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_bot_statuses_key` (`status_key`),
  KEY `idx_bot_statuses_locked_until` (`locked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('bot_enabled', '1'),
('bot_batch_limit', '5'),
('bot_min_delay_seconds', '20'),
('bot_max_delay_seconds', '90'),
('bot_cooldown_seconds', '900'),
('facebook_base_url', 'https://mbasic.facebook.com'),
('telegram_enabled', '0')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

INSERT INTO `bot_statuses` (`status_key`, `status_value`) VALUES
('bot_lock', NULL),
('last_result', 'Idle')
ON DUPLICATE KEY UPDATE `status_key` = VALUES(`status_key`);
