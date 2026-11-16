-- Additive Legacy referral-attribution eligibility snapshot.
-- Review and apply manually after backup; this file is not executed by the application.
-- Apply only after 2026-11-12-create-partners-referral-analytics-outbox.sql.

ALTER TABLE `orders`
    ADD COLUMN `partners_referral_context_expires_at` DATETIME(3) NULL DEFAULT NULL
        AFTER `partners_referral_context`,
    ADD COLUMN `partners_referral_order_created_at` DATETIME(3) NULL DEFAULT NULL
        AFTER `partners_referral_context_expires_at`;
