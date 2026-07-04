-- ============================================================
-- ServusPBX Medical 1.2.1
-- SIP-Trunks Schema Fix
-- ============================================================
--
-- Korrigiert auf vorhandene Tabellenstruktur:
-- - spbx_trunk_providers.client_domain statt domain
-- - spbx_trunks.id ist AUTO_INCREMENT int
-- - spbx_trunks.endpoint_id ist die Asterisk/Realtime-ID
-- - provider enum wird um easybell und magenta ergänzt
-- ============================================================

ALTER TABLE `spbx_trunks`
  MODIFY COLUMN `provider` enum('a1','easybell','magenta','other') NOT NULL DEFAULT 'a1';

ALTER TABLE `spbx_trunks`
  ADD COLUMN IF NOT EXISTS `name` varchar(160) NOT NULL,
  ADD COLUMN IF NOT EXISTS `auth_user` varchar(160) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `client_domain` varchar(255) NOT NULL,
  ADD COLUMN IF NOT EXISTS `context` varchar(80) NOT NULL DEFAULT 'from-trunk',
  ADD COLUMN IF NOT EXISTS `transport` varchar(40) NOT NULL DEFAULT 'transport-udp',
  ADD COLUMN IF NOT EXISTS `codecs` varchar(160) NOT NULL DEFAULT 'alaw,ulaw',
  ADD COLUMN IF NOT EXISTS `contact_user` varchar(160) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `expiration` int(11) NOT NULL DEFAULT 3600,
  ADD COLUMN IF NOT EXISTS `status` varchar(40) DEFAULT 'unbekannt',
  ADD COLUMN IF NOT EXISTS `domain` varchar(255) NOT NULL;

UPDATE `spbx_trunks`
SET
  `name` = COALESCE(NULLIF(`name`,''), `trunk_name`),
  `client_domain` = COALESCE(NULLIF(`client_domain`,''), NULLIF(`domain`,''), `from_domain`, ''),
  `domain` = COALESCE(NULLIF(`domain`,''), NULLIF(`client_domain`,''), `from_domain`, ''),
  `context` = COALESCE(NULLIF(`context`,''), 'from-trunk'),
  `transport` = COALESCE(NULLIF(`transport`,''), 'transport-udp'),
  `codecs` = COALESCE(NULLIF(`codecs`,''), 'alaw,ulaw'),
  `expiration` = COALESCE(`expiration`, 3600),
  `status` = COALESCE(NULLIF(`status`,''), 'unbekannt');

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

ALTER TABLE `spbx_trunk_providers`
  ADD COLUMN IF NOT EXISTS `client_domain` varchar(255) NOT NULL,
  ADD COLUMN IF NOT EXISTS `outbound_proxy` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `transport` varchar(40) NOT NULL DEFAULT 'transport-udp',
  ADD COLUMN IF NOT EXISTS `codecs` varchar(160) NOT NULL DEFAULT 'alaw,ulaw',
  ADD COLUMN IF NOT EXISTS `context` varchar(80) NOT NULL DEFAULT 'from-trunk',
  ADD COLUMN IF NOT EXISTS `expiration` int(11) NOT NULL DEFAULT 3600;

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

DELETE FROM `extensions`
WHERE `context`='from-trunk'
  AND `exten`='s'
  AND `priority` IN (1,2);

INSERT INTO `extensions` (`context`,`exten`,`priority`,`app`,`appdata`) VALUES
('from-trunk','s',1,'NoOp','ServusPBX inbound trunk placeholder'),
('from-trunk','s',2,'Hangup','');

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sip_trunks_schema_fix', '1.2.1', 'SIP-Trunks an vorhandenes Schema angepasst: endpoint_id statt id, client_domain statt domain'),
('sip_trunk_providers', 'a1,easybell,magenta', 'Verfügbare SIP-Trunk Provider-Vorlagen')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Danach:
-- sudo asterisk -rx "module reload res_pjsip.so"
-- sudo asterisk -rx "module reload res_pjsip_outbound_registration.so"
-- sudo asterisk -rx "dialplan reload"
-- ============================================================
-- Ende ServusPBX Medical 1.2.1
-- ============================================================
