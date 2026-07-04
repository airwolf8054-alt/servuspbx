-- ============================================================
-- ServusPBX Medical 1.4.8
-- Pattern-Hints + saubere ast_config Metrik
-- ============================================================

-- Hints brauchen priority='hint'.
ALTER TABLE `extensions`
  MODIFY `priority` varchar(20) NOT NULL;

-- ast_config für dynamische Contexts sauber neu schreiben.
DELETE FROM `ast_config`
WHERE `filename`='extensions.conf'
  AND (
    `category` LIKE 'internal\\_%'
    OR `category` LIKE 'outgoing\\_%'
  );

INSERT INTO `ast_config`
(`cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val`)
SELECT
  2000,
  0,
  0,
  'extensions.conf',
  REPLACE(`outgoing_context`, 'outgoing_', 'internal_'),
  'switch',
  'Realtime/@extensions'
FROM `spbx_outbound_routes`
WHERE `active`=1;

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

-- Alte Einzel-Hints entfernen.
DELETE FROM `extensions`
WHERE `context` LIKE 'internal\\_%'
  AND `priority`='hint';

-- Pattern-Hint je internal_<rufnummer> Context.
-- entspricht:
-- exten => _X.,hint,PJSIP/${EXTEN}
INSERT INTO `extensions` (`context`, `exten`, `priority`, `app`, `appdata`)
SELECT
  REPLACE(`outgoing_context`, 'outgoing_', 'internal_'),
  '_X.',
  'hint',
  'PJSIP/${EXTEN}',
  ''
FROM `spbx_outbound_routes`
WHERE `active`=1;

-- Sicherheits-Fix: ab 5 Stellen aus internal_<rufnummer> zu outgoing_<rufnummer>.
DELETE FROM `extensions`
WHERE `context` LIKE 'internal\\_%'
  AND `exten`='_XXXXX.'
  AND `app`='Goto';

INSERT INTO `extensions` (`context`, `exten`, `priority`, `app`, `appdata`)
SELECT
  REPLACE(`outgoing_context`, 'outgoing_', 'internal_'),
  '_XXXXX.',
  '1',
  'Goto',
  CONCAT(`outgoing_context`, ',${EXTEN},1')
FROM `spbx_outbound_routes`
WHERE `active`=1;

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('pattern_hints', '1.4.8', 'BLF-Hints werden als _X. Pattern-Hint pro internal_<rufnummer> erzeugt'),
('ast_config_metric_clean', '1.4.8', 'ast_config cat_metric für internal/outgoing Contexts ist sauber 2000/3000')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Danach:
-- asterisk -rx "dialplan reload"
-- Prüfung:
-- SELECT * FROM ast_config WHERE filename='extensions.conf';
-- asterisk -rx "dialplan show internal_43312423826"
-- Erwartung:
-- '_X.' => hint: PJSIP/${EXTEN}
-- ============================================================
