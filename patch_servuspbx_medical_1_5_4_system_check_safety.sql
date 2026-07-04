-- ============================================================
-- ServusPBX Medical 1.5.4
-- Systemprüfung / Deppensicherheit
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('system_check_page', '1.5.4', 'Systemprüfung für Realtime, ast_config, A1 Trunks und BLF-Schutz'),
('menu_opening_hours_removed', '1.5.4', 'Öffnungszeiten wurde aus dem Hauptmenü entfernt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
