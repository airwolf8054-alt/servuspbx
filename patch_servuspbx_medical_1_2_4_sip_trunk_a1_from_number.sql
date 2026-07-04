-- ============================================================
-- ServusPBX Medical 1.2.4
-- SIP-Trunk A1 + Endpoint-ID from_<rufnummer>
-- ============================================================

-- A1 Providerdaten korrigieren.
UPDATE `spbx_trunk_providers`
SET
  `server_uri`='sip:siptrunk.a1.net',
  `client_domain`='siptrunk.a1.net'
WHERE `provider_key`='a1';

INSERT INTO `spbx_trunk_providers`
(`provider_key`,`display_name`,`server_uri`,`client_domain`,`outbound_proxy`,`transport`,`codecs`,`context`,`expiration`) VALUES
('a1','A1','sip:siptrunk.a1.net','siptrunk.a1.net',NULL,'transport-udp','alaw,ulaw','from-trunk',3600)
ON DUPLICATE KEY UPDATE
  `display_name`=VALUES(`display_name`),
  `server_uri`=VALUES(`server_uri`),
  `client_domain`=VALUES(`client_domain`),
  `outbound_proxy`=VALUES(`outbound_proxy`),
  `transport`=VALUES(`transport`),
  `codecs`=VALUES(`codecs`),
  `context`=VALUES(`context`),
  `expiration`=VALUES(`expiration`);

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sip_trunk_endpoint_id_format', 'from_number', 'Neue SIP-Trunks erhalten endpoint_id from_<rufnummer>'),
('sip_trunk_a1_server', 'siptrunk.a1.net', 'A1 SIP-Trunk Server/Domain')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Hinweis:
-- Bestehende Trunks werden nicht automatisch umbenannt, da endpoint_id auch in
-- ps_endpoints/ps_auths/ps_aors/ps_registrations referenziert wird.
-- Falls gewünscht, bestehenden Trunk löschen und neu anlegen.

-- Ende ServusPBX Medical 1.2.4
