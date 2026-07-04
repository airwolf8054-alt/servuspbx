-- ServusPBX Medical 1.7.8 - Call Rule Time Loader Hardened

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rule_time_loader_hardened', '1.7.8', 'Öffnungszeiten Loader unterstützt sort_order 0/1 und 1/2 sowie mehrere weekday Formate')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
