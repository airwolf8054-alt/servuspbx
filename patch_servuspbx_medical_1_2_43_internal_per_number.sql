-- ============================================================
-- ServusPBX Medical 1.2.43
-- Internal Context pro Rufnummer
-- ============================================================
--
-- Eingehend bleibt unverändert:
--   incoming
--
-- Ausgehend / intern:
--   internal_<rufnummer>  -> interne Nebenstellen und Ausgang
--   outgoing_<rufnummer>  -> Provider-Trunk
--
-- Beispiel:
--   internal_43312423826
--   outgoing_43312423826
--
-- Enthalten:
-- - 2-, 3-, 4-stellige interne Durchwahlen
-- - Österreichische Notrufe/Kurznummern: 112,122,133,144,141,1450
-- - Deutsche Notrufe/Kurznummern: 110,112,115,116117
-- - ausgehend 0... und +... an outgoing_<rufnummer>
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('internal_context_mode', 'per_number', 'Nebenstellen verwenden internal_<rufnummer> pro Outbound-Route'),
('internal_context_version', '1.2.43', 'Interne Contexts mit 2/3/4-stelligen Durchwahlen und AT/DE Notrufen')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Nach Installation:
-- /pages/outbound_routes.php öffnen und "Dialplan neu aufbauen" klicken.
-- Danach:
-- asterisk -rx "pjsip reload"
--
-- Prüfung:
-- SELECT id, context FROM ps_endpoints WHERE device_type <> 'trunk' OR device_type IS NULL;
-- SELECT * FROM extensions WHERE context LIKE 'internal_%' ORDER BY context, exten, CAST(priority AS UNSIGNED);
