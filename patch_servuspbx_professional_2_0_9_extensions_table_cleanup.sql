-- ServusPBX Professional 2.0.9 - Extensions Table Cleanup

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('extensions_table_cleanup', '2.0.9', 'Nebenstellenliste: Caller-ID entfernt, Name einzeilig, MAC/IPEI korrigiert')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
