-- ============================================================
-- ServusPBX Medical 1.2.14
-- SIP-Trunk Status + Domain Anzeige
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sip_trunk_status_source', 'asterisk_pjsip_show_registrations', 'SIP-Trunk Status wird aus pjsip show registrations gelesen'),
('sip_trunk_list_domain_column', '1', 'Übersicht zeigt Domain statt Server URI')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Keine Datenbankstrukturänderungen erforderlich.
-- Ende ServusPBX Medical 1.2.14
