-- Keep an audit trail of non-price product differences found during catalog import.
-- Prices and stock values are intentionally excluded from the tracked payload.
CREATE TABLE `product_import_changes` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT UNSIGNED NOT NULL,
	`product_guid` VARCHAR(128) NOT NULL,
	`action` VARCHAR(16) NOT NULL,
	`changed_fields` LONGTEXT NOT NULL,
	`created_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_product_import_changes_created_at` (`created_at`),
	KEY `idx_product_import_changes_product_id` (`product_id`),
	KEY `idx_product_import_changes_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
