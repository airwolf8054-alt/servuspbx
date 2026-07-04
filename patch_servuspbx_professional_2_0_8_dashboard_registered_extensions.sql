-- ServusPBX Professional 2.0.8 - Dashboard Registered Extensions

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('dashboard_registered_extensions', '2.0.8', 'Dashboard zählt registrierte Nebenstellen ohne Trunks')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
