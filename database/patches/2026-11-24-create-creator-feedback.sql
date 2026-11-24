-- Creator dashboard support inbox.
CREATE TABLE IF NOT EXISTS `creator_feedback_messages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `email` VARCHAR(255) NOT NULL DEFAULT '',
    `phone` VARCHAR(64) NOT NULL DEFAULT '',
    `topic` ENUM('links', 'orders', 'commission', 'payout', 'account', 'other') NOT NULL DEFAULT 'other',
    `message` TEXT NOT NULL,
    `locale` CHAR(2) NOT NULL DEFAULT 'ru',
    `status` ENUM('new', 'in_progress', 'closed') NOT NULL DEFAULT 'new',
    `admin_note` TEXT NULL,
    `reviewer_admin_id` INT NULL,
    `reviewed_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_creator_feedback_status_created` (`status`, `created_at`),
    KEY `idx_creator_feedback_client_created` (`client_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
