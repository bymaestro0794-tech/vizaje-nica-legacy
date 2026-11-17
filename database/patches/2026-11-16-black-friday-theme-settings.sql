-- Black Friday announcement copy and its active timer window.
-- Apply after 2026-10-21-create-site-theme-settings.sql.
ALTER TABLE `site_theme_settings`
	ADD COLUMN `black_friday_starts_at` DATETIME NULL DEFAULT NULL,
	ADD COLUMN `black_friday_ends_at` DATETIME NULL DEFAULT NULL,
	ADD COLUMN `black_friday_text_ru` VARCHAR(255) NOT NULL DEFAULT '',
	ADD COLUMN `black_friday_text_ro` VARCHAR(255) NOT NULL DEFAULT '';
