-- ============================================================
-- ServusPBX Medical 1.2.24
-- D810 Context-Key Belegung
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('snom_d810_context_key_0', 'F_ADR_BOOK', 'D810 verwendet Context-Key 0 für Telefonbuch'),
('snom_d815_context_key_0', 'F_DND', 'D815 behält Context-Key 0 für DND')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.24
