-- ============================================================
-- ServusPBX Medical 1.3.3
-- Trunk-Verwaltung 2.0 + Durchwahlbereiche
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
  UNIQUE KEY `uniq_main_number` (`main_number`),
  KEY `idx_endpoint_id` (`endpoint_id`),
  KEY `idx_provider` (`provider`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET @tbl := 'spbx_trunks';

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=@tbl AND COLUMN_NAME='ext_from');
SET @s := IF(@c=0, 'ALTER TABLE spbx_trunks ADD COLUMN ext_from INT DEFAULT NULL AFTER clip_no_screening', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=@tbl AND COLUMN_NAME='ext_to');
SET @s := IF(@c=0, 'ALTER TABLE spbx_trunks ADD COLUMN ext_to INT DEFAULT NULL AFTER ext_from', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=@tbl AND COLUMN_NAME='emergency_profile');
SET @s := IF(@c=0, "ALTER TABLE spbx_trunks ADD COLUMN emergency_profile ENUM('AT','DE','CH','LI','CUSTOM') NOT NULL DEFAULT 'AT' AFTER contact_user", 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=@tbl AND COLUMN_NAME='clip_no_screening');
SET @s := IF(@c=0, 'ALTER TABLE spbx_trunks ADD COLUMN clip_no_screening TINYINT(1) NOT NULL DEFAULT 1 AFTER emergency_profile', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('trunk_management_v2', '1.3.3', 'Provider-geführte Trunk-Verwaltung mit automatischen Contexts'),
('extension_ranges', '1.3.3', 'Durchwahlbereiche pro Trunk und Nebenstellen-Dropdown'),
('unique_extensions_policy', 'global', 'Durchwahlen sind systemweit eindeutig')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Nach Installation:
-- SIP-Trunks öffnen, bestehenden Trunk bearbeiten/speichern.
-- Dadurch werden internal_<rufnummer> und outgoing_<rufnummer> erzeugt.
