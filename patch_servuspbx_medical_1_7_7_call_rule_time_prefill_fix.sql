-- ============================================================
-- ServusPBX Medical 1.7.7
-- Call Rule Time Prefill Fix
-- ============================================================
--
-- Fix:
-- Öffnungszeiten werden beim Bearbeiten wieder korrekt vorbefüllt.
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rule_time_prefill_fix', '1.7.7', 'Öffnungszeiten werden in Anrufregeln wieder korrekt vorbefüllt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
