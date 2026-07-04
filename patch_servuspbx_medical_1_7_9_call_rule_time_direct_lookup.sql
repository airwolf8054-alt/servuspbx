-- ServusPBX Medical 1.7.9 - Call Rule Time Direct Lookup

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rule_time_direct_lookup', '1.7.9', 'Öffnungszeiten werden direkt per rule_id/weekday/sort_order geladen')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
