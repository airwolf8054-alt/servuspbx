-- ============================================================
-- ServusPBX Medical 1.2.45
-- Notrufprofile pro Trunk + outgoing _X.
-- ============================================================
--
-- Neu:
-- - emergency_profile pro Outbound-Route: AT, DE, CH, LI, CUSTOM
-- - internal_<rufnummer> erzeugt nur die Notrufe dieses Profils
-- - internal_<rufnummer>: 2/3/4-stellige Durchwahlen bleiben intern
-- - internal_<rufnummer>: alles ab 5 Stellen geht zu outgoing_<rufnummer>
-- - outgoing_<rufnummer>: _X. nimmt alles an, damit lokale Nummern ohne Vorwahl funktionieren
-- ============================================================

ALTER TABLE `spbx_outbound_routes`
  ADD COLUMN IF NOT EXISTS `emergency_profile` ENUM('AT','DE','CH','LI','CUSTOM') NOT NULL DEFAULT 'AT' AFTER `callerid_mode`;

UPDATE `spbx_outbound_routes`
SET `emergency_profile` = CASE
  WHEN REGEXP_REPLACE(`main_number`, '[^0-9]', '') LIKE '49%' THEN 'DE'
  WHEN REGEXP_REPLACE(`main_number`, '[^0-9]', '') LIKE '41%' THEN 'CH'
  WHEN REGEXP_REPLACE(`main_number`, '[^0-9]', '') LIKE '423%' THEN 'LI'
  ELSE 'AT'
END
WHERE `emergency_profile` IS NULL OR `emergency_profile`='AT';

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('outbound_emergency_profiles', '1.2.45', 'Notrufprofile AT/DE/CH/LI pro Outbound-Route'),
('outgoing_pattern', '_X.', 'Outgoing Context nimmt alle Ziele an'),
('internal_outgoing_threshold', '5', 'Alles ab 5 Stellen geht aus internal_<rufnummer> in outgoing_<rufnummer>')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Nach Installation:
-- /pages/outbound_routes.php öffnen, Profil prüfen/speichern und "Dialplan neu aufbauen" klicken.
