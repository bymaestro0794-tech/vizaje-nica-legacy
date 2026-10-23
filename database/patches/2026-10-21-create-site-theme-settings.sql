-- Predefined seasonal atmosphere for the storefront.
-- The active value is a safe key; theme colors and assets remain in application code.
CREATE TABLE IF NOT EXISTS `site_theme_settings` (
	`id` TINYINT UNSIGNED NOT NULL,
	`theme_key` VARCHAR(32) NOT NULL DEFAULT 'default',
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `site_theme_settings` (`id`, `theme_key`, `updated_at`)
VALUES (1, 'default', UTC_TIMESTAMP());
