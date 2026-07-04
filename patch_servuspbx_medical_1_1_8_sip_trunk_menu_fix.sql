-- ============================================================
-- ServusPBX Medical 1.1.8
-- SIP-Trunk Menü Fix
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sip_trunk_menu_target', 'pages/sip_trunks.php', 'Menüpunkt SIP-Trunks zeigt auf die neue Übersichtsseite'),
('sip_trunk_old_a1_redirect', 'pages/trunk_a1.php', 'Alte A1-Seite leitet auf sip_trunks.php weiter')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.1.8
