-- ============================================================
-- ServusPBX Medical 1.2.7
-- SIP-Trunk Provider UI + Magenta
-- ============================================================

UPDATE `spbx_trunk_providers`
SET
  `server_uri`='sip:sip1.magenta.at',
  `client_domain`='sip1.magenta.at',
  `transport`='transport-magenta'
WHERE `provider_key`='magenta';

INSERT INTO `spbx_trunk_providers`
(`provider_key`,`display_name`,`server_uri`,`client_domain`,`outbound_proxy`,`transport`,`codecs`,`context`,`expiration`) VALUES
('magenta','Magenta','sip:sip1.magenta.at','sip1.magenta.at',NULL,'transport-magenta','alaw,ulaw','from-trunk',3600)
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
('sip_trunk_magenta_domain', 'sip1.magenta.at', 'Magenta SIP-Trunk Domain'),
('sip_trunk_provider_ui', '1.2.7', 'Providerwechsel aktualisiert Formularfelder ohne Seitenreload')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.7
