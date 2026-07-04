-- ServusPBX Professional 2.0.4 - Active Calls Dashboard

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('ami_host', '127.0.0.1', 'Asterisk AMI Host'),
('ami_port', '5038', 'Asterisk AMI Port'),
('ami_user', 'ServusPBX', 'Asterisk AMI Benutzer'),
('ami_secret', 'Phonesystem2020!', 'Asterisk AMI Secret'),
('active_calls_dashboard', '2.0.4', 'Dashboard Widget für aktuell laufende Gespräche über AMI')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
