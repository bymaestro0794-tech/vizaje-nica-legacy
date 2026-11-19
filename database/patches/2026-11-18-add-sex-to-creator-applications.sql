-- Add the optional profile gender to creator applications.
-- Safe to rerun; apply manually after a database backup.
SET @vizaje_patch_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'creator_program_applications'
       AND COLUMN_NAME = 'sex') = 0,
    'ALTER TABLE `creator_program_applications` ADD COLUMN `sex` TINYINT UNSIGNED NULL AFTER `date_of_birth`',
    'SELECT 1'
);
PREPARE vizaje_patch_stmt FROM @vizaje_patch_sql;
EXECUTE vizaje_patch_stmt;
DEALLOCATE PREPARE vizaje_patch_stmt;
