-- ============================================================
-- ServusPBX Medical 1.2.25 - Eingehender Callflow
-- ============================================================
CREATE TABLE IF NOT EXISTS `spbx_inbound_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `route_name` varchar(120) NOT NULL,
  `trunk_id` int(11) DEFAULT NULL,
  `trunk_context` varchar(120) NOT NULL,
  `did` varchar(80) NOT NULL,
  `holiday_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `manual_announcement_1_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `manual_announcement_1_file` varchar(255) DEFAULT NULL,
  `manual_announcement_2_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `manual_announcement_2_file` varchar(255) DEFAULT NULL,
  `closed_audio` varchar(255) NOT NULL DEFAULT '/var/www/html/sounds/closed.mp3',
  `closed_action` enum('hangup','voicemail') NOT NULL DEFAULT 'hangup',
  `main_mailbox` varchar(40) NOT NULL DEFAULT '0',
  `main_mailbox_context` varchar(80) NOT NULL DEFAULT 'internal',
  `open_destination_context` varchar(80) NOT NULL DEFAULT 'internal',
  `open_destination_exten` varchar(80) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`), KEY `idx_trunk_context` (`trunk_context`), KEY `idx_did` (`did`), KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS `spbx_inbound_time_windows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `route_id` int(11) NOT NULL,
  `weekday` enum('mon','tue','wed','thu','fri','sat','sun') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`), KEY `idx_route_weekday` (`route_id`,`weekday`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('inbound_callflow_version', '1.2.25', 'Eingehende Routen mit Zeitfenstern, Feiertagen und Closed-Aktion'),
('main_voicemail_mailbox', '0', 'Globale Hauptbox Mailbox'),
('main_voicemail_context', 'internal', 'Globale Hauptbox Context'),
('holiday_astdb_family', 'bankholiday', 'Globale Feiertage in Asterisk AstDB')
ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`), `description`=VALUES(`description`);
-- Ende ServusPBX Medical 1.2.25
