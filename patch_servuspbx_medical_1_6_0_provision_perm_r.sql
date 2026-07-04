-- ============================================================
-- ServusPBX Medical 1.6.0
-- Provisioning perm=R Normalisierung
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('provision_perm_r', '1.6.0', 'Provisioning setzt allgemeine Einstellungen mit perm=R; Klingeltöne bleiben unverändert'),
('led_policy_perm_r', '1.6.0', 'led_blink_fast und led_orange werden mit perm=R provisioniert')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
