-- ============================================================
-- ServusPBX Medical 1.2.31
-- Anrufregeln SIP-Trunk Dropdown Fallback
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rules_trunk_dropdown_fallback', '1.2.31', 'Dropdown nutzt spbx_trunks plus Fallback ps_endpoints device_type=trunk')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.31
