-- ============================================================
-- ServusPBX Medical 1.2.8
-- SIP-Trunk bind_param Fix
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sip_trunk_bindparam_fix', '1.2.8', 'bind_param Typdefinition in trunk_a1.php korrigiert')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.8
