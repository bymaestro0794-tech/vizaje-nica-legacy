-- NULL means automatic completeness-based indexing; 1/0 are manual overrides.
ALTER TABLE `products`
    ADD COLUMN `seo_indexable` TINYINT(1) NULL DEFAULT NULL
    AFTER `isShown`;
