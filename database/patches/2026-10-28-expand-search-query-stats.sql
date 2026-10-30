CREATE TABLE IF NOT EXISTS search_query_stats (
    query_date DATE NOT NULL,
    query_text VARCHAR(100) NOT NULL,
    search_count INT UNSIGNED NOT NULL DEFAULT 1,
    zero_result_count INT UNSIGNED NOT NULL DEFAULT 0,
    result_click_count INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (query_date, query_text),
    KEY idx_search_query_date_count (query_date, search_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @search_stats_has_zero_result_count := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'search_query_stats'
      AND COLUMN_NAME = 'zero_result_count'
);
SET @search_stats_zero_result_sql := IF(
    @search_stats_has_zero_result_count = 0,
    'ALTER TABLE `search_query_stats` ADD COLUMN `zero_result_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `search_count`',
    'SELECT 1'
);
PREPARE search_stats_zero_result_stmt FROM @search_stats_zero_result_sql;
EXECUTE search_stats_zero_result_stmt;
DEALLOCATE PREPARE search_stats_zero_result_stmt;

SET @search_stats_has_result_click_count := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'search_query_stats'
      AND COLUMN_NAME = 'result_click_count'
);
SET @search_stats_result_click_sql := IF(
    @search_stats_has_result_click_count = 0,
    'ALTER TABLE `search_query_stats` ADD COLUMN `result_click_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `zero_result_count`',
    'SELECT 1'
);
PREPARE search_stats_result_click_stmt FROM @search_stats_result_click_sql;
EXECUTE search_stats_result_click_stmt;
DEALLOCATE PREPARE search_stats_result_click_stmt;
