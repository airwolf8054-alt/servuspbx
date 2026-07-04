-- ============================================================
-- ServusPBX Medical 1.8.7
-- P82x Layout + Outbound Plus Fix
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('p82x_layout_plus_outbound', '1.8.7', 'P82x Tastenlayout final und ausgehende Zielnummern wieder mit + statt 00')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
