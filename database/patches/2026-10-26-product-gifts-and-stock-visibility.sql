-- Подарочные товары legacy.
-- Такой товар заполняется как обычный, но не публикуется в каталоге и
-- может попасть в корзину только через roulette/gift promotion flow.
-- Патч идемпотентный: его можно безопасно повторить на dev/prod.
SET @product_gift_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'products'
      AND COLUMN_NAME = 'isGift'
);

SET @product_gift_column_sql := IF(
    @product_gift_column_exists = 0,
    'ALTER TABLE `products` ADD COLUMN `isGift` TINYINT(1) NOT NULL DEFAULT 0 AFTER `isShown`',
    'SELECT 1'
);

PREPARE product_gift_column_statement FROM @product_gift_column_sql;
EXECUTE product_gift_column_statement;
DEALLOCATE PREPARE product_gift_column_statement;

-- В карточке товара/подарочной акции можно показывать оставшееся время,
-- остаток товара или ничего. Старые записи сохраняют текущий таймер.
SET @offer_progress_table_exists := (
    SELECT COUNT(*)
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'offers'
);

SET @offer_progress_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'offers'
      AND COLUMN_NAME = 'progress_mode'
);

SET @offer_progress_sql := IF(
    @offer_progress_table_exists = 1 AND @offer_progress_column_exists = 0,
    'ALTER TABLE `offers` ADD COLUMN `progress_mode` VARCHAR(16) NOT NULL DEFAULT ''time'' AFTER `is_online`',
    'SELECT 1'
);

PREPARE offer_progress_statement FROM @offer_progress_sql;
EXECUTE offer_progress_statement;
DEALLOCATE PREPARE offer_progress_statement;

SET @gift_promotion_progress_table_exists := (
    SELECT COUNT(*)
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'gift_promotions'
);

SET @gift_promotion_progress_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'gift_promotions'
      AND COLUMN_NAME = 'progress_mode'
);

SET @gift_promotion_progress_sql := IF(
    @gift_promotion_progress_table_exists = 1 AND @gift_promotion_progress_column_exists = 0,
    'ALTER TABLE `gift_promotions` ADD COLUMN `progress_mode` VARCHAR(16) NOT NULL DEFAULT ''time'' AFTER `is_online`',
    'SELECT 1'
);

PREPARE gift_promotion_progress_statement FROM @gift_promotion_progress_sql;
EXECUTE gift_promotion_progress_statement;
DEALLOCATE PREPARE gift_promotion_progress_statement;
