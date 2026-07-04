-- ============================================================
-- ServusPBX Professional 2.0.2
-- UI Refinement
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('professional_ui_refine', '2.0.2', 'Registered Logo und Proxmox-inspirierte UI-Verfeinerung')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
