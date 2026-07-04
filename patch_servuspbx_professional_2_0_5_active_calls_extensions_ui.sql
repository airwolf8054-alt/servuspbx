-- ServusPBX Professional 2.0.5 - Active Calls + Extensions UI

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('active_calls_extensions_ui', '2.0.5', 'Aktive Gespräche Anzeige verbessert und Nebenstellenliste modernisiert')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
