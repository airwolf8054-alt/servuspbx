-- ============================================================
-- ServusPBX Medical 1.6.1
-- Rufgruppen
-- ============================================================

CREATE TABLE IF NOT EXISTS `spbx_ring_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_number` varchar(20) NOT NULL,
  `name` varchar(120) NOT NULL,
  `strategy` enum('ringall','hunt','random') NOT NULL DEFAULT 'ringall',
  `ring_time` int(11) NOT NULL DEFAULT 25,
  `timeout_action` enum('hangup','extension') NOT NULL DEFAULT 'hangup',
  `timeout_target` varchar(40) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_group_number` (`group_number`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `spbx_ring_group_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `extension` varchar(20) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_group_member` (`group_id`,`extension`),
  KEY `idx_group` (`group_id`),
  KEY `idx_extension` (`extension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('ring_groups', '1.6.1', 'Rufgruppen Modul mit ringall/hunt/random und Dialplan Generator')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
