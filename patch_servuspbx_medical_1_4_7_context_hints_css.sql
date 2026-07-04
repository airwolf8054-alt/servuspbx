-- ============================================================
-- ServusPBX Medical 1.4.7
-- Context Hints + Outbound CSS
-- ============================================================
--
-- Wichtig:
-- Für Asterisk-Hints muss extensions.priority auch den Wert 'hint'
-- speichern können. Falls priority bisher INT war, wird sie auf VARCHAR(20)
-- geändert.
-- ============================================================

ALTER TABLE `extensions`
  MODIFY `priority` varchar(20) NOT NULL;

-- Bestehende Hints in dynamischen internal_ Contexts neu erzeugen.
DELETE FROM `extensions`
WHERE `context` LIKE 'internal\\_%'
  AND `priority`='hint';

INSERT INTO `extensions` (`context`, `exten`, `priority`, `app`, `appdata`)
SELECT
  REPLACE(o.`outgoing_context`, 'outgoing_', 'internal_') AS context,
  e.`extension` AS exten,
  'hint' AS priority,
  CONCAT('PJSIP/', e.`extension`) AS app,
  '' AS appdata
FROM `spbx_outbound_routes` o
JOIN `spbx_extensions` e
WHERE o.`active`=1
  AND e.`extension` REGEXP '^[0-9]{2,4}$';

-- Sicherheits-Fix: internal_<rufnummer> ab 5 Stellen zu outgoing_<rufnummer>.
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
('context_hints', '1.4.7', 'BLF/PRESENCE Hints für 2-, 3- und 4-stellige Nebenstellen in internal_<rufnummer> Contexts'),
('outbound_routes_css_guard', '1.4.7', 'Ausgehende Routen Formular verwendet einheitlichen ServusPBX Form Style')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Danach:
-- asterisk -rx "dialplan reload"
-- Prüfung:
-- asterisk -rx "dialplan show internal_43312423826"
-- Erwartung:
-- 10 => hint: PJSIP/10
-- ============================================================
