-- SHAI Panel database schema
-- Import into the panel database (phpMyAdmin -> Import, or SQL tab).
-- Safe to run repeatedly: every statement is guarded.
--
-- The panel talks to these tables directly:
--   keys_code  - licence keys read by the game client through /connect
--   onoff      - maintenance mode
--   _ftext     - floating text / mod status
--   Feature    - per-feature switches returned to the client
--   modname    - mod name returned to the client

CREATE TABLE IF NOT EXISTS `users` (
  `id_users` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(155) DEFAULT NULL,
  `username` varchar(66) NOT NULL,
  `email` varchar(40) NOT NULL,
  `reset_link_token` varchar(255) NOT NULL DEFAULT '',
  `exp_date` varchar(250) NOT NULL DEFAULT '',
  `level` int(11) NOT NULL DEFAULT 1,
  `saldo` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `uplink` varchar(66) DEFAULT NULL,
  `password` varchar(155) NOT NULL,
  `user_ip` varchar(155) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `expiration_date` datetime DEFAULT NULL,
  `logged_in` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_users`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `referral_code` (
  `id_reff` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(128) NOT NULL,
  `Referral` varchar(25) NOT NULL,
  `level` int(11) NOT NULL,
  `set_saldo` int(11) NOT NULL DEFAULT 0,
  `used_by` varchar(66) NOT NULL DEFAULT '',
  `created_by` varchar(66) NOT NULL DEFAULT 'Owner',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `acc_expiration` datetime DEFAULT NULL,
  PRIMARY KEY (`id_reff`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `keys_code` (
  `id_keys` int(11) NOT NULL AUTO_INCREMENT,
  `game` varchar(32) NOT NULL,
  `user_key` varchar(32) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `expired_date` datetime DEFAULT NULL,
  `max_devices` int(11) DEFAULT NULL,
  `devices` mediumtext DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `registrator` varchar(32) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_keys`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Installs that imported the upstream dump have `id_keys` as a plain INT without
-- AUTO_INCREMENT. CodeIgniter reads a new key's id from insert_id(), so every
-- generated key would get id 0 and the key list would break. The column is
-- converted when that is safe. Duplicate ids are left alone because renumbering
-- them would break the history table that points at keys_id.
SET @dup_ids := (SELECT COUNT(*) FROM (SELECT `id_keys` FROM `keys_code` GROUP BY `id_keys` HAVING COUNT(*) > 1) d);
SET @auto_inc := (SELECT COUNT(*) FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'keys_code'
                    AND COLUMN_NAME = 'id_keys' AND EXTRA LIKE '%auto_increment%');
SET @has_pk := (SELECT COUNT(*) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'keys_code'
                  AND INDEX_NAME = 'PRIMARY');

SET @fix := IF(@dup_ids = 0 AND @has_pk = 0,
    'ALTER TABLE `keys_code` ADD PRIMARY KEY (`id_keys`)',
    'SELECT ''keys_code primary key left as is''');
PREPARE stmt FROM @fix; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- The primary key must exist before the column can become AUTO_INCREMENT.
SET @fix := IF(@auto_inc = 0 AND @dup_ids = 0,
    'ALTER TABLE `keys_code` MODIFY `id_keys` int(11) NOT NULL AUTO_INCREMENT',
    'SELECT ''keys_code.id_keys left as is: already AUTO_INCREMENT or duplicate ids present''');
PREPARE stmt FROM @fix; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `credit` (
  `id` int(11) NOT NULL,
  `credit` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `credit` (`id`, `credit`)
SELECT 1, 'SHAI Panel' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `credit` WHERE `id` = 1);

CREATE TABLE IF NOT EXISTS `onoff` (
  `id` int(11) NOT NULL,
  `status` varchar(5) NOT NULL DEFAULT 'off',
  `myinput` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `onoff` (`id`, `status`, `myinput`)
SELECT 1, 'off', '' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `onoff` WHERE `id` = 1);

CREATE TABLE IF NOT EXISTS `_ftext` (
  `id` int(11) NOT NULL,
  `_status` varchar(100) NOT NULL DEFAULT 'Safe',
  `_ftext` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `_ftext` (`id`, `_status`, `_ftext`)
SELECT 1, 'Safe', 'Give Feedback else Keys off' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `_ftext` WHERE `id` = 1);

CREATE TABLE IF NOT EXISTS `Feature` (
  `id` int(11) NOT NULL,
  `ESP` varchar(10) NOT NULL DEFAULT 'false',
  `Item` varchar(10) NOT NULL DEFAULT 'false',
  `SilentAim` varchar(10) NOT NULL DEFAULT 'false',
  `AIM` varchar(10) NOT NULL DEFAULT 'false',
  `BulletTrack` varchar(10) NOT NULL DEFAULT 'false',
  `Memory` varchar(10) NOT NULL DEFAULT 'false',
  `Floating` varchar(10) NOT NULL DEFAULT 'false',
  `Setting` varchar(10) NOT NULL DEFAULT 'false',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `Feature` (`id`, `ESP`, `Item`, `SilentAim`, `AIM`, `BulletTrack`, `Memory`, `Floating`, `Setting`)
SELECT 1, 'true', 'false', 'false', 'false', 'false', 'false', 'false', 'false' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `Feature` WHERE `id` = 1);

CREATE TABLE IF NOT EXISTS `modname` (
  `id` int(11) NOT NULL,
  `modname` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Legacy table: older installs kept the mod name here. The panel still falls
-- back to it, so both tables are kept in sync.
CREATE TABLE IF NOT EXISTS `mod_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modname` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `modname` (`id`, `modname`)
SELECT 1, 'SHAI Panel' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modname` WHERE `id` = 1);

INSERT INTO `mod_settings` (`id`, `modname`)
SELECT 1, (SELECT `modname` FROM `modname` WHERE `id` = 1) FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `mod_settings` WHERE `id` = 1);

CREATE TABLE IF NOT EXISTS `history` (
  `id_history` int(11) NOT NULL AUTO_INCREMENT,
  `keys_id` varchar(33) DEFAULT NULL,
  `user_do` varchar(33) DEFAULT NULL,
  `info` mediumtext NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_history`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `lib` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `file_size` varchar(32) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `Keygen_Links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `8BP_Link` varchar(255) NOT NULL DEFAULT '',
  `CODM_Link` varchar(255) NOT NULL DEFAULT '',
  `FL84_Link` varchar(255) NOT NULL DEFAULT '',
  `MLBB_Link` varchar(255) NOT NULL DEFAULT '',
  `PUBG_Link` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `Keygen_Links` (`id`, `8BP_Link`, `CODM_Link`, `FL84_Link`, `MLBB_Link`, `PUBG_Link`)
SELECT 1, '', '', '', '', '' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `Keygen_Links` WHERE `id` = 1);

-- Sample referral codes and owner accounts are intentionally not installed.
