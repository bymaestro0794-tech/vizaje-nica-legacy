SET @search_stats_has_result_count_total := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'search_query_stats'
      AND COLUMN_NAME = 'result_count_total'
);
SET @search_stats_result_count_total_sql := IF(
    @search_stats_has_result_count_total = 0,
    'ALTER TABLE `search_query_stats` ADD COLUMN `result_count_total` BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER `result_click_count`',
    'SELECT 1'
);
PREPARE search_stats_result_count_total_stmt FROM @search_stats_result_count_total_sql;
EXECUTE search_stats_result_count_total_stmt;
DEALLOCATE PREPARE search_stats_result_count_total_stmt;

SET @search_stats_has_result_count_samples := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'search_query_stats'
      AND COLUMN_NAME = 'result_count_samples'
);
SET @search_stats_result_count_samples_sql := IF(
    @search_stats_has_result_count_samples = 0,
    'ALTER TABLE `search_query_stats` ADD COLUMN `result_count_samples` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `result_count_total`',
    'SELECT 1'
);
PREPARE search_stats_result_count_samples_stmt FROM @search_stats_result_count_samples_sql;
EXECUTE search_stats_result_count_samples_stmt;
DEALLOCATE PREPARE search_stats_result_count_samples_stmt;

SET @search_stats_has_add_to_cart_count := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'search_query_stats'
      AND COLUMN_NAME = 'add_to_cart_count'
);
SET @search_stats_add_to_cart_count_sql := IF(
    @search_stats_has_add_to_cart_count = 0,
    'ALTER TABLE `search_query_stats` ADD COLUMN `add_to_cart_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `result_count_samples`',
    'SELECT 1'
);
PREPARE search_stats_add_to_cart_count_stmt FROM @search_stats_add_to_cart_count_sql;
EXECUTE search_stats_add_to_cart_count_stmt;
DEALLOCATE PREPARE search_stats_add_to_cart_count_stmt;
