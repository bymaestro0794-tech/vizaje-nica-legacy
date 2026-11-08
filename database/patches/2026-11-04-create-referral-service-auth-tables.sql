-- M5.1A service authentication storage.
-- Additive only: this patch does not modify existing catalog/order tables.
-- Apply manually after review; this file is not executed by the application.

CREATE TABLE IF NOT EXISTS `referral_service_nonces` (
    `key_id` VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `nonce` CHAR(22) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `received_at` DATETIME(6) NOT NULL,
    `expires_at` DATETIME(6) NOT NULL,
    PRIMARY KEY (`key_id`, `nonce`),
    KEY `idx_referral_service_nonces_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin;

-- Supporting atomic per-service/minute rate counter required by M5.1 §6.
CREATE TABLE IF NOT EXISTS `referral_service_rate_limits` (
    `principal` VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `window_start` DATETIME NOT NULL,
    `request_count` INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`principal`, `window_start`),
    KEY `idx_referral_service_rate_limits_window_start` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin;
