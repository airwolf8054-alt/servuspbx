-- ============================================================
-- ServusPBX Medical 1.1.3
-- spbx_devices Cleanup / Normalisierung
-- ============================================================
--
-- Ziel:
-- spbx_devices als zentrale Geräte-/Provisioning-Tabelle stabilisieren.
--
-- Regeln:
-- - spbx_devices.endpoint_id verweist logisch auf ps_endpoints.id.
-- - ps_endpoints.id ist immer die Durchwahl.
-- - Tischtelefone: MAC gesetzt, IPEI NULL.
-- - DECT-Handsets: IPEI gesetzt, MAC NULL.
-- - SIP-User: MAC NULL, IPEI NULL.
--
-- Vorher Backup:
-- mysqldump -u root -p general > /root/general_before_1_1_3.sql
-- ============================================================

-- ------------------------------------------------------------
-- 1. Tabelle falls notwendig erstellen
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `spbx_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `endpoint_id` varchar(80) NOT NULL,
  `device_model` varchar(80) NOT NULL DEFAULT 'sip_user',
  `extension` varchar(20) DEFAULT NULL,
  `display_name` varchar(160) DEFAULT NULL,
  `mac` varchar(80) DEFAULT NULL,
  `ipei` varchar(80) DEFAULT NULL,
  `serial_number` varchar(120) DEFAULT NULL,
  `provisioning_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `provision_file` varchar(120) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_spbx_devices_endpoint_id` (`endpoint_id`),
  KEY `idx_spbx_devices_extension` (`extension`),
  KEY `idx_spbx_devices_device_model` (`device_model`),
  KEY `idx_spbx_devices_mac` (`mac`),
  KEY `idx_spbx_devices_ipei` (`ipei`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 2. Fehlende Spalten ergänzen
-- ------------------------------------------------------------

ALTER TABLE `spbx_devices`
  ADD COLUMN IF NOT EXISTS `endpoint_id` varchar(80) NOT NULL,
  ADD COLUMN IF NOT EXISTS `device_model` varchar(80) NOT NULL DEFAULT 'sip_user',
  ADD COLUMN IF NOT EXISTS `extension` varchar(20) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `display_name` varchar(160) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `mac` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `ipei` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `serial_number` varchar(120) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `provisioning_enabled` tinyint(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS `provision_file` varchar(120) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `active` tinyint(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS `created_at` timestamp NULL DEFAULT current_timestamp(),
  ADD COLUMN IF NOT EXISTS `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp();

-- ------------------------------------------------------------
-- 3. Leere Strings sauber auf NULL setzen
-- ------------------------------------------------------------

UPDATE `spbx_devices`
SET `mac` = NULL
WHERE `mac` = '';

UPDATE `spbx_devices`
SET `ipei` = NULL
WHERE `ipei` = '';

UPDATE `spbx_devices`
SET `serial_number` = NULL
WHERE `serial_number` = '';

UPDATE `spbx_devices`
SET `provision_file` = NULL
WHERE `provision_file` = '';

-- ------------------------------------------------------------
-- 4. Daten aus ps_endpoints nachziehen
-- ------------------------------------------------------------

UPDATE `spbx_devices` d
JOIN `ps_endpoints` p ON p.id = d.endpoint_id
SET
  d.extension = COALESCE(NULLIF(d.extension, ''), p.extension),
  d.display_name = COALESCE(NULLIF(d.display_name, ''), p.display_name),
  d.device_model = COALESCE(NULLIF(d.device_model, ''), p.device_model),
  d.active = COALESCE(d.active, p.active);

-- ------------------------------------------------------------
-- 5. Modellregeln normalisieren
-- ------------------------------------------------------------

-- Tischtelefone haben MAC, aber keine IPEI.
UPDATE `spbx_devices`
SET `ipei` = NULL,
    `provisioning_enabled` = 1,
    `provision_file` = CASE
      WHEN `device_model` = 'snomD815' THEN 'snomD815.php'
      WHEN `device_model` = 'snomD810' THEN 'snomD810.php'
      ELSE `provision_file`
    END
WHERE `device_model` IN ('snomD815','snomD810');

-- DECT-Handsets haben IPEI, aber keine MAC im spbx_devices Datensatz.
UPDATE `spbx_devices`
SET `mac` = NULL,
    `provision_file` = NULL,
    `provisioning_enabled` = 0
WHERE `device_model` = 'snom_dect_handset';

-- SIP-User haben weder MAC noch IPEI noch Provisioning.
UPDATE `spbx_devices`
SET `mac` = NULL,
    `ipei` = NULL,
    `provision_file` = NULL,
    `provisioning_enabled` = 0
WHERE `device_model` = 'sip_user';

-- ------------------------------------------------------------
-- 6. Fehlende spbx_devices aus ps_endpoints erzeugen
-- ------------------------------------------------------------

INSERT INTO `spbx_devices`
(`endpoint_id`, `device_model`, `extension`, `display_name`, `mac`, `ipei`, `serial_number`, `provisioning_enabled`, `provision_file`, `active`)
SELECT
  p.id,
  p.device_model,
  p.extension,
  p.display_name,
  NULL,
  NULL,
  NULL,
  CASE WHEN p.device_model IN ('snomD815','snomD810') THEN 1 ELSE 0 END,
  CASE
    WHEN p.device_model='snomD815' THEN 'snomD815.php'
    WHEN p.device_model='snomD810' THEN 'snomD810.php'
    ELSE NULL
  END,
  p.active
FROM `ps_endpoints` p
LEFT JOIN `spbx_devices` d ON d.endpoint_id=p.id
WHERE d.id IS NULL;

-- ------------------------------------------------------------
-- 7. Doppelte endpoint_id prüfen
-- ------------------------------------------------------------
-- Sollte leer sein. Falls nicht leer, bitte vor Unique-Key-Bereinigung prüfen.
SELECT endpoint_id, COUNT(*) AS cnt
FROM spbx_devices
GROUP BY endpoint_id
HAVING cnt > 1;

-- ------------------------------------------------------------
-- 8. Indizes setzen
-- ------------------------------------------------------------
-- Bei bereits vorhandenem Index meldet MariaDB ggf. Duplicate key name.
-- Das ist unkritisch.

ALTER TABLE `spbx_devices`
  ADD UNIQUE KEY IF NOT EXISTS `uniq_spbx_devices_endpoint_id` (`endpoint_id`);

ALTER TABLE `spbx_devices`
  ADD INDEX IF NOT EXISTS `idx_spbx_devices_extension` (`extension`);

ALTER TABLE `spbx_devices`
  ADD INDEX IF NOT EXISTS `idx_spbx_devices_device_model` (`device_model`);

ALTER TABLE `spbx_devices`
  ADD INDEX IF NOT EXISTS `idx_spbx_devices_mac` (`mac`);

ALTER TABLE `spbx_devices`
  ADD INDEX IF NOT EXISTS `idx_spbx_devices_ipei` (`ipei`);

-- ------------------------------------------------------------
-- 9. Optionaler Foreign Key bewusst NICHT automatisch setzen
-- ------------------------------------------------------------
-- Hintergrund:
-- In bestehenden Installationen können Altlasten vorhanden sein.
-- Sobald die folgende Prüfung keine Zeilen liefert, kann man später
-- optional einen Foreign Key setzen.
--
-- Prüfung:
-- SELECT d.endpoint_id
-- FROM spbx_devices d
-- LEFT JOIN ps_endpoints p ON p.id=d.endpoint_id
-- WHERE p.id IS NULL;
--
-- Optional später:
-- ALTER TABLE spbx_devices
--   ADD CONSTRAINT fk_spbx_devices_endpoint
--   FOREIGN KEY (endpoint_id) REFERENCES ps_endpoints(id)
--   ON DELETE CASCADE ON UPDATE CASCADE;

-- ------------------------------------------------------------
-- 10. Marker
-- ------------------------------------------------------------

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('spbx_devices_cleanup', '1.1.3', 'spbx_devices normalisiert: endpoint_id=Durchwahl, MAC/IPEI-Regeln, Indizes'),
('device_identity_model', 'endpoint_id_extension', 'Geräte referenzieren ps_endpoints.id; ps_endpoints.id ist die Durchwahl')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- ============================================================
-- Ende ServusPBX Medical 1.1.3
-- ============================================================
