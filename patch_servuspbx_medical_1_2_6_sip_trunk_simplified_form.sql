-- ============================================================
-- ServusPBX Medical 1.2.6
-- SIP-Trunk Formular vereinfacht + Provider-Transports
-- ============================================================

-- Provider-Vorlagen aktualisieren.
UPDATE `spbx_trunk_providers`
SET
  `server_uri`='sip:siptrunk.a1.net',
  `client_domain`='siptrunk.a1.net',
  `transport`='transport-a1',
  `codecs`='alaw,ulaw',
  `context`='from-trunk',
  `expiration`=3600
WHERE `provider_key`='a1';

UPDATE `spbx_trunk_providers`
SET `transport`='transport-easybell'
WHERE `provider_key`='easybell';

UPDATE `spbx_trunk_providers`
SET `transport`='transport-magenta'
WHERE `provider_key`='magenta';

-- Provider-spezifische Transports vorbereiten.
INSERT INTO `ps_transports` (`id`,`protocol`,`bind`,`allow_reload`)
VALUES
('transport-a1','udp','0.0.0.0:5060','yes'),
('transport-easybell','udp','0.0.0.0:5060','yes'),
('transport-magenta','udp','0.0.0.0:5060','yes')
ON DUPLICATE KEY UPDATE
  `protocol`=VALUES(`protocol`),
  `bind`=VALUES(`bind`),
  `allow_reload`=VALUES(`allow_reload`);

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sip_trunk_form_mode', 'simple', 'SIP-Trunk Formular zeigt nur Pflicht-/Praxisfelder'),
('sip_trunk_context_auto', 'from_number', 'Context wird automatisch from_<rufnummer> gesetzt'),
('sip_trunk_transport_mode', 'transport_provider', 'Transport wird automatisch transport-<provider> gesetzt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.6
