-- ============================================================
-- ServusPBX Medical 1.6.6
-- incoming ast_config Fix
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

DELETE FROM `ast_config`
WHERE `filename`='extensions.conf'
  AND `category`='incoming';

INSERT INTO `ast_config`
(`cat_metric`, `var_metric`, `commented`, `filename`, `category`, `var_name`, `var_val`)
VALUES
(1000, 0, 0, 'extensions.conf', 'incoming', 'switch', 'Realtime/@extensions');

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('incoming_ast_config_fix', '1.6.6', 'incoming Context wird in ast_config mit Realtime Switch erzeugt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Danach prüfen:
-- asterisk -rx "module reload pbx_config.so"
-- asterisk -rx "dialplan show incoming"
