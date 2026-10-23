-- Подключает хранение gift_id в истории выигрыша рулетки.
-- Выполнять после 2026-10-16-roulette-gameplay-v1.sql.
SET NAMES utf8mb4;

ALTER TABLE `roulette_spins`
    MODIFY COLUMN `reward_value_snapshot` DECIMAL(10,2) NULL DEFAULT NULL;

SET @gift_snapshot_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'roulette_spins'
      AND COLUMN_NAME = 'reward_payload_snapshot'
);
SET @gift_snapshot_sql := IF(
    @gift_snapshot_exists = 0,
    'ALTER TABLE `roulette_spins` ADD COLUMN `reward_payload_snapshot` VARCHAR(120) NULL DEFAULT NULL AFTER `reward_value_snapshot`',
    'SELECT 1'
);
PREPARE gift_snapshot_statement FROM @gift_snapshot_sql;
EXECUTE gift_snapshot_statement;
DEALLOCATE PREPARE gift_snapshot_statement;
