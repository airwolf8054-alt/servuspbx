-- ============================================================
-- ServusPBX Medical 1.2.2
-- SIP-Trunks Layout Fix
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sip_trunks_layout_fix', '1.2.2', 'trunk_a1.php verwendet wieder den bestehenden ServusPBX Seitenrahmen')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.2
