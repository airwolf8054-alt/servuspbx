-- ServusPBX Professional 2.0.6 - Calls Direction + MAC Column

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('calls_direction_mac_column', '2.0.6', 'Aktive Gespräche Richtung korrigiert und MAC-Spalte in Nebenstellenliste ergänzt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
