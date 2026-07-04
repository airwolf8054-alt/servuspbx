-- ============================================================
-- ServusPBX Medical 1.1.7
-- SIP-Trunks Verwaltung
-- ============================================================

-- ------------------------------------------------------------
-- 1. App-Tabelle für SIP-Trunks
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `spbx_trunks` (
  `id` varchar(80) NOT NULL,
  `provider` varchar(40) NOT NULL DEFAULT 'a1',
  `name` varchar(160) NOT NULL,
  `username` varchar(160) NOT NULL,
  `auth_user` varchar(160) DEFAULT NULL,
  `server_uri` varchar(255) NOT NULL,
  `client_domain` varchar(255) NOT NULL,
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
  ADD COLUMN IF NOT EXISTS `client_domain` varchar(255) NOT NULL,
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

-- ------------------------------------------------------------
-- 2. from-trunk Context vorbereiten
-- ------------------------------------------------------------

DELETE FROM `extensions`
WHERE `context`='from-trunk'
  AND `exten`='s'
  AND `priority` IN (1,2,3);

INSERT INTO `extensions` (`context`,`exten`,`priority`,`app`,`appdata`) VALUES
('from-trunk','s',1,'NoOp','ServusPBX inbound trunk placeholder'),
('from-trunk','s',2,'Hangup',''),
('from-trunk','s',3,'NoOp','unused')
ON DUPLICATE KEY UPDATE
  `app`=VALUES(`app`),
  `appdata`=VALUES(`appdata`);

-- ------------------------------------------------------------
-- 3. Provider-Vorlagen
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `spbx_trunk_providers` (
  `provider_key` varchar(40) NOT NULL,
  `display_name` varchar(80) NOT NULL,
  `server_uri` varchar(255) NOT NULL,
  `client_domain` varchar(255) NOT NULL,
  `outbound_proxy` varchar(255) DEFAULT NULL,
  `transport` varchar(40) NOT NULL DEFAULT 'transport-udp',
  `codecs` varchar(160) NOT NULL DEFAULT 'alaw,ulaw',
  `context` varchar(80) NOT NULL DEFAULT 'from-trunk',
  `expiration` int(11) NOT NULL DEFAULT 3600,
  PRIMARY KEY (`provider_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `spbx_trunk_providers`
(`provider_key`,`display_name`,`server_uri`,`client_domain`,`outbound_proxy`,`transport`,`codecs`,`context`,`expiration`) VALUES
('a1','A1','sip:registrar.a1.net','registrar.a1.net',NULL,'transport-udp','alaw,ulaw','from-trunk',3600),
('easybell','Easybell','sip:sip.easybell.de','sip.easybell.de',NULL,'transport-udp','alaw,ulaw,g722','from-trunk',600),
('magenta','Magenta','sip:tel.t-online.de','tel.t-online.de',NULL,'transport-udp','alaw,ulaw','from-trunk',3600)
ON DUPLICATE KEY UPDATE
  `display_name`=VALUES(`display_name`),
  `server_uri`=VALUES(`server_uri`),
  `client_domain`=VALUES(`client_domain`),
  `outbound_proxy`=VALUES(`outbound_proxy`),
  `transport`=VALUES(`transport`),
  `codecs`=VALUES(`codecs`),
  `context`=VALUES(`context`),
  `expiration`=VALUES(`expiration`);

-- ------------------------------------------------------------
-- 4. Settings
-- ------------------------------------------------------------

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sip_trunks_enabled', '1', 'SIP-Trunk-Verwaltung aktiviert'),
('sip_trunk_default_context', 'from-trunk', 'Eingehender Trunk Context'),
('sip_trunk_providers', 'a1,easybell,magenta', 'Verfügbare SIP-Trunk Provider-Vorlagen')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- ============================================================
-- Ende ServusPBX Medical 1.1.7
-- ============================================================
