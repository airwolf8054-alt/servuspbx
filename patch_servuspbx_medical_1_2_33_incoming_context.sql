-- ============================================================
-- ServusPBX Medical 1.2.33
-- Zentraler Incoming Context
-- ============================================================

-- Voraussetzung in /etc/asterisk/extensions.conf:
-- [incoming]
-- switch => Realtime/@extensions

UPDATE `ps_endpoints`
SET `context`='incoming'
WHERE `device_type`='trunk';

UPDATE `spbx_trunks`
SET `context`='incoming'
WHERE `context` IS NULL
   OR `context`=''
   OR `context` LIKE 'from\_%'
   OR `context`='from-trunk';

UPDATE `spbx_call_rules`
SET `trunk_context`='incoming'
WHERE `trunk_context` IS NULL
   OR `trunk_context`=''
   OR `trunk_context` LIKE 'from\_%'
   OR `trunk_context`='from-trunk';

UPDATE `spbx_trunk_providers`
SET `context`='incoming'
WHERE `context` IS NULL
   OR `context`=''
   OR `context`='from-trunk'
   OR `context` LIKE 'from\_%';

DELETE FROM `extensions`
WHERE `context` LIKE 'from\_%';

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('incoming_context', 'incoming', 'Zentraler Context für alle eingehenden SIP-Trunks'),
('call_rules_context_mode', 'central_incoming', 'Anrufregeln verwenden incoming + DID statt from_<rufnummer>'),
('call_rules_version', '1.2.33', 'Zentraler Incoming Context mit DID-Alias-Erzeugung')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Danach:
-- asterisk -rx "pjsip reload"
-- Anrufregel speichern, damit context incoming neu generiert wird.
-- asterisk -rx "dialplan show +43312423826@incoming"
