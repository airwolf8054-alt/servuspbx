-- ============================================================
-- ServusPBX Medical 1.7.4
-- DID Prefix Offset Fix
-- ============================================================
--
-- Fix:
-- _+43312423826XX erzeugt nun:
--   Set(DIDEXT=${EXTEN:12})
--   Goto(internal_43312423826,${DIDEXT},1)
--
-- _XX erzeugt:
--   Set(DIDEXT=${EXTEN})
--   Goto(internal_<context>,${DIDEXT},1)
-- ============================================================

UPDATE spbx_call_rules
SET open_destination_type='did_extension'
WHERE did LIKE '\\_%'
  AND open_destination_context LIKE 'internal\\_%'
  AND (
    open_destination_exten REGEXP '^[Xx]{1,4}$'
    OR open_destination_exten IN ('AUTO_DID','${EXTEN:-2}')
  );

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('did_prefix_offset_fix', '1.7.4', 'DID Pattern nutzt festen Prefix-Offset wie ${EXTEN:12} statt ${EXTEN:-2}')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
