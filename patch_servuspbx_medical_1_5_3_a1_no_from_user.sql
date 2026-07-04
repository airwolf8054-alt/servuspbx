-- ============================================================
-- ServusPBX Medical 1.5.3
-- A1 CLIP no Screening: from_user leer lassen
-- ============================================================
--
-- Erkenntnis:
-- ps_endpoints.from_user blockiert bei A1 CLIP no Screening.
--
-- Soll:
-- - from_user leer
-- - from_domain = siptrunk.a1.net
-- - contact_user = Hauptnummer
-- - CallerID/PAI/RPID darf weiterhin Hauptnummer + Durchwahl sein
-- ============================================================

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ps_endpoints' AND COLUMN_NAME='from_user');
SET @s := IF(@c=1, "UPDATE ps_endpoints SET from_user=NULL WHERE id LIKE 'trunk-a1-%'", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ps_endpoints' AND COLUMN_NAME='from_domain');
SET @s := IF(@c=1, "UPDATE ps_endpoints SET from_domain='siptrunk.a1.net' WHERE id LIKE 'trunk-a1-%'", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ps_endpoints' AND COLUMN_NAME='contact_user');
SET @s := IF(@c=1, "UPDATE ps_endpoints SET contact_user='+43312423826' WHERE id='trunk-a1-43312423826'", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('a1_no_from_user', '1.5.3', 'A1 Trunks setzen from_user nicht, damit CLIP no Screening funktioniert')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
