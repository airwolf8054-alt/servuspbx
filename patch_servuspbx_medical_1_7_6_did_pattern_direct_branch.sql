-- ============================================================
-- ServusPBX Medical 1.7.6
-- DID Pattern Direct Branch
-- ============================================================
--
-- Fix:
-- DID-Pattern werden nicht mehr intern auf incoming,_+...XX weitergeleitet.
-- Stattdessen bleibt der Call im gleichen Pattern und springt nur auf Prioritäten.
--
-- Erwarteter Dialplan:
--   GotoIfTime(...?80)
--   Goto(26)
--   80 Set(DURCHWAHL=${EXTEN:12})
--   81 NoOp(...)
--   82 Goto(internal_43312423826,${DURCHWAHL},1)
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
('did_pattern_direct_branch', '1.7.6', 'DID Pattern bleiben im Pattern und springen nur auf Prioritäten; EXTEN bleibt echte Rufnummer')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
