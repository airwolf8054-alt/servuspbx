-- ============================================================
-- ServusPBX Medical 1.2.21
-- Snom Telefonbuch korrekt im zentralen Provisioning
-- ============================================================
--
-- Telefonbuch wird aus spbx_phonebook erzeugt und exakt wie im alten
-- funktionierenden Script positioniert:
--
--   </phone-settings>
--   <tbook complete='true' e='2'>...</tbook>
--   <uploads>...</uploads>
--
-- Wichtig:
-- Keine directory-Tabelle.
-- Kein externes phonebook.xml.php als Hauptlösung.
-- Kein tbook außerhalb des <settings>-Root-Elements.
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('snom_phonebook_source', 'spbx_phonebook', 'Quelle für Snom Telefonbuch'),
('snom_phonebook_position', 'after_phone_settings_before_uploads', 'tbook wird nach phone-settings und vor uploads ausgegeben'),
('snom_phonebook_format', 'tbook_complete_true_e_2', 'Snom tbook Format wie altes Script')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.21
