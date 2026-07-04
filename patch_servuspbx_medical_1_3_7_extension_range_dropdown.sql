-- ============================================================
-- ServusPBX Medical 1.3.7
-- Nebenstellen-Dropdown mit belegten Durchwahlen
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('extension_range_dropdown', '1.3.7', 'Nebenstellen-Dropdown zeigt freie und belegte Durchwahlen aus dem Trunk-Bereich')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.3.7
