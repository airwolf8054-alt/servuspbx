-- ============================================================
-- ServusPBX Medical 1.3.1
-- Nebenstellen-Context + A1 Contact User Auto
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('extension_context_select', '1.3.1', 'Nebenstellen können einem internal_<rufnummer>-Context/Standort zugeordnet werden'),
('a1_contact_user_auto', '1.3.1', 'A1 Contact User wird automatisch aus der Hauptnummer im +E164 Format gebildet')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.3.1
