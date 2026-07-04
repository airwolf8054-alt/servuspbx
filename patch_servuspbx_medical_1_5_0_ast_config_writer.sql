-- ============================================================
-- ServusPBX Medical 1.5.0
-- AstConfigWriter + korrekte BLF Pattern-Hints
-- ============================================================
--
-- Korrekte ast_config Struktur:
--
-- category internal_<rufnummer>:
--   exten  => _XX,hint,PJSIP/${EXTEN}
--   exten  => _XXX,hint,PJSIP/${EXTEN}
--   exten  => _XXXX,hint,PJSIP/${EXTEN}
--   switch => Realtime/@extensions
--
-- category outgoing_<rufnummer>:
--   switch => Realtime/@extensions
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

-- Alte dynamische Contexts sauber entfernen.
DELETE FROM `ast_config`
WHERE `filename`='extensions.conf'
  AND (
    `category` LIKE 'internal\\_%'
    OR `category` LIKE 'outgoing\\_%'
  );

-- Falsche Hints aus der Realtime-Dialplan-Tabelle entfernen.
DELETE FROM `extensions`
WHERE `context` LIKE 'internal\\_%'
  AND `priority`='hint';

-- Internal Contexts:
INSERT INTO `ast_config`
(`cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val`)
SELECT 2000 + `id`, 0, 0, 'extensions.conf',
       REPLACE(`outgoing_context`, 'outgoing_', 'internal_'),
       'exten',
       '_XX,hint,PJSIP/${EXTEN}'
FROM `spbx_outbound_routes`
WHERE `active`=1;

INSERT INTO `ast_config`
(`cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val`)
SELECT 2000 + `id`, 1, 0, 'extensions.conf',
       REPLACE(`outgoing_context`, 'outgoing_', 'internal_'),
       'exten',
       '_XXX,hint,PJSIP/${EXTEN}'
FROM `spbx_outbound_routes`
WHERE `active`=1;

INSERT INTO `ast_config`
(`cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val`)
SELECT 2000 + `id`, 2, 0, 'extensions.conf',
       REPLACE(`outgoing_context`, 'outgoing_', 'internal_'),
       'exten',
       '_XXXX,hint,PJSIP/${EXTEN}'
FROM `spbx_outbound_routes`
WHERE `active`=1;

INSERT INTO `ast_config`
(`cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val`)
SELECT 2000 + `id`, 10, 0, 'extensions.conf',
       REPLACE(`outgoing_context`, 'outgoing_', 'internal_'),
       'switch',
       'Realtime/@extensions'
FROM `spbx_outbound_routes`
WHERE `active`=1;

-- Outgoing Contexts:
INSERT INTO `ast_config`
(`cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val`)
SELECT 3000 + `id`, 0, 0, 'extensions.conf',
       `outgoing_context`,
       'switch',
       'Realtime/@extensions'
FROM `spbx_outbound_routes`
WHERE `active`=1;

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('ast_config_writer', '1.5.0', 'AstConfigWriter erzeugt Context-Switches und BLF Pattern-Hints sauber in ast_config'),
('ast_config_hints_format', '_XX/_XXX/_XXXX', 'BLF-Hints werden wie in der alten Anlage als exten-Entries in ast_config erzeugt')
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
