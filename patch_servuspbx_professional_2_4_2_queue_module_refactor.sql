-- ServusPBX Professional 2.4.2 - Queue Module Refactor

ALTER TABLE spbx_queues
  ADD COLUMN IF NOT EXISTS queue_announcement_enabled TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS queue_announcement_file VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS queue_tts_text TEXT NULL;

INSERT INTO spbx_settings (setting_key, setting_value, description) VALUES
('queue_module_refactor', '2.4.2', 'Queue Modul zentralisiert und Queue-Auswahl in Anrufregeln stabilisiert')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), description=VALUES(description);
