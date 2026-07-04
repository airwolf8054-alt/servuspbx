-- ServusPBX Medical 1.6.9 - Call Rules DID Fix

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rules_did_fix', '1.6.9', 'DID Pattern Auswahl und Ziel Durchwahl aus DID speichern sauber')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
