-- ============================================================
-- ServusPBX Medical 1.2.22
-- Snom Context-Key Beschriftung
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('snom_context_key_text', 'on', 'Beschriftung der Context-Keys unter dem Display aktivieren')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.22
