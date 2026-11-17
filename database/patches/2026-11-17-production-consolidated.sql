-- Consolidated production patch: creator links, referral attribution expiry,
-- and Black Friday theme settings.
-- Apply once after taking a database backup.
--
-- Prerequisites:
--   * Existing `clients`, `orders`, and `site_theme_settings` tables.
--   * `orders.partners_referral_context` from
--     2026-11-12-create-partners-referral-analytics-outbox.sql.
--   * `site_theme_settings` from
--     2026-10-21-create-site-theme-settings.sql.
--
-- This combined file replaces these three patches for this deployment:
--   2026-11-16-create-creator-partner-links.sql
--   2026-11-16-add-partners-referral-context-expiry.sql
--   2026-11-16-black-friday-theme-settings.sql

-- M5.5.2: Legacy creator account links.
CREATE TABLE IF NOT EXISTS `creator_partner_links` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id` INT UNSIGNED NOT NULL,
    `partner_id` CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `creator_id` CHAR(10) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `account_type` VARCHAR(24) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'creator',
    `partner_status` VARCHAR(24) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `scopes` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    `grant_encrypted` VARCHAR(512) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `grant_key_id` VARCHAR(80) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `status_checked_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_creator_partner_links_client` (`client_id`),
    UNIQUE KEY `uq_creator_partner_links_partner` (`partner_id`),
    KEY `idx_creator_partner_links_creator` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- M5.5.3: Snapshot referral eligibility at order creation.
ALTER TABLE `orders`
    ADD COLUMN `partners_referral_context_expires_at` DATETIME(3) NULL DEFAULT NULL
        AFTER `partners_referral_context`,
    ADD COLUMN `partners_referral_order_created_at` DATETIME(3) NULL DEFAULT NULL
        AFTER `partners_referral_context_expires_at`;

-- Black Friday: announcement copy and campaign timer window.
ALTER TABLE `site_theme_settings`
    ADD COLUMN `black_friday_starts_at` DATETIME NULL DEFAULT NULL,
    ADD COLUMN `black_friday_ends_at` DATETIME NULL DEFAULT NULL,
    ADD COLUMN `black_friday_text_ru` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `black_friday_text_ro` VARCHAR(255) NOT NULL DEFAULT '';
