-- ============================================================
-- ServusPBX Medical 1.2.35
-- BLF Sync Notify All Phones
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('callerid_blf_sync_notify_mode', 'all_provisioned_phones', 'Nach CallerID/BLF-Änderung werden alle aktiven provisionierten Telefone per snom-check-cfg benachrichtigt'),
('callerid_blf_sync', '1.2.35', 'BLF-Labels aktualisieren + alle provisionierten Telefone neu provisionieren')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.35
