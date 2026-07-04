-- ============================================================
-- ServusPBX Medical 1.2.19
-- Inline Snom tbook Provisioning
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('snom_phonebook_mode', 'inline_tbook', 'Telefonbuch wird direkt im Snom Provisioning nach phone-settings ausgegeben'),
('snom_phonebook_fallback', 'ps_endpoints', 'Wenn directory leer ist, werden aktive Nebenstellen ausgegeben')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.19
