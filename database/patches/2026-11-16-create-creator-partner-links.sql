-- M5.5.2 Legacy creator account links.
-- Review the target schema and take a backup before manual application.
-- Intentionally no foreign key: clients.id signedness is not guaranteed across installs.
-- Stores one active Legacy client <-> Partners identity and its encrypted grant.

CREATE TABLE `creator_partner_links` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id` INT UNSIGNED NOT NULL,
    `partner_id` CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `creator_id` CHAR(10) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `account_type` VARCHAR(24) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'creator',
    `partner_status` VARCHAR(24) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `scopes` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    `grant_encrypted` VARCHAR(512) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `grant_key_id` VARCHAR(80) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `status_checked_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_creator_partner_links_client` (`client_id`),
    UNIQUE KEY `uq_creator_partner_links_partner` (`partner_id`),
    KEY `idx_creator_partner_links_creator` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
