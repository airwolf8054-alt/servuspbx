-- ============================================================
-- ServusPBX Medical 1.4.6
-- ast_config Context Switches
-- ============================================================
--
-- Problem:
-- extensions enthält zwar Einträge für internal_<rufnummer>/outgoing_<rufnummer>,
-- aber Asterisk kennt den Context nicht, wenn kein
-- [context]
-- switch => Realtime/@extensions
-- existiert.
--
-- Lösung:
-- Dynamische Contexts werden in ast_config erzeugt.
-- ============================================================

CREATE TABLE IF NOT EXISTS `ast_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_metric` int(11) NOT NULL DEFAULT 0,
  `var_metric` int(11) NOT NULL DEFAULT 0,
  `commented` tinyint(1) NOT NULL DEFAULT 0,
  `filename` varchar(128) NOT NULL,
  `category` varchar(128) NOT NULL,
  `var_name` varchar(128) NOT NULL,
  `var_val` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_file_cat` (`filename`,`category`),
  KEY `idx_file_cat_var` (`filename`,`category`,`var_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bestehende ServusPBX dynamische Contexts entfernen.
DELETE FROM `ast_config`
WHERE `filename`='extensions.conf'
  AND (
    `category` LIKE 'internal\\_%'
    OR `category` LIKE 'outgoing\\_%'
  );

-- Aktuellen A1-Context sofort eintragen.
INSERT INTO `ast_config`
(`cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val`)
VALUES
(2000, 0, 0, 'extensions.conf', 'internal_43312423826', 'switch', 'Realtime/@extensions'),
(3000, 0, 0, 'extensions.conf', 'outgoing_43312423826', 'switch', 'Realtime/@extensions');

-- Alle aktiven Outbound-Routen ebenfalls eintragen.
INSERT INTO `ast_config`
(`cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val`)
SELECT
  2000 + id,
  0,
  0,
  'extensions.conf',
  REPLACE(outgoing_context, 'outgoing_', 'internal_'),
  'switch',
  'Realtime/@extensions'
FROM spbx_outbound_routes
WHERE active=1
ON DUPLICATE KEY UPDATE
  `var_val`=VALUES(`var_val`);

INSERT INTO `ast_config`
(`cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val`)
SELECT
  3000 + id,
  0,
  0,
  'extensions.conf',
  outgoing_context,
  'switch',
  'Realtime/@extensions'
FROM spbx_outbound_routes
WHERE active=1
ON DUPLICATE KEY UPDATE
  `var_val`=VALUES(`var_val`);

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('ast_config_context_switches', '1.4.6', 'internal_<rufnummer> und outgoing_<rufnummer> werden über ast_config mit Realtime Switch erzeugt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Danach:
-- asterisk -rx "dialplan reload"
-- Prüfen:
-- asterisk -rx "dialplan show internal_43312423826"
-- asterisk -rx "dialplan show outgoing_43312423826"
--
-- Wichtig:
-- extconfig.conf muss extensions.conf auf ast_config mappen, z.B.:
-- extensions.conf => odbc,asterisk,ast_config
-- ============================================================
