-- ServusPBX Medical 1.6.5 - Call Rules idempotenter Dialplan Insert

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rules_idempotent_insert', '1.6.5', 'Anrufregeln Dialplan-Rebuild nutzt ON DUPLICATE KEY UPDATE gegen doppelte Prioritäten')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
