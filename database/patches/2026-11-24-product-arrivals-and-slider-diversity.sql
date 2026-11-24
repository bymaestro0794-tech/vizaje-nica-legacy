-- Track first catalog publication for automatic new-arrival badges and listings.
-- Safe to run again if the column or index already exists.
SET @arrival_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'products'
       AND COLUMN_NAME = 'catalog_published_at') = 0,
    'ALTER TABLE `products` ADD COLUMN `catalog_published_at` DATETIME NULL DEFAULT NULL AFTER `is_new`',
    'DO 0'
);
PREPARE arrival_patch_stmt FROM @arrival_patch_sql;
EXECUTE arrival_patch_stmt;
DEALLOCATE PREPARE arrival_patch_stmt;

SET @arrival_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'products'
       AND INDEX_NAME = 'idx_products_catalog_published_at') = 0,
    'ALTER TABLE `products` ADD INDEX `idx_products_catalog_published_at` (`catalog_published_at`, `id`)',
    'DO 0'
);
PREPARE arrival_patch_stmt FROM @arrival_patch_sql;
EXECUTE arrival_patch_stmt;
DEALLOCATE PREPARE arrival_patch_stmt;

-- Seed only rows without a date. The 20 newest eligible products are dated first;
-- all remaining legacy products are then moved outside the 7-day window.
UPDATE `products`
SET `catalog_published_at` = DATE_SUB(NOW(), INTERVAL 1 DAY)
WHERE `catalog_published_at` IS NULL
  AND `id` IN (
      SELECT `recent_arrivals`.`product_id`
      FROM (
          SELECT `p`.`id` AS `product_id`
          FROM `products` AS `p`
          WHERE `p`.`isShown` = 1
            AND EXISTS (
                SELECT 1 FROM `products_img` AS `pi`
                WHERE `pi`.`product_id` = `p`.`id`
            )
            AND (
                (`p`.`price` > 0 AND `p`.`on_stock` > 0)
                OR EXISTS (
                    SELECT 1 FROM `products_variable` AS `pv`
                    WHERE `pv`.`product_id` = `p`.`id`
                      AND `pv`.`isShown` = 1
                      AND `pv`.`price` > 0
                      AND `pv`.`qty` > 0
                )
            )
          ORDER BY `p`.`id` DESC
          LIMIT 20
      ) AS `recent_arrivals`
  );

UPDATE `products`
SET `catalog_published_at` = DATE_SUB(NOW(), INTERVAL 8 DAY)
WHERE `catalog_published_at` IS NULL;

-- Repair a prior run that aged every legacy row before failing to mark the newest 20.
-- The 7-to-9-day guard makes this safe to re-run later without refreshing old badges.
UPDATE `products`
SET `catalog_published_at` = DATE_SUB(NOW(), INTERVAL 1 DAY)
WHERE `catalog_published_at` BETWEEN DATE_SUB(NOW(), INTERVAL 9 DAY)
                                 AND DATE_SUB(NOW(), INTERVAL 7 DAY)
  AND `id` IN (
      SELECT `recent_arrivals`.`product_id`
      FROM (
          SELECT `p`.`id` AS `product_id`
          FROM `products` AS `p`
          WHERE `p`.`isShown` = 1
            AND EXISTS (
                SELECT 1 FROM `products_img` AS `pi`
                WHERE `pi`.`product_id` = `p`.`id`
            )
            AND (
                (`p`.`price` > 0 AND `p`.`on_stock` > 0)
                OR EXISTS (
                    SELECT 1 FROM `products_variable` AS `pv`
                    WHERE `pv`.`product_id` = `p`.`id`
                      AND `pv`.`isShown` = 1
                      AND `pv`.`price` > 0
                      AND `pv`.`qty` > 0
                )
            )
          ORDER BY `p`.`id` DESC
          LIMIT 20
      ) AS `recent_arrivals`
  );
