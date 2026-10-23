-- Beauty selections are editorial collections, separate from offers and brands.
CREATE TABLE IF NOT EXISTS `beauty_selections` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titleRU` VARCHAR(255) NOT NULL,
    `titleRO` VARCHAR(255) NOT NULL,
    `uriRU` VARCHAR(255) NOT NULL,
    `uriRO` VARCHAR(255) NOT NULL,
    `descRU` TEXT NULL,
    `descRO` TEXT NULL,
    `textRU` MEDIUMTEXT NULL,
    `textRO` MEDIUMTEXT NULL,
    `imgRU` VARCHAR(255) NULL,
    `imgRO` VARCHAR(255) NULL,
    `seoTitleRU` VARCHAR(255) NULL,
    `seoTitleRO` VARCHAR(255) NULL,
    `seoKeywordsRU` TEXT NULL,
    `seoKeywordsRO` TEXT NULL,
    `seoDescRU` TEXT NULL,
    `seoDescRO` TEXT NULL,
    `isShown` TINYINT(1) NOT NULL DEFAULT 1,
    `sorder` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_beauty_selections_uri_ru` (`uriRU`),
    UNIQUE KEY `uniq_beauty_selections_uri_ro` (`uriRO`),
    KEY `idx_beauty_selections_visibility_order` (`isShown`, `sorder`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `beauty_selection_products` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `selection_id` INT UNSIGNED NOT NULL,
    `product_id` INT NOT NULL,
    `sorder` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_beauty_selection_product` (`selection_id`, `product_id`),
    KEY `idx_beauty_selection_products_order` (`selection_id`, `sorder`, `id`),
    CONSTRAINT `fk_beauty_selection_products_selection`
        FOREIGN KEY (`selection_id`) REFERENCES `beauty_selections` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
