-- ============================================================
-- ServusPBX Medical 1.5.1
-- A1 Outbound From-Domain Fix
-- ============================================================
--
-- Problem im SIP INVITE:
-- From/Contact/PAI verwendeten 192.168.0.10 als Domain.
--
-- Fix:
-- ps_endpoints.from_user    = Hauptnummer
-- ps_endpoints.from_domain  = siptrunk.a1.net
-- ps_endpoints.contact_user = Hauptnummer
-- sofern die Spalten im Schema existieren.
-- ============================================================

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ps_endpoints' AND COLUMN_NAME='from_user');
SET @s := IF(@c=1, "UPDATE ps_endpoints SET from_user='+43312423826' WHERE id='trunk-a1-43312423826'", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ps_endpoints' AND COLUMN_NAME='from_domain');
SET @s := IF(@c=1, "UPDATE ps_endpoints SET from_domain='siptrunk.a1.net' WHERE id='trunk-a1-43312423826'", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ps_endpoints' AND COLUMN_NAME='contact_user');
SET @s := IF(@c=1, "UPDATE ps_endpoints SET contact_user='+43312423826' WHERE id='trunk-a1-43312423826'", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Sicherheit: A1 inbound weiterhin ohne Auth und auf incoming.
UPDATE ps_endpoints
SET auth=NULL,
    context='incoming'
WHERE id='trunk-a1-43312423826';

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('a1_outbound_from_domain_fix', '1.5.1', 'A1 Trunk setzt from_user/from_domain/contact_user für ausgehende INVITEs')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Danach:
-- asterisk -rx "pjsip reload"
-- Test:
-- asterisk -rx "pjsip show endpoint trunk-a1-43312423826"
-- Erwartung:
-- from_user    +43312423826
-- from_domain  siptrunk.a1.net
-- contact_user +43312423826, falls Spalte vorhanden
-- ============================================================
