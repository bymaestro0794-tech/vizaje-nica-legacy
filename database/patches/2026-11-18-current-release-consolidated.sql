-- Vizaje-Nica current-release additive schema patch.
-- Includes creator program, local referral attribution, site dialogs,
-- Black Friday settings, and the existing referral service/outbox support.
-- Apply manually after a database backup. The application never runs this file.
-- Safe to rerun for tables/columns/indexes already present.
-- Prerequisites: the existing `clients` and `orders` tables; `clients.isb2b`.
-- Intentionally excludes `creator_partner_links`: external account linking was retired.
-- Replaces relevant schema changes from the 2026-11-04 through 2026-11-17 patches
-- for referral auth/outbox, creator profile/preferences, dialogs, Black Friday,
-- referral expiry, and the local creator program. Do not run those same patches
-- separately after this file. Older unrelated project migrations remain separate.

-- ---------------------------------------------------------------------------
-- Creator access flag and persistent creator display preferences.
-- ---------------------------------------------------------------------------
SET @vizaje_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clients' AND COLUMN_NAME = 'is_creator') = 0,
    'ALTER TABLE `clients` ADD COLUMN `is_creator` TINYINT(1) NOT NULL DEFAULT 0 AFTER `isb2b`', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

SET @vizaje_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clients' AND COLUMN_NAME = 'creator_widget_enabled') = 0,
    'ALTER TABLE `clients` ADD COLUMN `creator_widget_enabled` TINYINT(1) NOT NULL DEFAULT 1 AFTER `is_creator`', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

SET @vizaje_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clients' AND COLUMN_NAME = 'creator_b2c_mode') = 0,
    'ALTER TABLE `clients` ADD COLUMN `creator_b2c_mode` TINYINT(1) NOT NULL DEFAULT 0 AFTER `creator_widget_enabled`', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

CREATE TABLE IF NOT EXISTS `creator_program_applications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id` INT NOT NULL,
    `full_name` VARCHAR(180) NOT NULL,
    `email` VARCHAR(254) NOT NULL,
    `phone` VARCHAR(32) NOT NULL,
    `date_of_birth` DATE NOT NULL,
    `sex` TINYINT UNSIGNED NULL,
    `audience_size` VARCHAR(32) NOT NULL,
    `partnership_format` VARCHAR(32) NOT NULL,
    `instagram` VARCHAR(500) NULL,
    `tiktok` VARCHAR(500) NULL,
    `other_social` VARCHAR(500) NULL,
    `content_description` TEXT NOT NULL,
    `partnership_reason` TEXT NOT NULL,
    `consent_at` DATETIME NOT NULL,
    `locale` ENUM('ru', 'ro') NOT NULL DEFAULT 'ru',
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `reviewer_admin_id` INT NULL,
    `reviewed_at` DATETIME NULL,
    `rejection_reason` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_creator_applications_status_created` (`status`, `created_at`),
    KEY `idx_creator_applications_client_status` (`client_id`, `status`),
    KEY `idx_creator_applications_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Add profile gender to applications created by an earlier version of the patch.
SET @vizaje_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'creator_program_applications' AND COLUMN_NAME = 'sex') = 0,
    'ALTER TABLE `creator_program_applications` ADD COLUMN `sex` TINYINT UNSIGNED NULL AFTER `date_of_birth`', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

CREATE TABLE IF NOT EXISTS `creator_program_email_outbox` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `application_id` INT UNSIGNED NOT NULL,
    `event_type` ENUM('approved', 'rejected') NOT NULL,
    `status` ENUM('queued', 'sending', 'sent', 'failed') NOT NULL DEFAULT 'queued',
    `attempt_count` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `last_error` VARCHAR(80) NULL,
    `last_attempt_at` DATETIME NULL,
    `sent_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_creator_program_email_event` (`application_id`, `event_type`),
    KEY `idx_creator_program_email_due` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `creator_payout_requests` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id` INT NOT NULL,
    `amount_mdl` INT UNSIGNED NOT NULL,
    `comment` VARCHAR(1000) NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `admin_comment` VARCHAR(1000) NULL,
    `reviewer_admin_id` INT NULL,
    `reviewed_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_creator_payout_status_created` (`status`, `created_at`),
    KEY `idx_creator_payout_client_status` (`client_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Local creator links and 7-day referral attribution.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `legacy_creator_referral_links` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id` INT NOT NULL,
    `code` CHAR(12) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `label` VARCHAR(120) NULL,
    `link_type` ENUM('homepage', 'product', 'category', 'brand') NOT NULL,
    `locale` ENUM('ru', 'ro') NOT NULL,
    `target_url` VARCHAR(2048) NOT NULL,
    `status` ENUM('active', 'revoked') NOT NULL DEFAULT 'active',
    `click_count` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_legacy_creator_referral_code` (`code`),
    KEY `idx_legacy_creator_referral_client` (`client_id`, `status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `legacy_creator_referral_attributions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `link_id` BIGINT UNSIGNED NOT NULL,
    `token_hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `expires_at` DATETIME(3) NOT NULL,
    `consented_at` DATETIME(3) NULL DEFAULT NULL,
    `order_id` BIGINT UNSIGNED NULL,
    `created_at` DATETIME(3) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_legacy_creator_attribution_token` (`token_hash`),
    UNIQUE KEY `uq_legacy_creator_attribution_order` (`order_id`),
    KEY `idx_legacy_creator_attribution_expiry` (`order_id`, `expires_at`),
    KEY `idx_legacy_creator_attribution_link` (`link_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @vizaje_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'legacy_creator_referral_attribution_id') = 0,
    'ALTER TABLE `orders` ADD COLUMN `legacy_creator_referral_attribution_id` BIGINT UNSIGNED NULL DEFAULT NULL', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

SET @vizaje_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND INDEX_NAME = 'uq_orders_legacy_creator_attribution') = 0,
    'ALTER TABLE `orders` ADD UNIQUE KEY `uq_orders_legacy_creator_attribution` (`legacy_creator_referral_attribution_id`)', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

-- ---------------------------------------------------------------------------
-- Admin-managed site dialogs.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `site_dialogs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title_ru` VARCHAR(180) NOT NULL,
    `title_ro` VARCHAR(180) NOT NULL DEFAULT '',
    `text_ru` TEXT NOT NULL,
    `text_ro` TEXT NOT NULL,
    `image` VARCHAR(255) NOT NULL DEFAULT '',
    `button_text_ru` VARCHAR(80) NOT NULL DEFAULT '',
    `button_text_ro` VARCHAR(80) NOT NULL DEFAULT '',
    `target_url_ru` VARCHAR(1000) NOT NULL DEFAULT '',
    `target_url_ro` VARCHAR(1000) NOT NULL DEFAULT '',
    `audience` ENUM('all', 'guest', 'customer') NOT NULL DEFAULT 'all',
    `display_delay_seconds` SMALLINT UNSIGNED NOT NULL DEFAULT 45,
    `priority` SMALLINT NOT NULL DEFAULT 0,
    `starts_at` DATETIME NULL DEFAULT NULL,
    `ends_at` DATETIME NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_site_dialogs_active_window` (`is_active`, `starts_at`, `ends_at`, `priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Seasonal theme settings and Black Friday campaign timer/copy.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `site_theme_settings` (
    `id` TINYINT UNSIGNED NOT NULL,
    `theme_key` VARCHAR(32) NOT NULL DEFAULT 'default',
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `site_theme_settings` (`id`, `theme_key`, `updated_at`)
VALUES (1, 'default', UTC_TIMESTAMP());

SET @vizaje_patch_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'site_theme_settings' AND COLUMN_NAME = 'black_friday_starts_at') = 0, 'ALTER TABLE `site_theme_settings` ADD COLUMN `black_friday_starts_at` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;
SET @vizaje_patch_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'site_theme_settings' AND COLUMN_NAME = 'black_friday_ends_at') = 0, 'ALTER TABLE `site_theme_settings` ADD COLUMN `black_friday_ends_at` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;
SET @vizaje_patch_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'site_theme_settings' AND COLUMN_NAME = 'black_friday_text_ru') = 0, CONCAT('ALTER TABLE `site_theme_settings` ADD COLUMN `black_friday_text_ru` VARCHAR(255) NOT NULL DEFAULT ', CHAR(39), CHAR(39)), 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;
SET @vizaje_patch_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'site_theme_settings' AND COLUMN_NAME = 'black_friday_text_ro') = 0, CONCAT('ALTER TABLE `site_theme_settings` ADD COLUMN `black_friday_text_ro` VARCHAR(255) NOT NULL DEFAULT ', CHAR(39), CHAR(39)), 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

-- ---------------------------------------------------------------------------
-- Existing referral API nonce/rate-limit storage and analytics outbox.
-- These remain separate from the local creator attribution tables above.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `referral_service_nonces` (
    `key_id` VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `nonce` CHAR(22) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `received_at` DATETIME(6) NOT NULL,
    `expires_at` DATETIME(6) NOT NULL,
    PRIMARY KEY (`key_id`, `nonce`),
    KEY `idx_referral_service_nonces_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin;

CREATE TABLE IF NOT EXISTS `referral_service_rate_limits` (
    `principal` VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `window_start` DATETIME NOT NULL,
    `request_count` INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`principal`, `window_start`),
    KEY `idx_referral_service_rate_limits_window_start` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin;

SET @vizaje_patch_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'partners_referral_context') = 0, 'ALTER TABLE `orders` ADD COLUMN `partners_referral_context` VARCHAR(512) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;
SET @vizaje_patch_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'partners_referral_items_complete') = 0, 'ALTER TABLE `orders` ADD COLUMN `partners_referral_items_complete` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;
SET @vizaje_patch_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'partners_referral_paid_at') = 0, 'ALTER TABLE `orders` ADD COLUMN `partners_referral_paid_at` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;
SET @vizaje_patch_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'partners_referral_reconciled_at') = 0, 'ALTER TABLE `orders` ADD COLUMN `partners_referral_reconciled_at` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;
SET @vizaje_patch_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'partners_referral_reconciled_state') = 0, 'ALTER TABLE `orders` ADD COLUMN `partners_referral_reconciled_state` VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;
SET @vizaje_patch_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'partners_referral_context_expires_at') = 0, 'ALTER TABLE `orders` ADD COLUMN `partners_referral_context_expires_at` DATETIME(3) NULL DEFAULT NULL', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;
SET @vizaje_patch_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'partners_referral_order_created_at') = 0, 'ALTER TABLE `orders` ADD COLUMN `partners_referral_order_created_at` DATETIME(3) NULL DEFAULT NULL', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

SET @vizaje_patch_sql = IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND INDEX_NAME = 'idx_orders_partners_referral_reconcile') = 0, 'ALTER TABLE `orders` ADD KEY `idx_orders_partners_referral_reconcile` (`partners_referral_items_complete`, `partners_referral_reconciled_at`, `id`)', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

CREATE TABLE IF NOT EXISTS `partners_referral_analytics_outbox` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `event_name` VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `endpoint_path` VARCHAR(160) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `aggregate_type` VARCHAR(32) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL,
    `aggregate_id` VARCHAR(96) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL,
    `payload` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    `status` ENUM('pending', 'processing', 'delivered', 'failed') NOT NULL DEFAULT 'pending',
    `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
    `next_attempt_at` DATETIME NOT NULL,
    `locked_at` DATETIME NULL DEFAULT NULL,
    `last_error_code` VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    `delivered_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_partners_referral_analytics_event_id` (`event_id`),
    KEY `idx_partners_referral_analytics_due` (`status`, `next_attempt_at`, `id`),
    KEY `idx_partners_referral_analytics_locked` (`status`, `locked_at`),
    KEY `idx_partners_referral_analytics_aggregate` (`aggregate_type`, `aggregate_id`, `event_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


