-- ServusPBX Medical 1.6.8 - Call Rules Ziel UI Fix

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rules_target_ui_fix', '1.6.8', 'Anrufregeln zeigen Ziel-Felder korrekt an')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
