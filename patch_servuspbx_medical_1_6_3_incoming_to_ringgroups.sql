-- ServusPBX Medical 1.6.3 - Eingehende Anrufregeln zu Rufgruppen

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('incoming_to_ringgroups', '1.6.3', 'Anrufregeln können Rufgruppen als Offen-Ziel verwenden')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
