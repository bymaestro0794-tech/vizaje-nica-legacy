-- Creator commission snapshots and payout request due dates.
-- Apply after taking a database backup. Safe to re-run.

SET @vizaje_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clients' AND COLUMN_NAME = 'creator_commission_percent') = 0,
    'ALTER TABLE `clients` ADD COLUMN `creator_commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `is_creator`', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

SET @vizaje_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'creator_commission_percent') = 0,
    'ALTER TABLE `orders` ADD COLUMN `creator_commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

SET @vizaje_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'creator_commission_amount') = 0,
    'ALTER TABLE `orders` ADD COLUMN `creator_commission_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

SET @vizaje_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'creator_payout_requests' AND COLUMN_NAME = 'expected_payment_date') = 0,
    'ALTER TABLE `creator_payout_requests` ADD COLUMN `expected_payment_date` DATE NULL AFTER `comment`', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

SET @vizaje_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'creator_payout_requests' AND COLUMN_NAME = 'amount_mdl' AND COLUMN_TYPE LIKE '%decimal%') = 0,
    'ALTER TABLE `creator_payout_requests` MODIFY COLUMN `amount_mdl` DECIMAL(12,2) UNSIGNED NOT NULL', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

SET @vizaje_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'creator_payout_requests' AND COLUMN_NAME = 'status' AND COLUMN_TYPE NOT LIKE '%paid%') = 1,
    'ALTER TABLE `creator_payout_requests` MODIFY COLUMN `status` ENUM(''pending'', ''approved'', ''rejected'', ''paid'') NOT NULL DEFAULT ''pending''', 'SELECT 1');
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql; EXECUTE vizaje_patch_stmt; DEALLOCATE PREPARE vizaje_patch_stmt;

UPDATE `creator_payout_requests`
SET `expected_payment_date` = DATE_ADD(LAST_DAY(`created_at`), INTERVAL 15 DAY)
WHERE `expected_payment_date` IS NULL;
