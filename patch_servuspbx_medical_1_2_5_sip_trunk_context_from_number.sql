-- ============================================================
-- ServusPBX Medical 1.2.5
-- SIP-Trunk Context from_<rufnummer>
-- ============================================================

-- A1 Providerdaten bleiben korrigiert.
UPDATE `spbx_trunk_providers`
SET
  `server_uri`='sip:siptrunk.a1.net',
  `client_domain`='siptrunk.a1.net'
WHERE `provider_key`='a1';

-- Wichtig:
-- Endpoint-ID bleibt stabil/eindeutig wie bisher:
--   trunk-<provider>-<name>
--
-- Der eingehende Context wird bei neuen Trunks aus der Rufnummer gebildet:
--   from_<rufnummer>
--
-- Bestehende Trunks werden bewusst nicht automatisch umgestellt,
-- damit kein bestehender Dialplan unerwartet verändert wird.

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sip_trunk_endpoint_id_format', 'trunk_provider_name', 'SIP-Trunk Endpoint-ID bleibt trunk-<provider>-<name>'),
('sip_trunk_context_format', 'from_number', 'Eingehender SIP-Trunk Context wird from_<rufnummer>'),
('sip_trunk_a1_server', 'siptrunk.a1.net', 'A1 SIP-Trunk Server/Domain')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.5
