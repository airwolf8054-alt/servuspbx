-- ============================================================
-- ServusPBX Medical 1.8.2
-- Call Rule Time Defaults auf Basis der echten call_rules.php
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rule_time_defaults_real_file', '1.8.2', 'Öffnungszeiten zeigen Mo-Fr 06:00-23:59 als Default, wenn kein DB-Wert vorhanden ist')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
