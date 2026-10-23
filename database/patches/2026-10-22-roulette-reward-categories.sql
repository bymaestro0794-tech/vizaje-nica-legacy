-- Prepare the roulette admin for non-discount reward categories.
-- Discount and free-shipping rewards are currently redeemable by checkout;
-- gift, promo-code, bonus and surprise rows stay disabled until their
-- fulfillment flow is connected.
SET NAMES utf8mb4;

-- Make the migration safe to run again in phpMyAdmin after a partial import.
-- MySQL versions shipped with XAMPP may not support ADD COLUMN IF NOT EXISTS.
SET @reward_payload_column_exists := (
	SELECT COUNT(*)
	FROM information_schema.COLUMNS
	WHERE TABLE_SCHEMA = DATABASE()
	  AND TABLE_NAME = 'roulette_prizes'
	  AND COLUMN_NAME = 'reward_payload'
);
SET @reward_payload_sql := IF(
	@reward_payload_column_exists = 0,
	'ALTER TABLE `roulette_prizes` ADD COLUMN `reward_payload` VARCHAR(120) NULL DEFAULT NULL AFTER `reward_value`',
	'SELECT 1'
);
PREPARE reward_payload_statement FROM @reward_payload_sql;
EXECUTE reward_payload_statement;
DEALLOCATE PREPARE reward_payload_statement;

INSERT IGNORE INTO `roulette_prizes`
	(`code`, `titleRU`, `titleRO`, `reward_type`, `reward_value`, `reward_payload`, `probability`, `is_enabled`, `sort_order`, `updated_at`)
VALUES
	('promo_code', 'Промокод', 'Cod promoțional', 'promo_code', NULL, NULL, NULL, 0, 7, UTC_TIMESTAMP());
