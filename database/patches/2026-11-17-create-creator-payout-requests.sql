-- Creator payout request intake and admin review only. No payment/transfer integration.
-- Apply manually after backup, after the creator flag patch.
CREATE TABLE IF NOT EXISTS `creator_payout_requests` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id` INT NOT NULL,
    `amount_mdl` INT UNSIGNED NOT NULL,
    `comment` VARCHAR(1000) NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `admin_comment` VARCHAR(1000) NULL,
    `reviewer_admin_id` INT NULL,
    `reviewed_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_creator_payout_status_created` (`status`, `created_at`),
    KEY `idx_creator_payout_client_status` (`client_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
