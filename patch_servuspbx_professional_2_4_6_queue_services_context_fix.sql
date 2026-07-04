-- ServusPBX Professional 2.4.6 - Queue Services Context Fix

INSERT INTO spbx_settings (setting_key, setting_value, description) VALUES
('queue_services_context_fix', '2.4.6', 'Anrufregeln erzeugen queue-services Context und Realtime-Extensions für Queues')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), description=VALUES(description);
