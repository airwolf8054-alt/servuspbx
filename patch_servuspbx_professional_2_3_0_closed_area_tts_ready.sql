-- ServusPBX Professional 2.3.0 - Closed Area TTS Ready

ALTER TABLE spbx_call_rules
  ADD COLUMN IF NOT EXISTS closed_destination_type VARCHAR(40) NOT NULL DEFAULT 'hangup',
  ADD COLUMN IF NOT EXISTS closed_destination_context VARCHAR(80) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS closed_destination_exten VARCHAR(80) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS closed_tts_text TEXT NULL,
  ADD COLUMN IF NOT EXISTS holiday_audio VARCHAR(255) NOT NULL DEFAULT '/var/www/html/sounds/holiday.mp3',
  ADD COLUMN IF NOT EXISTS holiday_destination_type VARCHAR(40) NOT NULL DEFAULT 'hangup',
  ADD COLUMN IF NOT EXISTS holiday_destination_context VARCHAR(80) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS holiday_destination_exten VARCHAR(80) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS holiday_tts_text TEXT NULL;

UPDATE spbx_call_rules
SET closed_destination_type = CASE WHEN closed_action='voicemail' THEN 'voicemail' ELSE 'hangup' END
WHERE closed_destination_type IS NULL OR closed_destination_type='';

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('closed_area_tts_ready', '2.3.0', 'Geschlossen- und Feiertagsbereich mit Zielsteuerung und TTS-Vorbereitung')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
