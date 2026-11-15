-- Admin-managed informational and promotional dialogs.
-- Apply manually in each environment; this patch is intentionally not run by the application.
CREATE TABLE IF NOT EXISTS `site_dialogs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title_ru` VARCHAR(180) NOT NULL,
  `title_ro` VARCHAR(180) NOT NULL DEFAULT '',
  `text_ru` TEXT NOT NULL,
  `text_ro` TEXT NOT NULL,
  `image` VARCHAR(255) NOT NULL DEFAULT '',
  `button_text_ru` VARCHAR(80) NOT NULL DEFAULT '',
  `button_text_ro` VARCHAR(80) NOT NULL DEFAULT '',
  `target_url_ru` VARCHAR(1000) NOT NULL DEFAULT '',
  `target_url_ro` VARCHAR(1000) NOT NULL DEFAULT '',
  `audience` ENUM('all', 'guest', 'customer') NOT NULL DEFAULT 'all',
  `display_delay_seconds` SMALLINT UNSIGNED NOT NULL DEFAULT 45,
  `priority` SMALLINT NOT NULL DEFAULT 0,
  `starts_at` DATETIME NULL DEFAULT NULL,
  `ends_at` DATETIME NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_site_dialogs_active_window` (`is_active`, `starts_at`, `ends_at`, `priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
