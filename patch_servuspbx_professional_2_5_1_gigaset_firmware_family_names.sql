-- ServusPBX Professional 2.5.1 - Gigaset Firmware Family Names

UPDATE spbx_settings
SET setting_value='P82x'
WHERE setting_key='fw_gigaset_p82x_model';

UPDATE spbx_settings
SET setting_value='P85x'
WHERE setting_key='fw_gigaset_p85x_model';

INSERT INTO spbx_settings (setting_key, setting_value, description) VALUES
('gigaset_firmware_family_names', '2.5.1', 'Gigaset Firmware nutzt P82x/P85x Familiennamen statt gigasetP820/gigasetP850')
ON DUPLICATE KEY UPDATE
  setting_value=VALUES(setting_value),
  description=VALUES(description);
