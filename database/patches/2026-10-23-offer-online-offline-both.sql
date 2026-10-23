-- Extend the existing offer channel flag:
-- 0 = offline, 1 = online (default), 2 = both online and offline.
ALTER TABLE `offers`
    MODIFY COLUMN `is_online` TINYINT UNSIGNED NOT NULL DEFAULT 1;

UPDATE `offers`
SET `is_online` = 1
WHERE `is_online` NOT IN (0, 1, 2);
