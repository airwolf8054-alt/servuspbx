-- ServusPBX Professional 2.4.4 - Queue Dropdown Hardfix

INSERT INTO spbx_settings (setting_key, setting_value, description) VALUES
('queue_dropdown_hardfix', '2.4.4', 'Queue Dropdown in Anrufregeln mit Helper und direktem SQL-Fallback')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), description=VALUES(description);
