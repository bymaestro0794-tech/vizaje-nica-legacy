CREATE TABLE IF NOT EXISTS search_query_stats (
    query_date DATE NOT NULL,
    query_text VARCHAR(100) NOT NULL,
    search_count INT UNSIGNED NOT NULL DEFAULT 1,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (query_date, query_text),
    KEY idx_search_query_date_count (query_date, search_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
