-- SHAI Panel -> ASTRAL Loader migration (additive).
-- Back up the database first. Existing users, keys_code, lib and devices are untouched.
-- Safe to run more than once; existing catalog rows are not dropped.
CREATE TABLE IF NOT EXISTS `loader_catalog` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `game_type` VARCHAR(32) NOT NULL,
  `game_package` VARCHAR(190) NOT NULL,
  `architecture` VARCHAR(16) NOT NULL DEFAULT '64bit',
  `download_url` VARCHAR(1024) NOT NULL,
  `jni_key` VARCHAR(255) NOT NULL DEFAULT '',
  `version_name` VARCHAR(50) NOT NULL DEFAULT '',
  `logo_url` VARCHAR(1024) NOT NULL DEFAULT '',
  `status` VARCHAR(16) NOT NULL DEFAULT 'DISABLED',
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 100,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_loader_catalog_sort` (`sort_order`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
