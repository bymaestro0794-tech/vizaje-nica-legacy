-- Add restricted roles to admin accounts. Existing admins retain full access.
SET @admin_role_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'admins'
      AND COLUMN_NAME = 'role'
);

SET @admin_role_patch_sql := IF(
    @admin_role_column_exists = 0,
    'ALTER TABLE `admins` ADD COLUMN `role` ENUM(''admin'', ''operator'', ''accountant'') NOT NULL DEFAULT ''admin'' AFTER `login`',
    'SELECT 1'
);

PREPARE admin_role_patch_stmt FROM @admin_role_patch_sql;
EXECUTE admin_role_patch_stmt;
DEALLOCATE PREPARE admin_role_patch_stmt;
