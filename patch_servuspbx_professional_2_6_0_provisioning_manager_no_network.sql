-- ServusPBX Professional 2.6.0 - Provisioning Manager / No Network

CREATE TABLE IF NOT EXISTS spbx_provisioning_jobs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  endpoint_id VARCHAR(80) NOT NULL,
  job_type VARCHAR(40) NOT NULL,
  status ENUM('pending','running','done','failed') NOT NULL DEFAULT 'pending',
  payload TEXT NULL,
  result TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_endpoint_status (endpoint_id,status),
  KEY idx_job_type (job_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS spbx_provisioning_options (
  id INT AUTO_INCREMENT PRIMARY KEY,
  option_key VARCHAR(80) NOT NULL UNIQUE,
  option_value VARCHAR(255) NOT NULL DEFAULT '',
  description VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO spbx_provisioning_options (option_key, option_value, description) VALUES
('send_network', '0', 'Netzwerkparameter an Telefone senden. Standard: aus, DHCP übernimmt Netzwerk.'),
('send_firmware', '1', 'Firmware-Tag senden.'),
('send_identity', '1', 'SIP-Identität senden.'),
('send_keys', '1', 'Funktionstasten senden.'),
('send_phonebook', '1', 'Telefonbuch senden.'),
('force_reboot', '0', 'Neustart nach Provisionierung erzwingen. Standard: aus.')
ON DUPLICATE KEY UPDATE description=VALUES(description);

INSERT INTO spbx_settings (setting_key, setting_value, description) VALUES
('provisioning_manager_no_network', '2.6.0', 'Provisioning Manager vorbereitet; Netzwerkparameter standardmäßig deaktiviert')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), description=VALUES(description);
