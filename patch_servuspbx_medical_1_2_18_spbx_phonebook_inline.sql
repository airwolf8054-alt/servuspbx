-- ============================================================
-- ServusPBX Medical 1.2.18
-- Snom Telefonbuch inline aus spbx_phonebook
-- ============================================================
--
-- Telefonbuch wird direkt im Snom Provisioning ausgegeben:
-- nach </phone-settings> und vor <uploads>.
--
-- Quelle:
-- spbx_phonebook
--
-- Erwartete Felder werden tolerant erkannt:
-- first_name oder firstname
-- last_name  oder lastname
-- number     oder phone_number
-- type       optional, Standard office
-- active     optional
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('snom_phonebook_mode', 'inline_tbook', 'Snom Telefonbuch wird direkt im Provisioning ausgegeben'),
('snom_phonebook_source', 'spbx_phonebook', 'Quelle für Snom Telefonbuch'),
('snom_phonebook_format', 'tbook', 'Snom tbook Format complete=true e=2')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.18
