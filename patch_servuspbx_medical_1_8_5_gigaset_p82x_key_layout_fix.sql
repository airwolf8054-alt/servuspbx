-- ============================================================
-- ServusPBX Medical 1.8.5
-- Gigaset P82x Key Layout Fix
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('gigaset_p82x_key_layout_fix', '1.8.5', 'Gigaset P82x Tastenbelegung zeigt 1-4 links und 5-8 rechts')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
