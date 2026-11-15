-- M5.4.3 — referral analytics storage.
-- Additive only. Review and apply manually to the local/dev database after backup.
-- This patch is not executed by the application and has not been applied.
-- Outbox DATETIME values are stored as UTC; the worker compares them with UTC_TIMESTAMP().

ALTER TABLE `orders`
    ADD COLUMN `partners_referral_context` VARCHAR(512)
        CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL,
    ADD COLUMN `partners_referral_items_complete` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `partners_referral_paid_at` DATETIME NULL DEFAULT NULL,
    ADD COLUMN `partners_referral_reconciled_at` DATETIME NULL DEFAULT NULL,
    ADD COLUMN `partners_referral_reconciled_state` VARCHAR(64)
        CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL,
    ADD KEY `idx_orders_partners_referral_reconcile`
        (`partners_referral_items_complete`, `partners_referral_reconciled_at`, `id`);

CREATE TABLE `partners_referral_analytics_outbox` (
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
