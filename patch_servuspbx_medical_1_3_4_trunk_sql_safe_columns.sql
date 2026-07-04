-- ============================================================
-- ServusPBX Medical 1.3.4
-- Safe SQL Migration für Trunk-Verwaltung 2.0
-- ============================================================
--
-- Korrigiert:
-- - keine ADD COLUMN ... AFTER Abhängigkeit mehr
-- - funktioniert auch, wenn clip_no_screening noch nicht existiert
-- ============================================================

CREATE TABLE IF NOT EXISTS `spbx_trunks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider` varchar(40) NOT NULL DEFAULT 'A1',
  `trunk_name` varchar(120) NOT NULL DEFAULT '',
  `name` varchar(120) DEFAULT NULL,
  `main_number` varchar(40) NOT NULL DEFAULT '',
  `username` varchar(120) NOT NULL DEFAULT '',
  `auth_user` varchar(120) DEFAULT NULL,
  `domain` varchar(160) DEFAULT NULL,
  `server_uri` varchar(180) DEFAULT NULL,
  `outbound_proxy` varchar(180) DEFAULT NULL,
  `endpoint_id` varchar(120) DEFAULT NULL,
  `contact_user` varchar(120) DEFAULT NULL,
  `emergency_profile` enum('AT','DE','CH','LI','CUSTOM') NOT NULL DEFAULT 'AT',
  `clip_no_screening` tinyint(1) NOT NULL DEFAULT 1,
  `ext_from` int(11) DEFAULT NULL,
  `ext_to` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_endpoint_id` (`endpoint_id`),
  KEY `idx_provider` (`provider`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- provider
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='provider');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN provider VARCHAR(40) NOT NULL DEFAULT 'A1'", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- trunk_name
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='trunk_name');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN trunk_name VARCHAR(120) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- name
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='name');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN name VARCHAR(120) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- main_number
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='main_number');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN main_number VARCHAR(40) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- username
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='username');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN username VARCHAR(120) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- auth_user
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='auth_user');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN auth_user VARCHAR(120) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- domain
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='domain');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN domain VARCHAR(160) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- server_uri
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='server_uri');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN server_uri VARCHAR(180) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- outbound_proxy
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='outbound_proxy');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN outbound_proxy VARCHAR(180) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- endpoint_id
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='endpoint_id');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN endpoint_id VARCHAR(120) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- contact_user
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='contact_user');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN contact_user VARCHAR(120) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- emergency_profile
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='emergency_profile');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN emergency_profile ENUM('AT','DE','CH','LI','CUSTOM') NOT NULL DEFAULT 'AT'", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- clip_no_screening
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='clip_no_screening');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN clip_no_screening TINYINT(1) NOT NULL DEFAULT 1", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ext_from
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='ext_from');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN ext_from INT DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ext_to
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='ext_to');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN ext_to INT DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- active
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_trunks' AND COLUMN_NAME='active');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Bestehenden A1 Trunk aus PJSIP übernehmen, falls spbx_trunks leer ist.
INSERT INTO spbx_trunks
(provider, trunk_name, name, main_number, username, auth_user, domain, server_uri,
 endpoint_id, contact_user, emergency_profile, clip_no_screening, ext_from, ext_to, active)
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
  1
WHERE NOT EXISTS (
  SELECT 1 FROM spbx_trunks WHERE endpoint_id='trunk-a1-43312423826'
);

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('trunk_sql_safe_columns', '1.3.4', 'Spaltenmigration ohne AFTER-Abhängigkeiten'),
('trunk_management_v2', '1.3.4', 'Provider-geführte Trunk-Verwaltung mit Durchwahlbereichen')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Danach:
-- Dateien kopieren, SIP-Trunk öffnen und speichern.
