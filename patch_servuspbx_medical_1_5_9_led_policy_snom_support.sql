-- ============================================================
-- ServusPBX Medical 1.5.9
-- LED Policy laut SNOM Support
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('led_policy_snom_support', '1.5.9', 'Provisioning setzt led_blink_fast und led_orange nach SNOM Support Vorgabe')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
