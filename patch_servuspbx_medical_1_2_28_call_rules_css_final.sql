-- ============================================================
-- ServusPBX Medical 1.2.28
-- Anrufregeln CSS Finalisierung
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rules_css_version', '1.2.28', 'Anrufregeln Formular- und Öffnungszeiten-CSS finalisiert')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.28
