CREATE TABLE IF NOT EXISTS `spbx_ivrs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ivr_number` varchar(20) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `prompt_file` varchar(255) DEFAULT NULL,
  `prompt_tts_text` text DEFAULT NULL,
  `timeout_seconds` int NOT NULL DEFAULT 10,
  `max_attempts` int NOT NULL DEFAULT 3,
  `timeout_target_type` varchar(40) NOT NULL DEFAULT 'hangup',
  `timeout_target_context` varchar(80) NOT NULL DEFAULT '',
  `timeout_target_exten` varchar(80) NOT NULL DEFAULT '',
  `invalid_target_type` varchar(40) NOT NULL DEFAULT 'repeat',
  `invalid_target_context` varchar(80) NOT NULL DEFAULT '',
  `invalid_target_exten` varchar(80) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_ivr_number` (`ivr_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `spbx_ivr_options` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ivr_id` int NOT NULL,
  `digit` varchar(2) NOT NULL,
  `target_type` varchar(40) NOT NULL DEFAULT 'none',
  `target_context` varchar(80) NOT NULL DEFAULT '',
  `target_exten` varchar(80) NOT NULL DEFAULT '',
  `sort_order` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_ivr_digit` (`ivr_id`,`digit`),
  KEY `idx_ivr_id` (`ivr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
