-- ServusPBX Medical 1.6.7 - Call Rules DID Patterns + internal_<rufnummer>

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rules_did_patterns_internal_context', '1.6.7', 'Anrufregeln erlauben DID Patterns und routen Nebenstellenziele automatisch auf internal_<trunknummer>')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
