-- ============================================================
-- ServusPBX Medical 1.2.3
-- SIP-Trunks Framework Fix
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sip_trunks_framework_fix', '1.2.3', 'trunk_a1.php verwendet explizit den bestehenden ServusPBX App-Rahmen mit Sidebar/Header/CSS')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.3
