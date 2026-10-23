-- Добавляет явную отметку подарка рулетки в строке заказа.
-- Выполнять после создания таблиц рулетки и gifts.
SET @gift_marker_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'orders_products'
      AND COLUMN_NAME = 'roulette_gift_id'
);

SET @gift_marker_sql := IF(
    @gift_marker_exists = 0,
    'ALTER TABLE `orders_products` ADD COLUMN `roulette_gift_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `options`',
    'SELECT 1'
);

PREPARE gift_marker_statement FROM @gift_marker_sql;
EXECUTE gift_marker_statement;
DEALLOCATE PREPARE gift_marker_statement;
