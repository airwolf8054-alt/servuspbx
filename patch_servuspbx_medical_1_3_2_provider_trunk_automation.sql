-- ============================================================
-- ServusPBX Medical 1.3.2
-- Provider-geführte Trunk-Automation
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('trunk_supported_providers', 'A1,MAGENTA,EASYBELL', 'Vorerst unterstützte Provider in der Trunkmaske'),
('provider_trunk_automation', '1.3.2', 'Provider erzeugen automatisch incoming/internal/outgoing und technische SIP-Felder'),
('a1_minimal_trunk_fields', '1', 'A1 blendet Auth-User und Contact-User aus und setzt sie automatisch')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.3.2
