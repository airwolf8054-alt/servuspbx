<?php
require_once __DIR__ . '/db.php';

function spbx_provisioning_manager_install_schema()
{
    $db = spbx_db();

    $db->query("CREATE TABLE IF NOT EXISTS spbx_provisioning_jobs (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $db->query("CREATE TABLE IF NOT EXISTS spbx_provisioning_options (
        id INT AUTO_INCREMENT PRIMARY KEY,
        option_key VARCHAR(80) NOT NULL UNIQUE,
        option_value VARCHAR(255) NOT NULL DEFAULT '',
        description VARCHAR(255) NOT NULL DEFAULT ''
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $defaults = [
        ['send_network', '0', 'Netzwerkparameter an Telefone senden. Standard: aus, DHCP übernimmt Netzwerk.'],
        ['send_firmware', '1', 'Firmware-Tag senden.'],
        ['send_identity', '1', 'SIP-Identität senden.'],
        ['send_keys', '1', 'Funktionstasten senden.'],
        ['send_phonebook', '1', 'Telefonbuch senden.'],
        ['force_reboot', '0', 'Neustart nach Provisionierung erzwingen. Standard: aus.'],
    ];

    foreach ($defaults as $d) {
        $stmt = $db->prepare("INSERT IGNORE INTO spbx_provisioning_options (option_key, option_value, description) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('sss', $d[0], $d[1], $d[2]);
            $stmt->execute();
        }
    }
}

function spbx_provisioning_option($key, $default = '')
{
    spbx_provisioning_manager_install_schema();
    $db = spbx_db();

    $stmt = $db->prepare("SELECT option_value FROM spbx_provisioning_options WHERE option_key=? LIMIT 1");
    if (!$stmt) return $default;

    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return $row ? (string)$row['option_value'] : $default;
}
?>