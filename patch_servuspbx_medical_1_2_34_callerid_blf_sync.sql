-- ============================================================
-- ServusPBX Medical 1.2.34
-- CallerID -> BLF Label Sync
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('callerid_blf_sync', '1.2.34', 'Bei CallerID-Änderung werden BLF-Tastenlabels aktualisiert und betroffene Snom-Telefone reprovisioniert')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Optionaler einmaliger Sync nach Installation:
-- php /var/www/html/scripts/sync_blf_labels.php
--
-- Ende ServusPBX Medical 1.2.34
