-- ServusPBX Medical 1.7.3 - DID Pattern Target Guard

-- Optionale Bereinigung falsch gespeicherter Regeln:
-- Wenn DID ein Asterisk Pattern ist und Ziel internal_* mit XX/XXX/XXXX gespeichert wurde,
-- wird open_destination_type auf did_extension gesetzt.
UPDATE spbx_call_rules
SET open_destination_type='did_extension'
WHERE did LIKE '\_%'
  AND open_destination_context LIKE 'internal\_%'
  AND open_destination_exten REGEXP '^[Xx]{1,4}$';

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('did_pattern_target_guard', '1.7.3', 'DID Pattern Ziele mit XX/XXX/XXXX werden zu Set(DIDEXT) + Goto(${DIDEXT}) normalisiert')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
