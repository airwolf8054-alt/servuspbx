-- ============================================================
-- ServusPBX Medical 1.4.1
-- Trunk Legacy Extension Columns
-- ============================================================
--
-- Fix:
-- - Falls ps_registrations.extension existiert und NOT NULL ist,
--   wird es beim Trunk-Speichern befüllt.
-- - ps_aors.extension bleibt aus 1.4.0 ebenfalls berücksichtigt.
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('trunk_save_ps_registrations_extension_fix', '1.4.1', 'ps_registrations.extension wird bei Legacy-Schema befüllt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.4.1
