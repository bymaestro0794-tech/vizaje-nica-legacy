-- Условные подарки: например, купи 2 товара выбранных брендов и получи подарок.
-- gifts остаётся общим каталогом подарков для рулетки и промо-условий.
CREATE TABLE IF NOT EXISTS `gift_promotions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titleRU` VARCHAR(255) NOT NULL,
    `titleRO` VARCHAR(255) NOT NULL,
    `descriptionRU` TEXT NULL,
    `descriptionRO` TEXT NULL,
    `gift_id` INT UNSIGNED NOT NULL,
    `offer_id` INT UNSIGNED NULL DEFAULT NULL,
    `condition_qty` INT UNSIGNED NOT NULL DEFAULT 2,
    `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `show_on_site` TINYINT(1) NOT NULL DEFAULT 1,
    `is_online` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `date_from` DATE NULL DEFAULT NULL,
    `date_to` DATE NULL DEFAULT NULL,
    `sorder` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_gift_promotions_active` (`is_enabled`, `show_on_site`, `is_online`, `date_from`, `date_to`, `sorder`, `id`),
    KEY `idx_gift_promotions_gift` (`gift_id`),
    KEY `idx_gift_promotions_offer` (`offer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gift_promotion_brands` (
    `promotion_id` INT UNSIGNED NOT NULL,
    `brand_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`promotion_id`, `brand_id`),
    KEY `idx_gift_promotion_brands_brand` (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
