-- ============================================================
-- ServusPBX Medical 1.4.5
-- Outbound/Internal Rule + Empty Identify Fix
-- ============================================================

-- Leeren Identify-Eintrag entfernen.
DELETE FROM ps_endpoint_id_ips
WHERE id='trunk-a1-43312423826'
   OR endpoint='trunk-a1-43312423826';

-- Sofort-Fix für aktuellen A1 Context.
-- Falls der Dialplan-Generator noch nicht neu gelaufen ist:
DELETE FROM extensions
WHERE context='internal_43312423826'
  AND exten='_XXXXX.'
  AND app='Goto';

INSERT INTO extensions (context, exten, priority, app, appdata)
VALUES ('internal_43312423826', '_XXXXX.', 1, 'Goto', 'outgoing_43312423826,${EXTEN},1');

-- Outgoing muss alles annehmen.
DELETE FROM extensions
WHERE context='outgoing_43312423826'
  AND exten='_X.'
  AND priority IN (1,2,3,4,5,6,7,8,9,10);

INSERT INTO extensions (context, exten, priority, app, appdata) VALUES
('outgoing_43312423826','_X.',1,'NoOp','ServusPBX A1 outbound ${EXTEN}'),
('outgoing_43312423826','_X.',2,'Set','A1_MAIN=+43312423826'),
('outgoing_43312423826','_X.',3,'Set','EXTNR=${CALLERID(num)}'),
('outgoing_43312423826','_X.',4,'Set','CALLERID(num)=${A1_MAIN}${EXTNR}'),
('outgoing_43312423826','_X.',5,'Set','CALLERID(name)='),
('outgoing_43312423826','_X.',6,'Set','DST=${EXTEN}'),
('outgoing_43312423826','_X.',7,'ExecIf','$["${DST:0:1}"="0"]?Set(DST=+43${DST:1})'),
('outgoing_43312423826','_X.',8,'NoOp','A1 From/CLI: ${CALLERID(num)} Ziel: ${DST}'),
('outgoing_43312423826','_X.',9,'Dial','PJSIP/${DST}@trunk-a1-43312423826,60'),
('outgoing_43312423826','_X.',10,'Hangup','');

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('outbound_internal_identify_fix', '1.4.5', 'internal_<rufnummer> routet ab 5 Stellen zu outgoing_<rufnummer>; leere Identify-Einträge entfernt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Danach:
-- asterisk -rx "dialplan reload"
-- asterisk -rx "pjsip reload"
