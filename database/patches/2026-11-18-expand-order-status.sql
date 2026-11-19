-- Allow the two additional order statuses used by the admin order editor.
-- The patch preserves the current ENUM values and column null/default settings.
-- It is intentionally separate from the historical empty-status backfill.

SET @orders_status_type := (
    SELECT `COLUMN_TYPE`
    FROM `information_schema`.`COLUMNS`
    WHERE `TABLE_SCHEMA` = DATABASE()
      AND `TABLE_NAME` = 'orders'
      AND `COLUMN_NAME` = 'status'
    LIMIT 1
);

SET @orders_status_nullable := (
    SELECT `IS_NULLABLE`
    FROM `information_schema`.`COLUMNS`
    WHERE `TABLE_SCHEMA` = DATABASE()
      AND `TABLE_NAME` = 'orders'
      AND `COLUMN_NAME` = 'status'
    LIMIT 1
);

SET @orders_status_default := (
    SELECT `COLUMN_DEFAULT`
    FROM `information_schema`.`COLUMNS`
    WHERE `TABLE_SCHEMA` = DATABASE()
      AND `TABLE_NAME` = 'orders'
      AND `COLUMN_NAME` = 'status'
    LIMIT 1
);

SET @orders_status_enum_type := IF(
    @orders_status_type LIKE 'enum(%',
    CONCAT(
        LEFT(@orders_status_type, CHAR_LENGTH(@orders_status_type) - 1),
        IF(@orders_status_type LIKE '%''courier''%', '', ',''courier'''),
        IF(@orders_status_type LIKE '%''delivered''%', '', ',''delivered'''),
        ')'
    ),
    NULL
);

SET @orders_status_default_sql := IF(
    @orders_status_default IS NULL,
    IF(@orders_status_nullable = 'YES', ' DEFAULT NULL', ' DEFAULT ''new'''),
    IF(
        @orders_status_default IN (
            'new',
            'progress',
            'courier',
            'delivered',
            'finished',
            'canceled'
        ),
        CONCAT(' DEFAULT ', QUOTE(@orders_status_default)),
        ' DEFAULT ''new'''
    )
);

SET @orders_status_patch_sql := IF(
    @orders_status_enum_type IS NULL,
    'SELECT 1',
    CONCAT(
        'ALTER TABLE `orders` MODIFY COLUMN `status` ',
        @orders_status_enum_type,
        IF(@orders_status_nullable = 'NO', ' NOT NULL', ' NULL'),
        @orders_status_default_sql
    )
);

PREPARE orders_status_patch_stmt FROM @orders_status_patch_sql;
EXECUTE orders_status_patch_stmt;
DEALLOCATE PREPARE orders_status_patch_stmt;

SELECT `COLUMN_TYPE`, `IS_NULLABLE`, `COLUMN_DEFAULT`
FROM `information_schema`.`COLUMNS`
WHERE `TABLE_SCHEMA` = DATABASE()
  AND `TABLE_NAME` = 'orders'
  AND `COLUMN_NAME` = 'status';
