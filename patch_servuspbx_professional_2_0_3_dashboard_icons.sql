-- ============================================================
-- ServusPBX Professional 2.0.3
-- Dashboard SVG Icons
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('dashboard_svg_icons', '2.0.3', 'Dashboard verwendet moderne SVG Icons statt Emoji Icons')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
