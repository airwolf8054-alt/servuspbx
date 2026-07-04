-- ============================================================
-- ServusPBX Medical 1.5.6
-- Gigaset P82x/P85x Provisioning
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('gigaset_p82x_provisioning', '1.5.6', 'Gigaset P82x Provisioning und 8 BLF Tasten'),
('gigaset_p85x_provisioning', '1.5.6', 'Gigaset P85x Provisioning und 10 BLF Tasten')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
