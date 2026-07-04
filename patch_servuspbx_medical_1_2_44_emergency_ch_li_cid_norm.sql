-- ============================================================
-- ServusPBX Medical 1.2.44
-- Notrufe CH/LI + eingehende CallerID Normalisierung
-- ============================================================
--
-- Ergänzt:
-- - Schweiz/Liechtenstein Notrufe/Kurznummern:
--   112,117,118,144,145
-- - Schweiz zusätzlich:
--   1414,143,147
--
-- Eingehend:
-- - CallerID +43... wird im Callflow zu 0043... normalisiert.
--
-- Nach Installation:
-- /pages/outbound_routes.php -> Dialplan neu aufbauen
-- Anrufregel speichern, damit incoming neu generiert wird.
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('emergency_numbers_ch_li', '1.2.44', 'CH/LI Notrufe und Kurznummern in internal_<rufnummer> ergänzt'),
('incoming_callerid_normalization', 'plus_to_00', 'Eingehende CallerID +E164 wird auf 00 normalisiert')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.44
