-- ============================================================
-- ServusPBX Medical 1.5.5
-- Telefonbuch Notify Fix
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('phonebook_notify_fix', '1.5.5', 'Telefonbuch Änderungen senden snom-check-cfg an alle aktiven provisionierten Telefone')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
