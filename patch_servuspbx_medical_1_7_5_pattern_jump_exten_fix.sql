-- ============================================================
-- ServusPBX Medical 1.7.5
-- Pattern Jump EXTEN Fix
-- ============================================================
--
-- Fix:
-- Bei DID-Patterns wie _+43312423826XX wird innerhalb incoming nicht mehr
-- nach incoming,_+43312423826XX,12 gesprungen, sondern nach incoming,${EXTEN},12.
--
-- Dadurch bleibt ${EXTEN} die echte gewählte Rufnummer und ${EXTEN:12}
-- ergibt korrekt die Durchwahl.
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('pattern_jump_exten_fix', '1.7.5', 'Interne Sprünge bei DID-Patterns verwenden ${EXTEN} statt den Pattern-Namen')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
