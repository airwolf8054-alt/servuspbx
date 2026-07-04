-- ============================================================
-- ServusPBX Medical 1.4.9
-- Pattern-Hints in ast_config
-- ============================================================
--
-- Korrektur:
-- Der BLF-Hint gehört bei unserem Setup in ast_config/extensions.conf:
--
-- [internal_<rufnummer>]
-- exten => _X.,hint,PJSIP/${EXTEN}
-- switch => Realtime/@extensions
--
-- Die eigentlichen Dialplan-Regeln bleiben in der Tabelle extensions.
-- ============================================================

-- Alte Hint-Einträge in der Realtime-Dialplan-Tabelle entfernen.
DELETE FROM `extensions`
WHERE `context` LIKE 'internal\\_%'
  AND `priority`='hint';

-- Dynamische Contexts in ast_config bereinigen.
DELETE FROM `ast_config`
WHERE `filename`='extensions.conf'
  AND (
    `category` LIKE 'internal\\_%'
    OR `category` LIKE 'outgoing\\_%'
  );

-- internal_<rufnummer> bekommt zuerst den Pattern-Hint.
INSERT INTO `ast_config`
(`cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val`)
SELECT
  2000,
  0,
  0,
  'extensions.conf',
  REPLACE(`outgoing_context`, 'outgoing_', 'internal_'),
  'exten',
  '_X.,hint,PJSIP/${EXTEN}'
FROM `spbx_outbound_routes`
WHERE `active`=1;

-- Danach der Realtime-Switch.
INSERT INTO `ast_config`
(`cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val`)
SELECT
  2000,
  1,
  0,
  'extensions.conf',
  REPLACE(`outgoing_context`, 'outgoing_', 'internal_'),
  'switch',
  'Realtime/@extensions'
FROM `spbx_outbound_routes`
WHERE `active`=1;

-- outgoing_<rufnummer> braucht nur den Realtime-Switch.
INSERT INTO `ast_config`
(`cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val`)
SELECT
  3000,
  0,
  0,
  'extensions.conf',
  `outgoing_context`,
  'switch',
  'Realtime/@extensions'
FROM `spbx_outbound_routes`
WHERE `active`=1;

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('ast_config_pattern_hints', '1.4.9', 'BLF Pattern-Hints werden in ast_config/extensions.conf erzeugt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Danach:
-- asterisk -rx "module reload pbx_config.so"
-- asterisk -rx "dialplan reload"
--
-- Prüfung:
-- SELECT * FROM ast_config WHERE filename='extensions.conf' ORDER BY category,var_metric;
-- asterisk -rx "dialplan show internal_43312423826"
-- ============================================================
