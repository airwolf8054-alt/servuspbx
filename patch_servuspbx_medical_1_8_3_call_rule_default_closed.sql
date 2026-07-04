-- ============================================================
-- ServusPBX Medical 1.8.3
-- Call Rule Default Closed
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rule_default_closed', '1.8.3', 'Neue/leere Anrufregel-Zeitfenster werden mit 00:00-00:00 vorbelegt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
