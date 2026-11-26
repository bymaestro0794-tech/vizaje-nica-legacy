-- Track the operator responsible for each order. Unassigned orders remain NULL.
SET @order_operator_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'orders'
      AND COLUMN_NAME = 'operator_admin_id'
);

SET @order_operator_column_sql := IF(
    @order_operator_column_exists = 0,
    'ALTER TABLE `orders` ADD COLUMN `operator_admin_id` INT NULL DEFAULT NULL AFTER `status`',
    'SELECT 1'
);

PREPARE order_operator_column_stmt FROM @order_operator_column_sql;
EXECUTE order_operator_column_stmt;
DEALLOCATE PREPARE order_operator_column_stmt;

SET @order_operator_index_exists := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'orders'
      AND INDEX_NAME = 'idx_orders_operator_status'
);

SET @order_operator_index_sql := IF(
    @order_operator_index_exists = 0,
    'ALTER TABLE `orders` ADD KEY `idx_orders_operator_status` (`operator_admin_id`, `status`)',
    'SELECT 1'
);

PREPARE order_operator_index_stmt FROM @order_operator_index_sql;
EXECUTE order_operator_index_stmt;
DEALLOCATE PREPARE order_operator_index_stmt;
