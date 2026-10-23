-- Apply after 2026-10-16-create-roulette-admin-settings.sql.
-- One-time DEV migration. It prepares the server-side game ledger but does
-- not touch orders, clients, mobile APIs, or production data.

ALTER TABLE `roulette_settings`
	ADD COLUMN `is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
	ADD COLUMN `spin_interval_hours` INT UNSIGNED NULL DEFAULT NULL;

-- Start safely: no public attempt is available until an admin configures the
-- interval, turns the feature on, and enables supported sectors totaling 100%.
UPDATE `roulette_settings`
SET `is_enabled` = 0,
	`spin_interval_hours` = NULL;

UPDATE `roulette_prizes`
SET `is_enabled` = 0,
	`probability` = NULL,
	`updated_at` = UTC_TIMESTAMP()
WHERE `reward_type` <> 'discount_percent';

CREATE TABLE IF NOT EXISTS `roulette_account_state` (
	`client_id` BIGINT UNSIGNED NOT NULL,
	`next_spin_at` DATETIME NULL DEFAULT NULL,
	`created_at` DATETIME NOT NULL,
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roulette_spins` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`client_id` BIGINT UNSIGNED NOT NULL,
	`prize_id` SMALLINT UNSIGNED NOT NULL,
	`request_id` CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
	`prize_code_snapshot` VARCHAR(40) NOT NULL,
	`reward_type_snapshot` VARCHAR(32) NOT NULL,
	`reward_value_snapshot` DECIMAL(10,2) NOT NULL,
	`titleRU_snapshot` VARCHAR(120) NOT NULL,
	`titleRO_snapshot` VARCHAR(120) NOT NULL,
	`probability_snapshot` DECIMAL(5,2) NOT NULL,
	`spun_at` DATETIME NOT NULL,
	`expires_at` DATETIME NOT NULL,
	`status` VARCHAR(16) NOT NULL DEFAULT 'active',
	`order_id` BIGINT UNSIGNED NULL DEFAULT NULL,
	`used_at` DATETIME NULL DEFAULT NULL,
	`created_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uniq_roulette_spin_request` (`client_id`, `request_id`),
	KEY `idx_roulette_spin_account_status_expiry` (`client_id`, `status`, `expires_at`),
	KEY `idx_roulette_spin_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roulette_payment_reservations` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`spin_id` BIGINT UNSIGNED NOT NULL,
	`order_id` BIGINT UNSIGNED NOT NULL,
	`paynet_external_id` VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
	`created_at` DATETIME NOT NULL,
	`reserved_until` DATETIME NOT NULL,
	`status` VARCHAR(16) NOT NULL DEFAULT 'pending',
	`resolved_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uniq_roulette_paynet_external_id` (`paynet_external_id`),
	KEY `idx_roulette_reservation_spin_status` (`spin_id`, `status`, `reserved_until`),
	KEY `idx_roulette_reservation_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
