-- ============================================================
-- ServusPBX Medical 1.5.7
-- device_model VARCHAR Fix
-- ============================================================
--
-- Problem:
-- Beim Anlegen eines Gigaset P85x/P82x:
-- Data truncated for column 'device_model'
--
-- Ursache:
-- device_model ist in mindestens einer Tabelle noch ENUM.
--
-- Fix:
-- Alle bekannten device_model-Spalten auf VARCHAR(40) umstellen.
-- ============================================================

SET @c := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='ps_endpoints'
    AND COLUMN_NAME='device_model'
);
SET @s := IF(@c=1,
  "ALTER TABLE ps_endpoints MODIFY device_model VARCHAR(40) NOT NULL DEFAULT ''",
  "SELECT 1"
);
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='spbx_devices'
    AND COLUMN_NAME='device_model'
);
SET @s := IF(@c=1,
  "ALTER TABLE spbx_devices MODIFY device_model VARCHAR(40) NOT NULL DEFAULT ''",
  "SELECT 1"
);
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='spbx_extensions'
    AND COLUMN_NAME='device_model'
);
SET @s := IF(@c=1,
  "ALTER TABLE spbx_extensions MODIFY device_model VARCHAR(40) NOT NULL DEFAULT ''",
  "SELECT 1"
);
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('device_model_varchar_fix', '1.5.7', 'device_model wurde auf VARCHAR(40) umgestellt, damit neue Telefonmodelle ohne ENUM-Patch funktionieren')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
