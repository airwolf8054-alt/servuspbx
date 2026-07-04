CREATE TABLE IF NOT EXISTS `deskphones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mac` varchar(15) DEFAULT NULL,
  `phone_type` varchar(15) DEFAULT NULL,
  `sipuser` varchar(15) DEFAULT NULL,
  `eth_pc` varchar(4) NOT NULL DEFAULT 'off',
  `language` varchar(10) NOT NULL DEFAULT 'Deutsch',
  `fkey0action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey1action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey2action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey3action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey4action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey5action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey6action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey7action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey8action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey9action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey10action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey11action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey12action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey13action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey14action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey15action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey16action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey17action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey18action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey19action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey20action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey21action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey22action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey23action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey24action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey25action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey26action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey27action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey28action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey29action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey30action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey31action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey32action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey33action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey34action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey35action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey36action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey37action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey38action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey39action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey40action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey41action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey0value` varchar(20) DEFAULT NULL,
  `fkey1value` varchar(20) DEFAULT NULL,
  `fkey2value` varchar(20) DEFAULT NULL,
  `fkey3value` varchar(20) DEFAULT NULL,
  `fkey4value` varchar(20) DEFAULT NULL,
  `fkey5value` varchar(20) DEFAULT NULL,
  `fkey6value` varchar(20) DEFAULT NULL,
  `fkey7value` varchar(20) DEFAULT NULL,
  `fkey8value` varchar(20) DEFAULT NULL,
  `fkey9value` varchar(20) DEFAULT NULL,
  `fkey10value` varchar(20) DEFAULT NULL,
  `fkey11value` varchar(20) DEFAULT NULL,
  `fkey12value` varchar(20) DEFAULT NULL,
  `fkey13value` varchar(20) DEFAULT NULL,
  `fkey14value` varchar(20) DEFAULT NULL,
  `fkey15value` varchar(20) DEFAULT NULL,
  `fkey16value` varchar(20) DEFAULT NULL,
  `fkey17value` varchar(20) DEFAULT NULL,
  `fkey18value` varchar(20) DEFAULT NULL,
  `fkey19value` varchar(20) DEFAULT NULL,
  `fkey20value` varchar(20) DEFAULT NULL,
  `fkey21value` varchar(20) DEFAULT NULL,
  `fkey22value` varchar(20) DEFAULT NULL,
  `fkey23value` varchar(20) DEFAULT NULL,
  `fkey24value` varchar(20) DEFAULT NULL,
  `fkey25value` varchar(20) DEFAULT NULL,
  `fkey26value` varchar(20) DEFAULT NULL,
  `fkey27value` varchar(20) DEFAULT NULL,
  `fkey28value` varchar(20) DEFAULT NULL,
  `fkey29value` varchar(20) DEFAULT NULL,
  `fkey30value` varchar(20) DEFAULT NULL,
  `fkey31value` varchar(20) DEFAULT NULL,
  `fkey32value` varchar(20) DEFAULT NULL,
  `fkey33value` varchar(20) DEFAULT NULL,
  `fkey34value` varchar(20) DEFAULT NULL,
  `fkey35value` varchar(20) DEFAULT NULL,
  `fkey36value` varchar(20) DEFAULT NULL,
  `fkey37value` varchar(20) DEFAULT NULL,
  `fkey38value` varchar(20) DEFAULT NULL,
  `fkey39value` varchar(20) DEFAULT NULL,
  `fkey40value` varchar(20) DEFAULT NULL,
  `fkey41value` varchar(20) DEFAULT NULL,
  `fkey0label` varchar(28) DEFAULT NULL,
  `fkey1label` varchar(28) DEFAULT NULL,
  `fkey2label` varchar(28) DEFAULT NULL,
  `fkey3label` varchar(28) DEFAULT NULL,
  `fkey4label` varchar(28) DEFAULT NULL,
  `fkey5label` varchar(28) DEFAULT NULL,
  `fkey6label` varchar(28) DEFAULT NULL,
  `fkey7label` varchar(28) DEFAULT NULL,
  `fkey8label` varchar(28) DEFAULT NULL,
  `fkey9label` varchar(28) DEFAULT NULL,
  `fkey10label` varchar(28) DEFAULT NULL,
  `fkey11label` varchar(28) DEFAULT NULL,
  `fkey12label` varchar(28) DEFAULT NULL,
  `fkey13label` varchar(28) DEFAULT NULL,
  `fkey14label` varchar(28) DEFAULT NULL,
  `fkey15label` varchar(28) DEFAULT NULL,
  `fkey16label` varchar(28) DEFAULT NULL,
  `fkey17label` varchar(28) DEFAULT NULL,
  `fkey18label` varchar(28) DEFAULT NULL,
  `fkey19label` varchar(28) DEFAULT NULL,
  `fkey20label` varchar(28) DEFAULT NULL,
  `fkey21label` varchar(28) DEFAULT NULL,
  `fkey22label` varchar(28) DEFAULT NULL,
  `fkey23label` varchar(28) DEFAULT NULL,
  `fkey24label` varchar(28) DEFAULT NULL,
  `fkey25label` varchar(28) DEFAULT NULL,
  `fkey26label` varchar(28) DEFAULT NULL,
  `fkey27label` varchar(28) DEFAULT NULL,
  `fkey28label` varchar(28) DEFAULT NULL,
  `fkey29label` varchar(28) DEFAULT NULL,
  `fkey30label` varchar(28) DEFAULT NULL,
  `fkey31label` varchar(28) DEFAULT NULL,
  `fkey32label` varchar(28) DEFAULT NULL,
  `fkey33label` varchar(28) DEFAULT NULL,
  `fkey34label` varchar(28) DEFAULT NULL,
  `fkey35label` varchar(28) DEFAULT NULL,
  `fkey36label` varchar(28) DEFAULT NULL,
  `fkey37label` varchar(28) DEFAULT NULL,
  `fkey38label` varchar(28) DEFAULT NULL,
  `fkey39label` varchar(28) DEFAULT NULL,
  `fkey40label` varchar(28) DEFAULT NULL,
  `fkey41label` varchar(28) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_deskphones_mac` (`mac`),
  KEY `idx_deskphones_sipuser` (`sipuser`),
  KEY `idx_deskphones_phone_type` (`phone_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


INSERT IGNORE INTO `deskphones` (`mac`, `phone_type`, `sipuser`, `eth_pc`, `language`)
SELECT d.mac, p.device_model, p.id, 'off', COALESCE(p.language, 'Deutsch')
FROM spbx_devices d
JOIN ps_endpoints p ON p.id=d.endpoint_id
WHERE p.device_model IN ('snomD815','snomD810','snomD812','snomD862','snomD865','snomD892','snomD895','GigasetP810','GigasetP82x','GigasetP85x')
  AND d.mac IS NOT NULL AND d.mac<>'';

-- Bestehende spbx_device_keys in die flache Legacy-Struktur übernehmen.
-- Hinweis: dynamisches SQL vermeidet Abbrüche, wenn spbx_device_keys auf Altinstallationen fehlt.
DROP PROCEDURE IF EXISTS spbx_migrate_legacy_deskphone_keys;
DELIMITER $$
CREATE PROCEDURE spbx_migrate_legacy_deskphone_keys()
BEGIN
  IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'spbx_device_keys') THEN
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=0 SET dp.fkey0action=k.key_type, dp.fkey0value=k.key_value, dp.fkey0label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=1 SET dp.fkey1action=k.key_type, dp.fkey1value=k.key_value, dp.fkey1label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=2 SET dp.fkey2action=k.key_type, dp.fkey2value=k.key_value, dp.fkey2label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=3 SET dp.fkey3action=k.key_type, dp.fkey3value=k.key_value, dp.fkey3label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=4 SET dp.fkey4action=k.key_type, dp.fkey4value=k.key_value, dp.fkey4label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=5 SET dp.fkey5action=k.key_type, dp.fkey5value=k.key_value, dp.fkey5label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=6 SET dp.fkey6action=k.key_type, dp.fkey6value=k.key_value, dp.fkey6label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=7 SET dp.fkey7action=k.key_type, dp.fkey7value=k.key_value, dp.fkey7label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=8 SET dp.fkey8action=k.key_type, dp.fkey8value=k.key_value, dp.fkey8label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=9 SET dp.fkey9action=k.key_type, dp.fkey9value=k.key_value, dp.fkey9label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=10 SET dp.fkey10action=k.key_type, dp.fkey10value=k.key_value, dp.fkey10label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=11 SET dp.fkey11action=k.key_type, dp.fkey11value=k.key_value, dp.fkey11label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=12 SET dp.fkey12action=k.key_type, dp.fkey12value=k.key_value, dp.fkey12label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=13 SET dp.fkey13action=k.key_type, dp.fkey13value=k.key_value, dp.fkey13label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=14 SET dp.fkey14action=k.key_type, dp.fkey14value=k.key_value, dp.fkey14label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=15 SET dp.fkey15action=k.key_type, dp.fkey15value=k.key_value, dp.fkey15label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=16 SET dp.fkey16action=k.key_type, dp.fkey16value=k.key_value, dp.fkey16label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=17 SET dp.fkey17action=k.key_type, dp.fkey17value=k.key_value, dp.fkey17label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=18 SET dp.fkey18action=k.key_type, dp.fkey18value=k.key_value, dp.fkey18label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=19 SET dp.fkey19action=k.key_type, dp.fkey19value=k.key_value, dp.fkey19label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=20 SET dp.fkey20action=k.key_type, dp.fkey20value=k.key_value, dp.fkey20label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=21 SET dp.fkey21action=k.key_type, dp.fkey21value=k.key_value, dp.fkey21label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=22 SET dp.fkey22action=k.key_type, dp.fkey22value=k.key_value, dp.fkey22label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=23 SET dp.fkey23action=k.key_type, dp.fkey23value=k.key_value, dp.fkey23label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=24 SET dp.fkey24action=k.key_type, dp.fkey24value=k.key_value, dp.fkey24label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=25 SET dp.fkey25action=k.key_type, dp.fkey25value=k.key_value, dp.fkey25label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=26 SET dp.fkey26action=k.key_type, dp.fkey26value=k.key_value, dp.fkey26label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=27 SET dp.fkey27action=k.key_type, dp.fkey27value=k.key_value, dp.fkey27label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=28 SET dp.fkey28action=k.key_type, dp.fkey28value=k.key_value, dp.fkey28label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=29 SET dp.fkey29action=k.key_type, dp.fkey29value=k.key_value, dp.fkey29label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=30 SET dp.fkey30action=k.key_type, dp.fkey30value=k.key_value, dp.fkey30label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=31 SET dp.fkey31action=k.key_type, dp.fkey31value=k.key_value, dp.fkey31label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=32 SET dp.fkey32action=k.key_type, dp.fkey32value=k.key_value, dp.fkey32label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=33 SET dp.fkey33action=k.key_type, dp.fkey33value=k.key_value, dp.fkey33label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=34 SET dp.fkey34action=k.key_type, dp.fkey34value=k.key_value, dp.fkey34label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=35 SET dp.fkey35action=k.key_type, dp.fkey35value=k.key_value, dp.fkey35label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=36 SET dp.fkey36action=k.key_type, dp.fkey36value=k.key_value, dp.fkey36label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=37 SET dp.fkey37action=k.key_type, dp.fkey37value=k.key_value, dp.fkey37label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=38 SET dp.fkey38action=k.key_type, dp.fkey38value=k.key_value, dp.fkey38label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=39 SET dp.fkey39action=k.key_type, dp.fkey39value=k.key_value, dp.fkey39label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=40 SET dp.fkey40action=k.key_type, dp.fkey40value=k.key_value, dp.fkey40label=k.key_label;
    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx=41 SET dp.fkey41action=k.key_type, dp.fkey41value=k.key_value, dp.fkey41label=k.key_label;
  END IF;
END$$
DELIMITER ;
CALL spbx_migrate_legacy_deskphone_keys();
DROP PROCEDURE IF EXISTS spbx_migrate_legacy_deskphone_keys;
