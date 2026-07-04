-- ============================================================
-- ServusPBX Medical 1.5.8
-- Gigaset nutzt Snom/D815 Provisioning-Template
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('gigaset_use_snom_template', '1.5.8', 'Gigaset P82x/P85x nutzen das gleiche Provisioning-Template wie snom D815; nur Tastenanzahl unterscheidet sich')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
