-- ============================================================
-- ServusPBX Medical 1.2.17
-- Snom Telefonbuch Provisioning
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('snom_phonebook_url', 'http://10.43.4.244/provision/phonebook.xml.php', 'Snom Telefonbuch URL für Provisioning'),
('snom_phonebook_provisioning', '1.2.17', 'D810/D815 erhalten Telefonbuch per Provisioning')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.17
