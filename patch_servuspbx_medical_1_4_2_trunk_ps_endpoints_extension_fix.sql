-- ============================================================
-- ServusPBX Medical 1.4.2
-- Trunk ps_endpoints.extension Fix
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('trunk_save_ps_endpoints_extension_fix', '1.4.2', 'ps_endpoints.extension wird bei Legacy-Schema befüllt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.4.2
