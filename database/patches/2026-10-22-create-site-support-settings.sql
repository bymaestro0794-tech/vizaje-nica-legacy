CREATE TABLE IF NOT EXISTS site_support_settings (
    id TINYINT UNSIGNED NOT NULL,
    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
    telegram_contact VARCHAR(255) NOT NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO site_support_settings (id, is_enabled, telegram_contact, updated_at)
VALUES (1, 1, '@vizaje_nica_support_bot', UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE id = VALUES(id);
