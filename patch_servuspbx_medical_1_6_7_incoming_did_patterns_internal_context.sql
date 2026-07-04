-- ServusPBX Medical 1.6.7 - Incoming DID Patterns + trunkbezogener Internal Context

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('incoming_did_patterns_internal_context', '1.6.7', 'Anrufregeln erlauben DID Patterns und routen Nebenstellenziele nach internal_<rufnummer>')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
