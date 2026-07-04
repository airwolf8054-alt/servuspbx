-- ============================================================
-- ServusPBX Medical 1.2.0
-- SIP-Trunks Stable
-- ============================================================
--
-- Ziel:
-- - SIP-Trunks auf Basis der bestehenden ServusPBX-Seite trunk_a1.php.
-- - Menütext ohne A1.
-- - Provider-Vorlagen: A1, Easybell, Magenta.
-- - Speicherung in spbx_trunks + Asterisk Realtime Tabellen.
-- - from-trunk Context vorbereiten.
--
-- Backup:
-- mysqldump -u root -p general > /root/general_before_1_2_0.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `spbx_trunks` (
  `id` varchar(80) NOT NULL,
  `provider` varchar(40) NOT NULL DEFAULT 'a1',
  `name` varchar(160) NOT NULL,
  `username` varchar(160) NOT NULL,
  `auth_user` varchar(160) DEFAULT NULL,
  `server_uri` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `outbound_proxy` varchar(255) DEFAULT NULL,
  `context` varchar(80) NOT NULL DEFAULT 'from-trunk',
  `transport` varchar(40) NOT NULL DEFAULT 'transport-udp',
  `codecs` varchar(160) NOT NULL DEFAULT 'alaw,ulaw',
  `contact_user` varchar(160) DEFAULT NULL,
  `expiration` int(11) NOT NULL DEFAULT 3600,
  `status` varchar(40) DEFAULT 'unbekannt',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `provider` (`provider`),
  KEY `context` (`context`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `spbx_trunks`
  ADD COLUMN IF NOT EXISTS `provider` varchar(40) NOT NULL DEFAULT 'a1',
  ADD COLUMN IF NOT EXISTS `name` varchar(160) NOT NULL,
  ADD COLUMN IF NOT EXISTS `username` varchar(160) NOT NULL,
  ADD COLUMN IF NOT EXISTS `auth_user` varchar(160) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `server_uri` varchar(255) NOT NULL,
  ADD COLUMN IF NOT EXISTS `domain` varchar(255) NOT NULL,
  ADD COLUMN IF NOT EXISTS `outbound_proxy` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `context` varchar(80) NOT NULL DEFAULT 'from-trunk',
  ADD COLUMN IF NOT EXISTS `transport` varchar(40) NOT NULL DEFAULT 'transport-udp',
  ADD COLUMN IF NOT EXISTS `codecs` varchar(160) NOT NULL DEFAULT 'alaw,ulaw',
  ADD COLUMN IF NOT EXISTS `contact_user` varchar(160) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `expiration` int(11) NOT NULL DEFAULT 3600,
  ADD COLUMN IF NOT EXISTS `status` varchar(40) DEFAULT 'unbekannt',
  ADD COLUMN IF NOT EXISTS `active` tinyint(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS `created_at` timestamp NULL DEFAULT current_timestamp(),
  ADD COLUMN IF NOT EXISTS `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp();

CREATE TABLE IF NOT EXISTS `spbx_trunk_providers` (
  `provider_key` varchar(40) NOT NULL,
  `display_name` varchar(80) NOT NULL,
  `server_uri` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `outbound_proxy` varchar(255) DEFAULT NULL,
  `transport` varchar(40) NOT NULL DEFAULT 'transport-udp',
  `codecs` varchar(160) NOT NULL DEFAULT 'alaw,ulaw',
  `context` varchar(80) NOT NULL DEFAULT 'from-trunk',
  `expiration` int(11) NOT NULL DEFAULT 3600,
  PRIMARY KEY (`provider_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `spbx_trunk_providers`
(`provider_key`,`display_name`,`server_uri`,`domain`,`outbound_proxy`,`transport`,`codecs`,`context`,`expiration`) VALUES
('a1','A1','sip:registrar.a1.net','registrar.a1.net',NULL,'transport-udp','alaw,ulaw','from-trunk',3600),
('easybell','Easybell','sip:sip.easybell.de','sip.easybell.de',NULL,'transport-udp','alaw,ulaw,g722','from-trunk',600),
('magenta','Magenta','sip:tel.t-online.de','tel.t-online.de',NULL,'transport-udp','alaw,ulaw','from-trunk',3600)
ON DUPLICATE KEY UPDATE
  `display_name`=VALUES(`display_name`),
  `server_uri`=VALUES(`server_uri`),
  `domain`=VALUES(`domain`),
  `outbound_proxy`=VALUES(`outbound_proxy`),
  `transport`=VALUES(`transport`),
  `codecs`=VALUES(`codecs`),
  `context`=VALUES(`context`),
  `expiration`=VALUES(`expiration`);

CREATE TABLE IF NOT EXISTS `extensions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `context` varchar(40) NOT NULL,
  `exten` varchar(40) NOT NULL,
  `priority` int(11) NOT NULL,
  `app` varchar(40) NOT NULL,
  `appdata` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_context_exten_priority` (`context`,`exten`,`priority`),
  KEY `idx_context_exten` (`context`,`exten`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `extensions`
WHERE `context`='from-trunk'
  AND `exten`='s'
  AND `priority` IN (1,2);

INSERT INTO `extensions` (`context`,`exten`,`priority`,`app`,`appdata`) VALUES
('from-trunk','s',1,'NoOp','ServusPBX inbound trunk placeholder'),
('from-trunk','s',2,'Hangup','');

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sip_trunks_enabled', '1', 'SIP-Trunk-Verwaltung aktiviert'),
('sip_trunk_default_context', 'from-trunk', 'Eingehender Trunk Context'),
('sip_trunk_providers', 'a1,easybell,magenta', 'Verfügbare SIP-Trunk Provider-Vorlagen'),
('sip_trunk_page', 'pages/trunk_a1.php', 'SIP-Trunk-Seite basiert auf bestehender ServusPBX-Seite')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Danach:
-- sudo asterisk -rx "module reload res_pjsip.so"
-- sudo asterisk -rx "module reload res_pjsip_outbound_registration.so"
-- sudo asterisk -rx "dialplan reload"
-- ============================================================
-- Ende ServusPBX Medical 1.2.0
-- ============================================================
