-- ============================================================
-- ServusPBX Medical 1.1.6
-- Voicemail Context Based Mailboxes
-- ============================================================
--
-- Ziel:
-- Voicemail-Zuordnung richtet sich nach ps_endpoints.context.
--
-- Regel:
-- ps_endpoints.mailboxes = <extension>@<ps_endpoints.context>
-- voicemail.context      = <ps_endpoints.context>
-- voicemail.mailbox      = <extension>
--
-- Damit sind später mehrere Bereiche/Mandanten möglich:
-- 10@internal
-- 10@pflege
-- 10@verwaltung
--
-- Vorher Backup:
-- mysqldump -u root -p general > /root/general_before_1_1_6.sql
-- ============================================================

-- ------------------------------------------------------------
-- 1. Context absichern
-- ------------------------------------------------------------

UPDATE `ps_endpoints`
SET `context` = 'internal'
WHERE `context` IS NULL
   OR `context` = ''
   OR `context` = 'default'
   OR `context` = 'from-internal';

-- ------------------------------------------------------------
-- 2. Mailbox-Zuordnung endpoint-basiert setzen
-- ------------------------------------------------------------

UPDATE `ps_endpoints`
SET `mailboxes` = CONCAT(`extension`, '@', `context`)
WHERE `active`=1
  AND `extension` IS NOT NULL
  AND `extension` <> ''
  AND `context` IS NOT NULL
  AND `context` <> '';

-- ------------------------------------------------------------
-- 3. Voicemail-Tabelle sicherstellen
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `voicemail` (
  `uniqueid` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(40) DEFAULT NULL,
  `context` varchar(80) NOT NULL DEFAULT 'internal',
  `mailbox` varchar(80) NOT NULL,
  `password` varchar(80) NOT NULL DEFAULT '',
  `fullname` varchar(160) DEFAULT NULL,
  `email` varchar(160) DEFAULT NULL,
  `pager` varchar(160) DEFAULT NULL,
  `attach` varchar(10) DEFAULT 'yes',
  `attachfmt` varchar(20) DEFAULT 'wav',
  `serveremail` varchar(160) DEFAULT NULL,
  `language` varchar(10) DEFAULT 'de',
  `tz` varchar(40) DEFAULT NULL,
  `deletevoicemail` varchar(10) DEFAULT 'yes',
  `saycid` varchar(10) DEFAULT 'no',
  `sendvoicemail` varchar(10) DEFAULT 'no',
  `review` varchar(10) DEFAULT 'no',
  `tempgreetwarn` varchar(10) DEFAULT 'no',
  `operator` varchar(10) DEFAULT 'no',
  `envelope` varchar(10) DEFAULT 'no',
  `sayduration` varchar(10) DEFAULT 'no',
  `forcename` varchar(10) DEFAULT 'no',
  `forcegreetings` varchar(10) DEFAULT 'no',
  `callback` varchar(80) DEFAULT NULL,
  `dialout` varchar(80) DEFAULT NULL,
  `exitcontext` varchar(80) DEFAULT NULL,
  `maxmsg` int(11) DEFAULT 100,
  `volgain` decimal(5,2) DEFAULT 0.00,
  PRIMARY KEY (`uniqueid`),
  UNIQUE KEY `uniq_context_mailbox` (`context`,`mailbox`),
  KEY `mailbox` (`mailbox`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `voicemail`
  ADD COLUMN IF NOT EXISTS `context` varchar(80) NOT NULL DEFAULT 'internal',
  ADD COLUMN IF NOT EXISTS `mailbox` varchar(80) NOT NULL,
  ADD COLUMN IF NOT EXISTS `password` varchar(80) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS `fullname` varchar(160) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `email` varchar(160) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `attach` varchar(10) DEFAULT 'yes',
  ADD COLUMN IF NOT EXISTS `attachfmt` varchar(20) DEFAULT 'wav',
  ADD COLUMN IF NOT EXISTS `serveremail` varchar(160) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `language` varchar(10) DEFAULT 'de',
  ADD COLUMN IF NOT EXISTS `deletevoicemail` varchar(10) DEFAULT 'yes',
  ADD COLUMN IF NOT EXISTS `saycid` varchar(10) DEFAULT 'no',
  ADD COLUMN IF NOT EXISTS `envelope` varchar(10) DEFAULT 'no';

ALTER TABLE `voicemail`
  ADD UNIQUE KEY IF NOT EXISTS `uniq_context_mailbox` (`context`,`mailbox`);

-- ------------------------------------------------------------
-- 4. Bestehende Altmailboxen aus default/internal bereinigen
-- ------------------------------------------------------------

UPDATE `voicemail`
SET `context`='internal'
WHERE `context` IS NULL
   OR `context`=''
   OR `context`='default'
   OR `context`='from-internal';

-- ------------------------------------------------------------
-- 5. Voicemail-Datensätze aus ps_endpoints erzeugen/aktualisieren
-- ------------------------------------------------------------
-- Mail-only:
-- attach=yes, deletevoicemail=yes, keine PIN-Abfrage in GUI.
-- password bleibt technisch vorhanden, wird aber nicht verwendet.

INSERT INTO `voicemail`
(`context`, `mailbox`, `password`, `fullname`, `email`, `attach`, `attachfmt`, `language`, `deletevoicemail`, `saycid`, `envelope`)
SELECT
  p.context,
  p.extension,
  p.extension,
  p.display_name,
  NULL,
  'yes',
  'wav',
  'de',
  'yes',
  'no',
  'no'
FROM `ps_endpoints` p
WHERE p.active=1
  AND p.extension IS NOT NULL
  AND p.extension <> ''
  AND p.context IS NOT NULL
  AND p.context <> ''
ON DUPLICATE KEY UPDATE
  `fullname`=VALUES(`fullname`),
  `language`='de',
  `attach`='yes',
  `attachfmt`='wav',
  `deletevoicemail`='yes',
  `saycid`='no',
  `envelope`='no';

-- ------------------------------------------------------------
-- 6. Voicemail-Abfrage-Codes entfernen
-- ------------------------------------------------------------

DELETE FROM `extensions`
WHERE `exten` IN ('*97','*98')
  AND `app` IN ('VoiceMailMain','VoicemailMain');

-- ------------------------------------------------------------
-- 7. Settings
-- ------------------------------------------------------------

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('voicemail_context_mode', 'endpoint_context', 'Mailbox-Kontext wird aus ps_endpoints.context abgeleitet'),
('voicemail_mailbox_format', 'extension@context', 'ps_endpoints.mailboxes = extension@ps_endpoints.context'),
('voicemail_delivery_mode', 'mail_only', 'Voicemail wird nur per E-Mail zugestellt, kein Abruf per Durchwahl'),
('voicemail_main_email', '', 'Zentrale Haupt-Voicemail-Adresse, optional'),
('voicemail_attach', 'yes', 'Voicemail als WAV anhängen'),
('voicemail_delete_after_mail', 'yes', 'Nach Mailversand wird Voicemail lokal gelöscht')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- ============================================================
-- Ende ServusPBX Medical 1.1.6
-- ============================================================
