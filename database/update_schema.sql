-- SmartGov Market - Database Update Migration Script
-- File: database/update_schema.sql

USE `smartgov_market`;

-- Add document_path column to service_applications if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = "service_applications";
SET @columnname = "document_path";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 1",
  "ALTER TABLE `service_applications` ADD COLUMN `document_path` VARCHAR(255) NULL AFTER `remarks`;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
