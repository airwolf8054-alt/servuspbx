-- ============================================================
-- ServusPBX Medical 1.2.32
-- extensions.comment Schema-Fix
-- ============================================================
--
-- Der Dialplan-Generator erkennt jetzt automatisch, ob die Tabelle
-- extensions eine Spalte `comment` besitzt.
--
-- Keine DB-Änderung zwingend notwendig.
-- Optional kann man die Spalte ergänzen, wenn Kommentare gewünscht sind:
--
-- ALTER TABLE `extensions`
--   ADD COLUMN `comment` varchar(255) DEFAULT NULL;
--
INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('call_rules_extensions_comment_fix', '1.2.32', 'Dialplan-Generator unterstützt extensions mit oder ohne comment-Spalte')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.32
