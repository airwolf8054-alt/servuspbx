-- ServusPBX Professional 2.5.0 - Desktop Firmware Tag Fix

INSERT INTO spbx_settings (setting_key, setting_value, description) VALUES
('desktop_firmware_tag_fix', '2.5.0', 'Desktop Provisioning nutzt firmware statt firmware_status und modellabhängige Hersteller-URLs')
ON DUPLICATE KEY UPDATE
  setting_value=VALUES(setting_value),
  description=VALUES(description);
