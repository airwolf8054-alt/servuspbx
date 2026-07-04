-- ============================================================
-- ServusPBX Medical 1.2.41
-- Ausgehender A1 Callflow
-- ============================================================
-- Incoming bleibt unverändert: context incoming.
-- Ausgehend: outgoing_<rufnummer>, z.B. outgoing_43312423826.
-- A1: CLIP no Screening unterstützt Hauptnummer + Nebenstelle.
-- ============================================================

CREATE TABLE IF NOT EXISTS `spbx_outbound_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `route_name` varchar(120) NOT NULL,
  `provider` varchar(40) NOT NULL DEFAULT 'A1',
  `main_number` varchar(40) NOT NULL,
  `trunk_endpoint` varchar(120) NOT NULL,
  `outgoing_context` varchar(120) NOT NULL,
  `clip_no_screening` tinyint(1) NOT NULL DEFAULT 1,
  `callerid_mode` enum('main','extension') NOT NULL DEFAULT 'extension',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_out_ctx` (`outgoing_context`),
  KEY `idx_endpoint` (`trunk_endpoint`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('outbound_callflow_version', '1.2.41', 'Ausgehender A1 Callflow mit outgoing_<rufnummer> Contexts'),
('outbound_default_provider', 'A1', 'Standard Provider für ausgehende Routen'),
('outbound_clip_no_screening_default', '1', 'CLIP no Screening standardmäßig aktiv')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Nach Import:
-- /pages/outbound_routes.php öffnen und "Dialplan neu aufbauen" klicken.
-- Danach prüfen:
-- SELECT * FROM extensions WHERE context LIKE 'outgoing_%' ORDER BY context, exten, CAST(priority AS UNSIGNED);
-- asterisk -rx "dialplan show outgoing_43312423826"
