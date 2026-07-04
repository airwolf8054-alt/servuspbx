-- ============================================================
-- ServusPBX Medical 1.9.0
-- Outbound Normalize Single Priority
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('outbound_normalize_single_priority', '1.9.0', 'Outbound-Normalisierung erfolgt in einer Set-Priority ohne Prioritätskollision')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
