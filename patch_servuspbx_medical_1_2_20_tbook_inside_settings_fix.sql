-- ============================================================
-- ServusPBX Medical 1.2.20
-- tbook innerhalb <settings> Fix
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('snom_phonebook_position', 'inside_settings_before_closing_root', 'tbook wird innerhalb des settings Root-Elements vor </settings> ausgegeben'),
('snom_phonebook_source', 'spbx_phonebook', 'Quelle für Snom Telefonbuch')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.20
