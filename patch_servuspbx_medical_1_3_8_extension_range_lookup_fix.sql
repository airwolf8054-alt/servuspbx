-- ============================================================
-- ServusPBX Medical 1.3.8
-- Nebenstellen Range Lookup Fix
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('extension_range_lookup_fix', '1.3.8', 'Nebenstellen-Dropdown findet Trunk-Range robust über internal_<rufnummer>, Outbound-Route oder Fallback')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.3.8
