-- ============================================================
-- ServusPBX Medical 1.3.6
-- RESET spbx_trunks
-- ============================================================
--
-- Achtung:
-- Diese Migration löscht die alte Tabelle spbx_trunks vollständig
-- und erstellt sie sauber neu.
--
-- Sinnvoll, weil die Tabelle aus mehreren Entwicklungsständen stammt
-- und alte Pflichtfelder wie client_domain/auth_username Probleme machen.
-- ============================================================

DROP TABLE IF EXISTS `spbx_trunks`;

CREATE TABLE `spbx_trunks` (
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

INSERT INTO `spbx_trunks`
(`provider`, `trunk_name`, `name`, `main_number`, `username`, `auth_user`,
 `domain`, `server_uri`, `outbound_proxy`, `endpoint_id`, `contact_user`,
 `emergency_profile`, `clip_no_screening`, `ext_from`, `ext_to`, `active`)
VALUES
('A1', 'A1 Hauptnummer', 'A1 Hauptnummer', '+43312423826', '+43312423826', '+43312423826',
 'siptrunk.a1.net', 'sip:siptrunk.a1.net', NULL, 'trunk-a1-43312423826', '+43312423826',
 'AT', 1, 10, 99, 1);

-- Bestehende Outbound-Routen passend neu aufbauen.
DELETE FROM `spbx_outbound_routes`;

INSERT INTO `spbx_outbound_routes`
(`route_name`, `provider`, `main_number`, `trunk_endpoint`, `outgoing_context`,
 `clip_no_screening`, `callerid_mode`, `emergency_profile`, `active`)
VALUES
('A1 Hauptnummer', 'A1', '+43312423826', 'trunk-a1-43312423826', 'outgoing_43312423826',
 1, 'extension', 'AT', 1);

-- A1 Inbound Guard.
UPDATE `ps_endpoints`
SET `auth` = NULL,
    `context` = 'incoming'
WHERE `id` = 'trunk-a1-43312423826';

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('spbx_trunks_reset', '1.3.6', 'spbx_trunks wurde sauber neu erstellt'),
('trunk_management_v2', '1.3.6', 'Trunk-Verwaltung 2.0 mit sauberer Tabelle'),
('extension_ranges', '1.3.6', 'Durchwahlbereiche pro Trunk aktiv')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Danach:
-- 1. Dateien kopieren
-- 2. SIP-Trunks öffnen
-- 3. A1-Trunk öffnen und speichern
-- 4. Dadurch werden internal_43312423826 und outgoing_43312423826 neu erzeugt
-- 5. pjsip reload
