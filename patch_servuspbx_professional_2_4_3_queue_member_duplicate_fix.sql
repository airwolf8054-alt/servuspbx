-- ServusPBX Professional 2.4.3 - Queue Member Duplicate Fix

INSERT INTO spbx_settings (setting_key, setting_value, description) VALUES
('queue_member_duplicate_fix', '2.4.3', 'Queue Agenten speichern ohne Duplicate-Key Fehler; fixe Agenten gewinnen gegenüber optionalen Agenten')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), description=VALUES(description);
