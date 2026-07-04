-- ============================================================
-- ServusPBX Medical 1.2.18
-- Snom Telefonbuch im tbook-Format
-- ============================================================
--
-- Hinweis:
-- Dieser Patch ersetzt den verworfenen generischen Addressbook-Patch.
-- Verwendet wird das bewährte Snom-Format:
--
-- <tbook complete="true" e="2">
--   <item context="active" type="office">
--     <first_name>...</first_name>
--     <last_name>...</last_name>
--     <number>...</number>
--   </item>
-- </tbook>
-- ============================================================

CREATE TABLE IF NOT EXISTS `directory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(120) DEFAULT NULL,
  `last_name` varchar(120) DEFAULT NULL,
  `number` varchar(80) NOT NULL,
  `type` varchar(40) NOT NULL DEFAULT 'office',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_directory_name` (`last_name`,`first_name`),
  KEY `idx_directory_number` (`number`),
  KEY `idx_directory_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `directory`
  ADD COLUMN IF NOT EXISTS `first_name` varchar(120) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `last_name` varchar(120) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `number` varchar(80) NOT NULL,
  ADD COLUMN IF NOT EXISTS `type` varchar(40) NOT NULL DEFAULT 'office',
  ADD COLUMN IF NOT EXISTS `active` tinyint(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS `created_at` timestamp NULL DEFAULT current_timestamp(),
  ADD COLUMN IF NOT EXISTS `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp();

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('snom_phonebook_format', 'tbook', 'Snom Telefonbuch wird im bewährten tbook Format erzeugt'),
('snom_phonebook_url', 'http://10.43.4.244/provision/tbook.php', 'Snom tbook Telefonbuch URL'),
('snom_phonebook_source', 'directory', 'Primäre Quelle ist die Tabelle directory; Fallback ps_endpoints')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.18
