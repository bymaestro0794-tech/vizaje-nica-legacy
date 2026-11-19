-- Local Legacy creator links and 7-day, single-order attribution in the first-party session.
-- No IP address, user agent, or stable visitor identity is stored.
-- Apply manually after backup; no commission is calculated by this patch.
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

ALTER TABLE `orders`
    ADD COLUMN `legacy_creator_referral_attribution_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    ADD UNIQUE KEY `uq_orders_legacy_creator_attribution` (`legacy_creator_referral_attribution_id`);
