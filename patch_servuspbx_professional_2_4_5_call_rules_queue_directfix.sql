-- ServusPBX Professional 2.4.5 - Call Rules Queue Direct Fix

INSERT INTO spbx_settings (setting_key, setting_value, description) VALUES
('call_rules_queue_directfix', '2.4.5', 'Anrufregeln laden Queue-Auswahl direkt aus spbx_queues')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), description=VALUES(description);
