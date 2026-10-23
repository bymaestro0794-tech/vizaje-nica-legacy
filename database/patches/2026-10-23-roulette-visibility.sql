-- Separate public visibility from the ability to accept new roulette spins.
-- Existing configured roulette settings remain visible by default.
ALTER TABLE `roulette_settings`
    ADD COLUMN `is_visible` TINYINT(1) NOT NULL DEFAULT 1 AFTER `is_enabled`;

UPDATE `roulette_settings`
SET `is_visible` = 1
WHERE `id` = 1;
