-- ============================================================
-- ServusPBX Medical 1.1.5
-- Voicemail Mail-Only + Context internal
-- ============================================================
--
-- Ziel:
-- - Kein default Context mehr.
-- - Voicemail-Kontext heißt internal.
-- - Voicemail nur per E-Mail-Zustellung.
-- - Keine PIN-Abfrage in ServusPBX.
-- - E-Mail-Adresse je Nebenstelle bleibt optional.
-- - Haupt-Voicemail-Adresse als zentrales Setting.
--
-- Vorher Backup:
-- mysqldump -u root -p general > /root/general_before_1_1_5.sql
-- ============================================================

-- ------------------------------------------------------------
-- 1. Überall default -> internal
-- ------------------------------------------------------------

UPDATE `ps_endpoints`
SET
  `context`='internal',
  `mailboxes`=REPLACE(`mailboxes`, '@default', '@internal')
WHERE `context`='default'
   OR `context`='from-internal'
   OR `mailboxes` LIKE '%@default%';

-- ps_aors.mailboxes wird bewusst nicht verwendet; Mailbox-Zuordnung liegt in ps_endpoints.mailboxes.

-- Dialplan Default-Altlasten entfernen/vermeiden.
UPDATE `extensions`
SET `context`='internal'
WHERE `context` IN ('default','from-internal');

-- ------------------------------------------------------------
-- 2. Voicemail Realtime Tabelle
-- ------------------------------------------------------------
-- Asterisk Realtime voicemail.conf Schema, praxisnah und mail-only.
-- password bleibt technisch vorhanden, wird aber automatisch gesetzt
-- und nicht mehr in der GUI abgefragt.

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
  `imapuser` varchar(160) DEFAULT NULL,
  `imappassword` varchar(160) DEFAULT NULL,
  `imapserver` varchar(160) DEFAULT NULL,
  `imapport` varchar(20) DEFAULT NULL,
  `imapflags` varchar(160) DEFAULT NULL,
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

-- ------------------------------------------------------------
-- 3. Mailboxen aus Endpoints erzeugen/aktualisieren
-- ------------------------------------------------------------
-- password wird technisch auf die Durchwahl gesetzt, aber nicht verwendet/abgefragt.
-- deletevoicemail=yes bedeutet: Nachricht wird nach Mailversand aus der Telefonanlage gelöscht.

INSERT INTO `voicemail`
(`context`, `mailbox`, `password`, `fullname`, `email`, `attach`, `attachfmt`, `language`, `deletevoicemail`, `saycid`, `envelope`)
SELECT
  'internal',
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
ON DUPLICATE KEY UPDATE
  `fullname`=VALUES(`fullname`),
  `language`='de',
  `attach`='yes',
  `attachfmt`='wav',
  `deletevoicemail`='yes',
  `saycid`='no',
  `envelope`='no';

UPDATE `ps_endpoints`
SET `mailboxes` = CONCAT(`extension`, '@internal')
WHERE `active`=1
  AND `extension` IS NOT NULL
  AND `extension` <> '';

-- Keine Aktualisierung von ps_aors.mailboxes: Spalte existiert in dieser Installation nicht und ist nicht erforderlich.

-- ------------------------------------------------------------
-- 4. Voicemail-Abfrage-Codes entfernen
-- ------------------------------------------------------------
-- Kein *97/*98 und kein lokales Abrufen mit PIN.

DELETE FROM `extensions`
WHERE `context`='internal'
  AND `exten` IN ('*97','*98')
  AND `app` IN ('VoiceMailMain','VoicemailMain');

-- ------------------------------------------------------------
-- 5. Zentrale Settings
-- ------------------------------------------------------------

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('voicemail_context', 'internal', 'Voicemail Kontext ist internal; default wird nicht mehr verwendet'),
('voicemail_delivery_mode', 'mail_only', 'Voicemail wird nur per E-Mail zugestellt, kein Abruf per Durchwahl'),
('voicemail_main_email', '', 'Zentrale Haupt-Voicemail-Adresse, optional'),
('voicemail_attach', 'yes', 'Voicemail als WAV anhängen'),
('voicemail_delete_after_mail', 'yes', 'Nach Mailversand wird Voicemail lokal gelöscht'),
('voicemail_patch_fix', '1.1.5', 'ps_aors.mailboxes wird nicht mehr verwendet')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- ============================================================
-- Ende ServusPBX Medical 1.1.5
-- ============================================================
