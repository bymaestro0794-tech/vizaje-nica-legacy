-- Apply manually after reviewing the target database and taking a backup.
-- These are storefront preferences only and do not change order B2B status.
ALTER TABLE `clients`
    ADD COLUMN `creator_widget_enabled` TINYINT(1) NOT NULL DEFAULT 1 AFTER `isb2b`,
    ADD COLUMN `creator_b2c_mode` TINYINT(1) NOT NULL DEFAULT 0 AFTER `creator_widget_enabled`;
