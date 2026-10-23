-- Keep imported changes in an active queue until a product is saved or a row is manually removed.
ALTER TABLE `product_import_changes`
	ADD COLUMN `status` VARCHAR(16) NOT NULL DEFAULT 'new' AFTER `action`,
	ADD COLUMN `reviewed_at` DATETIME NULL DEFAULT NULL AFTER `created_at`,
	ADD KEY `idx_product_import_changes_status` (`status`);
