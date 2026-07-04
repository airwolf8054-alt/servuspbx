-- ServusPBX Professional 2.2.0 - Firmware + Gigaset DECT
INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('fw_snom_desktop_version','10.1.226.13','SNOM Desktop Firmware Version'),
('fw_gigaset_desktop_version','10.1.226.13','Gigaset Desktop Firmware Version'),
('fw_gigaset_p82x_model','gigasetP820','Gigaset P82x Firmware Modellname'),
('fw_gigaset_p85x_model','gigasetP850','Gigaset P85x Firmware Modellname'),
('snom_m400_fw','0790','SNOM M400 Firmware Version'),
('snom_m400_branch','0200','SNOM M400 Firmware Branch'),
('fw_gigaset_dect_n610_version','','Gigaset N610 Firmware Version'),
('fw_gigaset_dect_n610_url','','Gigaset N610 Firmware URL'),
('firmware_gigaset_dect','2.2.0','Firmwareverwaltung und Gigaset N610 Basis-Typ')
ON DUPLICATE KEY UPDATE `description`=VALUES(`description`);
