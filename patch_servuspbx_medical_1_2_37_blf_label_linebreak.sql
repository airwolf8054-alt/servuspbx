-- ============================================================
-- ServusPBX Medical 1.2.37
-- BLF Label Zeilenumbruch im Snom Provisioning
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('snom_blf_label_linebreak', '1.2.37', 'BLF-Labels werden im Provisioning bei Vorname Nachname zweizeilig ausgegeben')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.37
