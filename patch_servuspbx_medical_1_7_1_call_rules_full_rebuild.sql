-- ServusPBX Medical 1.7.1 - Call Rules Full Rebuild

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rules_full_rebuild', '1.7.1', 'Anrufregeln Seite und Generator komplett gegen echtes Schema neu aufgebaut')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
