CREATE TABLE IF NOT EXISTS `user_libraries` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `owner_user_id` INT NOT NULL,
  `client_name` VARCHAR(255) NOT NULL,
  `size_bytes` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `sha256` CHAR(64) NOT NULL,
  `uploaded_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_user_libraries_owner` (`owner_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @column_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'loader_catalog' AND COLUMN_NAME = 'owner_user_id'
);
SET @migration := IF(@column_exists = 0,
  'ALTER TABLE `loader_catalog` ADD COLUMN `owner_user_id` INT DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @migration;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
