-- Add an admin-managed online/offline label for each promotion.
-- Existing promotions default to online to preserve the current badge.
ALTER TABLE `offers`
	ADD COLUMN `is_online` TINYINT(1) NOT NULL DEFAULT 1 AFTER `sale`;
