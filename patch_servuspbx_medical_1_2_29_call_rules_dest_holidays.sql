-- ============================================================
-- ServusPBX Medical 1.2.29
-- Anrufregeln Zielauswahl + globale Feiertagsseite
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rules_destination_ui', '1.2.29', 'Offen-Ziel mit Zieltyp und Nebenstellen-Auswahl'),
('holidays_page', 'pages/holidays.php', 'Globale Feiertage werden separat gepflegt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.29
