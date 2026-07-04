-- ServusPBX Professional 2.1.1 - M400 Sync + DECT Directory

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('m400_sync_dectdirectory', '2.1.1', 'M400 Sync Button und DECT XML Telefonbuch')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
