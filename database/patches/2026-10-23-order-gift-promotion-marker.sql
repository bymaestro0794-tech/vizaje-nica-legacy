-- Явная отметка подарков в строке заказа.
-- Патч самодостаточный: если маркер рулетки ещё не применялся,
-- он будет создан перед gift_promotion_id.
SET @roulette_gift_marker_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'orders_products'
      AND COLUMN_NAME = 'roulette_gift_id'
);

SET @roulette_gift_marker_sql := IF(
    @roulette_gift_marker_exists = 0,
    'ALTER TABLE `orders_products` ADD COLUMN `roulette_gift_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `options`',
    'SELECT 1'
);

PREPARE roulette_gift_marker_statement FROM @roulette_gift_marker_sql;
EXECUTE roulette_gift_marker_statement;
DEALLOCATE PREPARE roulette_gift_marker_statement;

SET @promotion_gift_marker_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'orders_products'
      AND COLUMN_NAME = 'gift_promotion_id'
);

SET @promotion_gift_marker_sql := IF(
    @promotion_gift_marker_exists = 0,
    'ALTER TABLE `orders_products` ADD COLUMN `gift_promotion_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `roulette_gift_id`',
    'SELECT 1'
);

PREPARE promotion_gift_marker_statement FROM @promotion_gift_marker_sql;
EXECUTE promotion_gift_marker_statement;
DEALLOCATE PREPARE promotion_gift_marker_statement;
