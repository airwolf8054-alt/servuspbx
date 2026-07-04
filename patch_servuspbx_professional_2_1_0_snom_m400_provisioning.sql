-- ServusPBX Professional 2.1.0 - SNOM M400 Provisioning

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('snom_m400_provisioning', '2.1.0', 'SNOM M400 Provisioning mit 20 Handset Slots'),
('snom_m400_admin_pass', 'admin', 'SNOM M400 Admin/Web/HTTP Client Passwort'),
('snom_m400_fw', '', 'SNOM M400 Firmware Version optional'),
('snom_m400_branch', '', 'SNOM M400 Firmware Branch optional')
ON DUPLICATE KEY UPDATE
  `description`=VALUES(`description`);
