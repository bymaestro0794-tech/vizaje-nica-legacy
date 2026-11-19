-- Legacy creator program, phase 1: customer applications and decision-email outbox.
-- Prerequisite: database/patches/2026-11-11-add-client-creator-flag.sql must be applied first.
-- Apply manually after a database backup. This patch is not run by the application.

CREATE TABLE IF NOT EXISTS `creator_program_applications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id` INT NOT NULL,
    `full_name` VARCHAR(180) NOT NULL,
    `email` VARCHAR(254) NOT NULL,
    `phone` VARCHAR(32) NOT NULL,
    `date_of_birth` DATE NOT NULL,
    `sex` TINYINT UNSIGNED NULL,
    `audience_size` VARCHAR(32) NOT NULL,
    `partnership_format` VARCHAR(32) NOT NULL,
    `instagram` VARCHAR(500) NULL,
    `tiktok` VARCHAR(500) NULL,
    `other_social` VARCHAR(500) NULL,
    `content_description` TEXT NOT NULL,
    `partnership_reason` TEXT NOT NULL,
    `consent_at` DATETIME NOT NULL,
    `locale` ENUM('ru', 'ro') NOT NULL DEFAULT 'ru',
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `reviewer_admin_id` INT NULL,
    `reviewed_at` DATETIME NULL,
    `rejection_reason` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_creator_applications_status_created` (`status`, `created_at`),
    KEY `idx_creator_applications_client_status` (`client_id`, `status`),
    KEY `idx_creator_applications_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `creator_program_email_outbox` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `application_id` INT UNSIGNED NOT NULL,
    `event_type` ENUM('approved', 'rejected') NOT NULL,
    `status` ENUM('queued', 'sending', 'sent', 'failed') NOT NULL DEFAULT 'queued',
    `attempt_count` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `last_error` VARCHAR(80) NULL,
    `last_attempt_at` DATETIME NULL,
    `sent_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_creator_program_email_event` (`application_id`, `event_type`),
    KEY `idx_creator_program_email_due` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
