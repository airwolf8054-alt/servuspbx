-- ============================================================
-- ServusPBX Medical 1.4.4
-- ps_endpoints Legacy Defaults
-- ============================================================
--
-- Korrigiert:
-- - Field 'display_name' doesn't have a default value
-- - und weitere mögliche Alt-Pflichtfelder in ps_endpoints
--
-- Zusätzlich befüllt PHP ab 1.4.4 ps_endpoints schemaabhängig.
-- ============================================================

-- display_name
SET @c := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='ps_endpoints'
    AND COLUMN_NAME='display_name'
);
SET @s := IF(@c=1, "ALTER TABLE ps_endpoints MODIFY display_name VARCHAR(120) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- extension
SET @c := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='ps_endpoints'
    AND COLUMN_NAME='extension'
);
SET @s := IF(@c=1, "ALTER TABLE ps_endpoints MODIFY extension VARCHAR(80) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- callerid
SET @c := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='ps_endpoints'
    AND COLUMN_NAME='callerid'
);
SET @s := IF(@c=1, "ALTER TABLE ps_endpoints MODIFY callerid VARCHAR(160) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- mailboxes
SET @c := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='ps_endpoints'
    AND COLUMN_NAME='mailboxes'
);
SET @s := IF(@c=1, "ALTER TABLE ps_endpoints MODIFY mailboxes VARCHAR(160) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- accountcode
SET @c := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='ps_endpoints'
    AND COLUMN_NAME='accountcode'
);
SET @s := IF(@c=1, "ALTER TABLE ps_endpoints MODIFY accountcode VARCHAR(80) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- from_user
SET @c := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='ps_endpoints'
    AND COLUMN_NAME='from_user'
);
SET @s := IF(@c=1, "ALTER TABLE ps_endpoints MODIFY from_user VARCHAR(80) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- from_domain
SET @c := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='ps_endpoints'
    AND COLUMN_NAME='from_domain'
);
SET @s := IF(@c=1, "ALTER TABLE ps_endpoints MODIFY from_domain VARCHAR(160) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- contact_user
SET @c := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='ps_endpoints'
    AND COLUMN_NAME='contact_user'
);
SET @s := IF(@c=1, "ALTER TABLE ps_endpoints MODIFY contact_user VARCHAR(120) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('ps_endpoints_legacy_defaults', '1.4.4', 'Legacy NOT NULL Felder in ps_endpoints bekommen Defaults'),
('trunk_ps_endpoints_dynamic_insert', '1.4.4', 'Trunk-Speichern befüllt ps_endpoints schemaabhängig')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.4.4
