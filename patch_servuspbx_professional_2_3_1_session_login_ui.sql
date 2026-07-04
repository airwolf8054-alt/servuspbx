-- ServusPBX Professional 2.3.1 - Session + Login UI

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('session_login_ui', '2.3.1', 'Session Timeout 12 Stunden und Professional Login UI')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
