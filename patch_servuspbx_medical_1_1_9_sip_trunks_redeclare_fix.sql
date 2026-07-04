-- ============================================================
-- ServusPBX Medical 1.1.9
-- SIP-Trunks Redeclare Fix
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sip_trunks_redeclare_fix', '1.1.9', 'sip_trunks.php deklariert spbx_h nicht mehr doppelt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.1.9
