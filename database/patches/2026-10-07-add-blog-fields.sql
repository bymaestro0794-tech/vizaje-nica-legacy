-- Extend existing articles into a separate SEO blog without changing news records.
ALTER TABLE `articles`
    ADD COLUMN `isBlog` TINYINT(1) NOT NULL DEFAULT 0 AFTER `isShown`,
    ADD COLUMN `tagsRU` VARCHAR(500) NOT NULL DEFAULT '' AFTER `seoDescRU`,
    ADD COLUMN `tagsRO` VARCHAR(500) NOT NULL DEFAULT '' AFTER `seoDescRO`;

-- One like per browser session and article. Only a one-way hash is stored.
CREATE TABLE IF NOT EXISTS `article_likes` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `article_id` INT NOT NULL,
    `visitor_key` CHAR(64) NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_article_likes_visitor` (`article_id`, `visitor_key`),
    KEY `idx_article_likes_article` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
