-- ============================================================
-- ServusPBX Medical 1.2.15
-- SIP-Trunk Status via sudo asterisk
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sip_trunk_status_command', 'sudo /usr/sbin/asterisk -rx pjsip show registrations', 'SIP-Trunk Status wird per sudo Asterisk CLI gelesen')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Voraussetzung:
-- www-data ALL=(ALL) NOPASSWD: /usr/sbin/asterisk
-- Ende ServusPBX Medical 1.2.15
