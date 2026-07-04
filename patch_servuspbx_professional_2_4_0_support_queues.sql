-- ServusPBX Professional 2.4.0 - Support Queues

CREATE TABLE IF NOT EXISTS spbx_queues (
  id INT AUTO_INCREMENT PRIMARY KEY,
  queue_number VARCHAR(20) NOT NULL,
  queue_name VARCHAR(120) NOT NULL,
  strategy VARCHAR(40) NOT NULL DEFAULT 'ringall',
  timeout_seconds INT NOT NULL DEFAULT 20,
  max_wait_seconds INT NOT NULL DEFAULT 300,
  musicclass VARCHAR(80) NOT NULL DEFAULT 'default',
  emergency_announcement_enabled TINYINT(1) NOT NULL DEFAULT 0,
  emergency_announcement_file VARCHAR(255) DEFAULT NULL,
  emergency_tts_text TEXT NULL,
  timeout_destination_type VARCHAR(40) NOT NULL DEFAULT 'hangup',
  timeout_destination_context VARCHAR(80) NOT NULL DEFAULT '',
  timeout_destination_exten VARCHAR(80) NOT NULL DEFAULT '',
  callback_login_enabled TINYINT(1) NOT NULL DEFAULT 1,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_queue_number (queue_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS spbx_queue_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  queue_id INT NOT NULL,
  endpoint_id VARCHAR(80) NOT NULL,
  member_type ENUM('fixed','optional') NOT NULL DEFAULT 'fixed',
  penalty INT NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uniq_queue_member (queue_id, endpoint_id),
  KEY idx_endpoint (endpoint_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS spbx_queue_pause_reasons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reason_name VARCHAR(80) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  UNIQUE KEY uniq_reason (reason_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS spbx_queue_agent_status (
  id INT AUTO_INCREMENT PRIMARY KEY,
  endpoint_id VARCHAR(80) NOT NULL,
  queue_id INT NOT NULL,
  logged_in TINYINT(1) NOT NULL DEFAULT 0,
  paused TINYINT(1) NOT NULL DEFAULT 0,
  pause_reason_id INT NULL,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_agent_queue (endpoint_id, queue_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO spbx_queue_pause_reasons (reason_name, sort_order, active) VALUES
('Arbeitspause', 10, 1),
('Meeting', 20, 1),
('Mittagspause', 30, 1);

INSERT INTO spbx_settings (setting_key, setting_value, description) VALUES
('support_queues', '2.4.0', 'Support Queues mit Notansage, berechtigten Agenten und Pause')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), description=VALUES(description);
