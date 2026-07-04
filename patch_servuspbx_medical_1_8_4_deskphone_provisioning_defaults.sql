-- ============================================================
-- ServusPBX Medical 1.8.4
-- SNOM/Gigaset Tischtelefon Provisioning Defaults
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('deskphone_provisioning_defaults', '1.8.4', 'SNOM und Gigaset Tischtelefone erhalten zentrale Callscreen/LED/User-Active Defaults')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
