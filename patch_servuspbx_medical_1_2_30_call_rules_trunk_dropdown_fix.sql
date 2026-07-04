-- ============================================================
-- ServusPBX Medical 1.2.30
-- Anrufregeln Trunk-Dropdown + Feiertagsfeld CSS
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rules_trunk_dropdown', '1.2.30', 'SIP-Trunks werden robust in Anrufregeln angezeigt und bestehende Verknüpfungen markiert'),
('holidays_css_fix', '1.2.30', 'Feiertagsformular Bezeichnung Feld CSS korrigiert')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.30
