-- ============================================================
-- ServusPBX Medical 1.9.1
-- Compact List Rows
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('compact_list_rows', '1.9.1', 'Alle Listen/Tabellen verwenden kompaktere Zeilenhöhe wie die Nebenstellenliste')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
