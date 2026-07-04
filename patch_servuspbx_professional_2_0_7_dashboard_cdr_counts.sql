-- ServusPBX Professional 2.0.7 - Dashboard CDR Counts

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('dashboard_cdr_counts', '2.0.7', 'Dashboard zählt eingehende, ausgehende und verpasste Anrufe über CDR-Kontext/Trunk statt über src-Länge')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
