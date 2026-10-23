-- Общий каталог подарков для акций и рулетки.
-- Подарок ссылается на существующий товар; данные товара не дублируются.
CREATE TABLE IF NOT EXISTS `gifts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titleRU` VARCHAR(255) NOT NULL,
    `titleRO` VARCHAR(255) NOT NULL,
    `descriptionRU` TEXT NULL,
    `descriptionRO` TEXT NULL,
    `product_id` INT NULL,
    `variant_id` INT NULL,
    `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `sorder` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_gifts_enabled_order` (`is_enabled`, `sorder`, `id`),
    KEY `idx_gifts_product` (`product_id`),
    KEY `idx_gifts_variant` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
