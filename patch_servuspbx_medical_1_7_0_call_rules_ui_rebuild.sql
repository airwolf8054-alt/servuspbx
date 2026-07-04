-- ServusPBX Medical 1.7.0 - Call Rules UI Rebuild

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rules_ui_rebuild', '1.7.0', 'Offen-Ziel Bereich der Anrufregeln sauber neu aufgebaut')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
