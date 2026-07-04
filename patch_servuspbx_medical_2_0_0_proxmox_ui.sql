-- ============================================================
-- ServusPBX Medical 2.0.0
-- Proxmox UI Navigation
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('proxmox_ui_navigation', '2.0.0', 'Proxmox-inspirierte Navigation mit weißen SVG Icons und servusPBX Logo')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
