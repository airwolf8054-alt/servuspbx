-- ============================================================
-- ServusPBX Medical 1.2.36
-- BLF Sync Hook Fix + Snom Reboot Button
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('callerid_blf_sync_hook', 'after_commit', 'BLF-Sync läuft nach erfolgreichem Speichern unabhängig von Voicemail'),
('snom_reboot_button', '1.2.36', 'Nebenstellenliste enthält Reboot-Button für snom D810/D815')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.36
