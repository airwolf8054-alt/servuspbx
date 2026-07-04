-- ============================================================
-- ServusPBX Medical 1.2.23
-- Snom Text-Softkeys aktivieren
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('snom_context_key_text', 'on', 'Context-Key Beschriftung aktiv'),
('snom_text_softkey', 'on', 'Text-Softkeys aktivieren, damit Context-Keys Beschriftungen anzeigen')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.23
