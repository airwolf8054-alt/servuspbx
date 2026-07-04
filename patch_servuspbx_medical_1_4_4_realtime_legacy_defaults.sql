-- ============================================================
-- ServusPBX Medical 1.4.4
-- Realtime Legacy Defaults + display_name Fix
-- ============================================================
--
-- Ziel:
-- Alte Realtime-Tabellen enthalten teils NOT NULL Spalten ohne DEFAULT.
-- Das führt bei modernen Inserts zu Fehlern wie:
-- Field 'display_name' doesn't have a default value
--
-- Dieser Patch entschärft bekannte Legacy-Spalten mit Defaults.
-- ============================================================

-- ps_endpoints Legacy-Spalten
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ps_endpoints' AND COLUMN_NAME='extension');
SET @s := IF(@c=1, "ALTER TABLE ps_endpoints MODIFY extension VARCHAR(80) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ps_endpoints' AND COLUMN_NAME='display_name');
SET @s := IF(@c=1, "ALTER TABLE ps_endpoints MODIFY display_name VARCHAR(120) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ps_endpoints' AND COLUMN_NAME='device_type');
SET @s := IF(@c=1, "ALTER TABLE ps_endpoints MODIFY device_type VARCHAR(40) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ps_endpoints' AND COLUMN_NAME='mailboxes');
SET @s := IF(@c=1, "ALTER TABLE ps_endpoints MODIFY mailboxes VARCHAR(120) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ps_aors Legacy-Spalten
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ps_aors' AND COLUMN_NAME='extension');
SET @s := IF(@c=1, "ALTER TABLE ps_aors MODIFY extension VARCHAR(80) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ps_registrations Legacy-Spalten
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ps_registrations' AND COLUMN_NAME='extension');
SET @s := IF(@c=1, "ALTER TABLE ps_registrations MODIFY extension VARCHAR(80) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Bestehenden A1-Trunk-Endpunkt sauber befüllen, falls die Spalten existieren.
UPDATE ps_endpoints
SET display_name = CASE WHEN COALESCE(display_name,'')='' THEN 'A1 Hauptnummer' ELSE display_name END
WHERE id='trunk-a1-43312423826'
  AND EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='ps_endpoints'
      AND COLUMN_NAME='display_name'
  );

UPDATE ps_endpoints
SET extension = CASE WHEN COALESCE(extension,'')='' THEN 'trunk-a1-43312423826' ELSE extension END
WHERE id='trunk-a1-43312423826'
  AND EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='ps_endpoints'
      AND COLUMN_NAME='extension'
  );

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('realtime_legacy_defaults', '1.4.4', 'Legacy NOT NULL Spalten in ps_endpoints/ps_aors/ps_registrations erhalten Defaults'),
('trunk_save_ps_endpoints_display_name_fix', '1.4.4', 'ps_endpoints.display_name wird beim Trunk-Speichern befüllt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
