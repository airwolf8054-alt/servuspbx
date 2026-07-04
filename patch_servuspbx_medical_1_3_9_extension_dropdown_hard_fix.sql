-- ============================================================
-- ServusPBX Medical 1.3.9
-- Nebenstellen-Dropdown Hard Fix
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('extension_dropdown_hard_fix', '1.3.9', 'Dropdown nutzt harte Range-Auflösung aus internal_<rufnummer>, Outbound-Route und Fallback 10-99')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.3.9
