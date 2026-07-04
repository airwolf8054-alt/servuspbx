-- ============================================================
-- ServusPBX Medical 1.3.0
-- Telephony Base: Incoming bleibt stabil, Outgoing/Internal sauber
-- ============================================================
--
-- Ziel:
-- - eingehend bleibt: incoming
-- - ausgehend: outgoing_<rufnummer>
-- - intern: internal_<rufnummer>
-- - Länder-/Notrufprofile datenbankgestützt
-- - robuste Migration auch wenn spbx_outbound_routes noch fehlt
-- ============================================================

-- ------------------------------------------------------------
-- 1. Outbound Routes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `spbx_outbound_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `route_name` varchar(120) NOT NULL,
  `provider` varchar(40) NOT NULL DEFAULT 'A1',
  `main_number` varchar(40) NOT NULL,
  `trunk_endpoint` varchar(120) NOT NULL,
  `outgoing_context` varchar(120) NOT NULL,
  `clip_no_screening` tinyint(1) NOT NULL DEFAULT 1,
  `callerid_mode` enum('main','extension') NOT NULL DEFAULT 'extension',
  `emergency_profile` enum('AT','DE','CH','LI','CUSTOM') NOT NULL DEFAULT 'AT',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_out_ctx` (`outgoing_context`),
  KEY `idx_endpoint` (`trunk_endpoint`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Spalte für Altstände ergänzen.
SET @col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'spbx_outbound_routes'
    AND COLUMN_NAME = 'emergency_profile'
);
SET @sql := IF(@col_exists = 0,
  "ALTER TABLE `spbx_outbound_routes` ADD COLUMN `emergency_profile` ENUM('AT','DE','CH','LI','CUSTOM') NOT NULL DEFAULT 'AT' AFTER `callerid_mode`",
  "SELECT 'emergency_profile already exists'"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- 2. Länder-/Notrufprofile
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `spbx_country_emergency_numbers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(10) NOT NULL,
  `country_name` varchar(80) NOT NULL,
  `emergency_number` varchar(20) NOT NULL,
  `description` varchar(120) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 100,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_country_number` (`country_code`, `emergency_number`),
  KEY `idx_country_active` (`country_code`, `active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `spbx_country_emergency_numbers`
(`country_code`, `country_name`, `emergency_number`, `description`, `sort_order`, `active`) VALUES
('AT','Österreich','112','Euro-Notruf',10,1),
('AT','Österreich','122','Feuerwehr',20,1),
('AT','Österreich','133','Polizei',30,1),
('AT','Österreich','144','Rettung',40,1),
('AT','Österreich','141','Ärztenotdienst',50,1),
('AT','Österreich','1450','Gesundheitsberatung',60,1),
('DE','Deutschland','110','Polizei',10,1),
('DE','Deutschland','112','Feuerwehr / Rettung',20,1),
('DE','Deutschland','115','Behördennummer',30,1),
('DE','Deutschland','116117','Ärztlicher Bereitschaftsdienst',40,1),
('CH','Schweiz','112','Euro-Notruf',10,1),
('CH','Schweiz','117','Polizei',20,1),
('CH','Schweiz','118','Feuerwehr',30,1),
('CH','Schweiz','144','Sanität',40,1),
('CH','Schweiz','145','Tox Info Suisse',50,1),
('CH','Schweiz','1414','Rega',60,1),
('CH','Schweiz','143','Dargebotene Hand',70,1),
('CH','Schweiz','147','Pro Juventute',80,1),
('LI','Liechtenstein','112','Euro-Notruf',10,1),
('LI','Liechtenstein','117','Polizei',20,1),
('LI','Liechtenstein','118','Feuerwehr',30,1),
('LI','Liechtenstein','144','Rettung',40,1)
ON DUPLICATE KEY UPDATE
  `country_name`=VALUES(`country_name`),
  `description`=VALUES(`description`),
  `sort_order`=VALUES(`sort_order`),
  `active`=VALUES(`active`);

-- ------------------------------------------------------------
-- 3. Bestehende Trunks zu Outbound-Routen synchronisieren
-- ------------------------------------------------------------
INSERT INTO `spbx_outbound_routes`
(`route_name`, `provider`, `main_number`, `trunk_endpoint`, `outgoing_context`,
 `clip_no_screening`, `callerid_mode`, `emergency_profile`, `active`)
SELECT
  CONCAT(UPPER(COALESCE(NULLIF(provider,''),'A1')), ' ', COALESCE(NULLIF(main_number,''), endpoint_id)),
  UPPER(COALESCE(NULLIF(provider,''),'A1')),
  CASE
    WHEN LEFT(main_number,1)='+' THEN CONCAT('+', REGEXP_REPLACE(main_number, '[^0-9]', ''))
    WHEN REGEXP_REPLACE(main_number, '[^0-9]', '') LIKE '43%' THEN CONCAT('+', REGEXP_REPLACE(main_number, '[^0-9]', ''))
    WHEN REGEXP_REPLACE(main_number, '[^0-9]', '') LIKE '0%' THEN CONCAT('+43', SUBSTRING(REGEXP_REPLACE(main_number, '[^0-9]', ''),2))
    ELSE CONCAT('+', REGEXP_REPLACE(main_number, '[^0-9]', ''))
  END,
  endpoint_id,
  CONCAT('outgoing_',
    REGEXP_REPLACE(
      CASE
        WHEN LEFT(main_number,1)='+' THEN main_number
        WHEN REGEXP_REPLACE(main_number, '[^0-9]', '') LIKE '43%' THEN main_number
        WHEN REGEXP_REPLACE(main_number, '[^0-9]', '') LIKE '0%' THEN CONCAT('43', SUBSTRING(REGEXP_REPLACE(main_number, '[^0-9]', ''),2))
        ELSE REGEXP_REPLACE(main_number, '[^0-9]', '')
      END,
      '[^0-9]', ''
    )
  ),
  1,
  'extension',
  CASE
    WHEN REGEXP_REPLACE(main_number, '[^0-9]', '') LIKE '49%' THEN 'DE'
    WHEN REGEXP_REPLACE(main_number, '[^0-9]', '') LIKE '41%' THEN 'CH'
    WHEN REGEXP_REPLACE(main_number, '[^0-9]', '') LIKE '423%' THEN 'LI'
    ELSE 'AT'
  END,
  COALESCE(active,1)
FROM `spbx_trunks`
WHERE COALESCE(endpoint_id,'') <> ''
  AND COALESCE(main_number,'') <> ''
ON DUPLICATE KEY UPDATE
  `route_name`=VALUES(`route_name`),
  `provider`=VALUES(`provider`),
  `main_number`=VALUES(`main_number`),
  `trunk_endpoint`=VALUES(`trunk_endpoint`),
  `active`=VALUES(`active`);

-- ------------------------------------------------------------
-- 4. A1 Inbound Guard
-- ------------------------------------------------------------
UPDATE `ps_endpoints` p
JOIN `spbx_trunks` t ON t.endpoint_id = p.id
SET p.auth = NULL,
    p.context = 'incoming'
WHERE p.device_type = 'trunk'
  AND UPPER(COALESCE(NULLIF(t.provider,''),'A1')) = 'A1';

-- ------------------------------------------------------------
-- 5. Settings Marker
-- ------------------------------------------------------------
INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('servuspbx_schema_version', '1.3.0', 'Telephony Base konsolidiert'),
('incoming_context', 'incoming', 'Eingehende Trunks bleiben zentral auf incoming'),
('internal_context_mode', 'per_number', 'Nebenstellen verwenden internal_<rufnummer>'),
('outbound_callflow_version', '1.3.0', 'Ausgehend via outgoing_<rufnummer> mit Notrufprofilen'),
('outbound_emergency_profiles', 'db', 'Notrufnummern werden aus spbx_country_emergency_numbers gelesen'),
('outgoing_pattern', '_X.', 'Outgoing Context nimmt alle Ziele an'),
('internal_outgoing_threshold', '5', 'Alles ab 5 Stellen geht aus internal_<rufnummer> zu outgoing_<rufnummer>')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- ------------------------------------------------------------
-- Nach Installation
-- ------------------------------------------------------------
-- 1. Dateien kopieren.
-- 2. /pages/outbound_routes.php öffnen.
-- 3. Land/Notrufprofil prüfen.
-- 4. "Dialplan neu aufbauen" klicken.
-- 5. sudo /usr/sbin/asterisk -rx "pjsip reload"
--
-- Prüfung:
-- SELECT * FROM spbx_outbound_routes;
-- SELECT * FROM spbx_country_emergency_numbers ORDER BY country_code, sort_order;
-- SELECT id, context, auth, outbound_auth FROM ps_endpoints WHERE device_type='trunk';
-- SELECT id, context FROM ps_endpoints WHERE device_type IS NULL OR device_type <> 'trunk';
-- ============================================================
