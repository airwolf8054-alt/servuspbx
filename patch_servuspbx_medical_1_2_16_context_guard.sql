-- ============================================================
-- ServusPBX Medical 1.2.16
-- Context Guard / Repair
-- ============================================================
--
-- Regel:
-- - Interne Nebenstellen: context='internal'
-- - SIP-Trunks: context='from_<rufnummer>'
--
-- Dieser Patch repariert bestehende falsche Telefon-Contexts.
-- ============================================================

-- 1. Normale Telefone/Nebenstellen wieder auf internal setzen.
UPDATE `ps_endpoints`
SET `context`='internal'
WHERE (`device_type` IS NULL OR `device_type` <> 'trunk')
  AND (`context` IS NULL OR `context` <> 'internal');

-- 2. spbx_devices/Telefonreferenzen bleiben unberührt; nur ps_endpoints.context wird korrigiert.

-- 3. Übersicht falscher Contexts nach Reparatur.
SELECT `id`, `extension`, `display_name`, `device_type`, `device_model`, `context`
FROM `ps_endpoints`
WHERE (`device_type` IS NULL OR `device_type` <> 'trunk')
  AND `context` <> 'internal';

-- 4. Marker
INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('context_guard', '1.2.16', 'Interne Nebenstellen werden auf context=internal geschützt; Trunks verwenden from_<rufnummer>'),
('phone_context_default', 'internal', 'Standard-Kontext für Nebenstellen'),
('trunk_context_format', 'from_number', 'SIP-Trunk Context Format from_<rufnummer>')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.16
