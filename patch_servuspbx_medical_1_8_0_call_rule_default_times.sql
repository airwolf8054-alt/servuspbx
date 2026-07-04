-- ServusPBX Medical 1.8.0 - Call Rule Default Times

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rule_default_times', '1.8.0', 'Neue/leere Anrufregel-Zeitfenster werden mit Mo-Fr 06:00-23:59 vorbefüllt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
