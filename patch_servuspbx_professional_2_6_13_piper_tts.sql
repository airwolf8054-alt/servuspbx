-- ServusPBX Professional 2.6.13 - Piper TTS
CREATE TABLE IF NOT EXISTS `spbx_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO spbx_settings (setting_key, setting_value, description) VALUES
('tts_engine', 'piper', 'TTS Engine'),
('tts_voice', 'de_DE-thorsten-medium', 'Zentrale Piper Stimme für alle TTS-Felder'),
('tts_piper_bin', '/usr/local/servuspbx/tts/piper/piper', 'Pfad zur Piper Binary'),
('tts_voice_dir', '/usr/local/servuspbx/tts/voices', 'Verzeichnis für Piper Stimmen'),
('tts_output_dir', '/var/lib/asterisk/sounds/custom/tts', 'Ausgabeverzeichnis für generierte MP3-Dateien'),
('tts_ffmpeg_bin', '/usr/bin/ffmpeg', 'Pfad zu ffmpeg'),
('piper_tts', '2.6.13', 'Piper Text-to-Speech mit zentraler Stimme und MP3-Erzeugung')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), description=VALUES(description);
