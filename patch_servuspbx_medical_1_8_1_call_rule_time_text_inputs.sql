-- ServusPBX Medical 1.8.1 - Call Rule Time Text Inputs

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rule_time_text_inputs', '1.8.1', 'Öffnungszeiten nutzen Textfelder HH:MM statt Browser time inputs')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
