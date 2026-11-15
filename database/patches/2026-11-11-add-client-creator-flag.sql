-- Apply manually after reviewing the target database and taking a backup.
-- This adds a separate creator pricing/access flag; it does not set orders.isb2b.
ALTER TABLE `clients`
    ADD COLUMN `is_creator` TINYINT(1) NOT NULL DEFAULT 0 AFTER `isb2b`;
