-- ============================================================
-- ServusPBX Medical 1.8.8
-- Outbound 00 Plus Fix
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('outbound_00_plus_fix', '1.8.8', 'Ausgehend wird 00 zuerst zu + normalisiert und lokale 0 danach zu +43')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
