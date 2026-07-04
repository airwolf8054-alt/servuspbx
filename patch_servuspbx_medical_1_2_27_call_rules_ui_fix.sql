-- ============================================================
-- ServusPBX Medical 1.2.27
-- Anrufregeln UI Fix
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rules_ui_version', '1.2.27', 'Anrufregeln CSS/Feldlayout und Öffnungszeiten-Tabelle')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.27
