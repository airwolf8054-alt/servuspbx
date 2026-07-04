-- ============================================================
-- ServusPBX Medical 1.3.5
-- Legacy Required Columns Fix für spbx_trunks
-- ============================================================
--
-- Fehlerbild:
-- #1364 - Feld 'client_domain' hat keinen Vorgabewert
--
-- Ursache:
-- Deine bestehende spbx_trunks Tabelle enthält ältere NOT NULL Spalten
-- ohne DEFAULT, z.B. client_domain.
--
-- Dieser Patch:
-- - ergänzt fehlende neue Spalten
-- - setzt sinnvolle Defaults auf alten Pflichtspalten
-- - übernimmt den bestehenden A1-Trunk inklusive Legacy-Felder
-- ============================================================

-- Neue Spalten ohne AFTER-Abhängigkeit ergänzen.
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='trunk_name');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN trunk_name VARCHAR(120) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='name');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN name VARCHAR(120) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='main_number');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN main_number VARCHAR(40) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='username');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN username VARCHAR(120) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='auth_user');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN auth_user VARCHAR(120) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='domain');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN domain VARCHAR(160) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='server_uri');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN server_uri VARCHAR(180) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='outbound_proxy');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN outbound_proxy VARCHAR(180) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='endpoint_id');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN endpoint_id VARCHAR(120) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='contact_user');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN contact_user VARCHAR(120) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='emergency_profile');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN emergency_profile ENUM('AT','DE','CH','LI','CUSTOM') NOT NULL DEFAULT 'AT'", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='clip_no_screening');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN clip_no_screening TINYINT(1) NOT NULL DEFAULT 1", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='ext_from');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN ext_from INT DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='ext_to');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN ext_to INT DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Alte Pflichtspalten mit fehlenden Defaults entschärfen.
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='client_domain');
SET @s := IF(@c=1, "ALTER TABLE spbx_trunks MODIFY client_domain VARCHAR(160) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='client_uri');
SET @s := IF(@c=1, "ALTER TABLE spbx_trunks MODIFY client_uri VARCHAR(180) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='server_uri');
SET @s := IF(@c=1, "ALTER TABLE spbx_trunks MODIFY server_uri VARCHAR(180) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='auth_username');
SET @s := IF(@c=1, "ALTER TABLE spbx_trunks MODIFY auth_username VARCHAR(120) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='contact_user');
SET @s := IF(@c=1, "ALTER TABLE spbx_trunks MODIFY contact_user VARCHAR(120) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Bestehende leere Werte auffüllen.
UPDATE spbx_trunks
SET
  provider = COALESCE(NULLIF(provider,''),'A1'),
  trunk_name = COALESCE(NULLIF(trunk_name,''), NULLIF(name,''), 'A1 Hauptnummer'),
  name = COALESCE(NULLIF(name,''), NULLIF(trunk_name,''), 'A1 Hauptnummer'),
  main_number = COALESCE(NULLIF(main_number,''), '+43312423826'),
  username = COALESCE(NULLIF(username,''), '+43312423826'),
  auth_user = COALESCE(NULLIF(auth_user,''), NULLIF(username,''), '+43312423826'),
  domain = COALESCE(NULLIF(domain,''), 'siptrunk.a1.net'),
  server_uri = COALESCE(NULLIF(server_uri,''), 'sip:siptrunk.a1.net'),
  endpoint_id = COALESCE(NULLIF(endpoint_id,''), 'trunk-a1-43312423826'),
  contact_user = COALESCE(NULLIF(contact_user,''), '+43312423826'),
  emergency_profile = COALESCE(NULLIF(emergency_profile,''), 'AT'),
  clip_no_screening = COALESCE(clip_no_screening, 1),
  ext_from = COALESCE(ext_from, 10),
  ext_to = COALESCE(ext_to, 99);

-- Falls alte Spalten vorhanden sind, ebenso befüllen.
UPDATE spbx_trunks
SET client_domain='siptrunk.a1.net'
WHERE EXISTS (
  SELECT 1 FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='spbx_trunks'
    AND COLUMN_NAME='client_domain'
)
AND COALESCE(client_domain,'')='';

UPDATE spbx_trunks
SET client_uri='sip:+43312423826@siptrunk.a1.net'
WHERE EXISTS (
  SELECT 1 FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='spbx_trunks'
    AND COLUMN_NAME='client_uri'
)
AND COALESCE(client_uri,'')='';

UPDATE spbx_trunks
SET auth_username='+43312423826'
WHERE EXISTS (
  SELECT 1 FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='spbx_trunks'
    AND COLUMN_NAME='auth_username'
)
AND COALESCE(auth_username,'')='';

-- A1 Datensatz nur noch einfügen, wenn keiner existiert.
INSERT INTO spbx_trunks
(provider, trunk_name, name, main_number, username, auth_user, domain, server_uri,
 endpoint_id, contact_user, emergency_profile, clip_no_screening, ext_from, ext_to, active,
 client_domain, client_uri, auth_username)
SELECT
  'A1',
  'A1 Hauptnummer',
  'A1 Hauptnummer',
  '+43312423826',
  '+43312423826',
  '+43312423826',
  'siptrunk.a1.net',
  'sip:siptrunk.a1.net',
  'trunk-a1-43312423826',
  '+43312423826',
  'AT',
  1,
  10,
  99,
  1,
  'siptrunk.a1.net',
  'sip:+43312423826@siptrunk.a1.net',
  '+43312423826'
WHERE NOT EXISTS (
  SELECT 1 FROM spbx_trunks WHERE endpoint_id='trunk-a1-43312423826'
);

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('trunk_legacy_required_columns_fix', '1.3.5', 'Altspalten wie client_domain bekommen Defaults und werden bei Migration befüllt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
