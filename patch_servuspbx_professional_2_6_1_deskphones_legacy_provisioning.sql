-- ServusPBX Professional 2.6.1
-- Deskphones / Legacy Provisioning Basis

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
  `fkey42action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey43action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey44action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey45action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey46action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey47action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey48action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey49action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey50action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey51action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey52action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey53action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey54action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey55action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey56action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey57action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey58action` varchar(8) NOT NULL DEFAULT 'none',
  `fkey59action` varchar(8) NOT NULL DEFAULT 'none',
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
  `fkey42value` varchar(20) DEFAULT NULL,
  `fkey43value` varchar(20) DEFAULT NULL,
  `fkey44value` varchar(20) DEFAULT NULL,
  `fkey45value` varchar(20) DEFAULT NULL,
  `fkey46value` varchar(20) DEFAULT NULL,
  `fkey47value` varchar(20) DEFAULT NULL,
  `fkey48value` varchar(20) DEFAULT NULL,
  `fkey49value` varchar(20) DEFAULT NULL,
  `fkey50value` varchar(20) DEFAULT NULL,
  `fkey51value` varchar(20) DEFAULT NULL,
  `fkey52value` varchar(20) DEFAULT NULL,
  `fkey53value` varchar(20) DEFAULT NULL,
  `fkey54value` varchar(20) DEFAULT NULL,
  `fkey55value` varchar(20) DEFAULT NULL,
  `fkey56value` varchar(20) DEFAULT NULL,
  `fkey57value` varchar(20) DEFAULT NULL,
  `fkey58value` varchar(20) DEFAULT NULL,
  `fkey59value` varchar(20) DEFAULT NULL,
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
  `fkey42label` varchar(28) DEFAULT NULL,
  `fkey43label` varchar(28) DEFAULT NULL,
  `fkey44label` varchar(28) DEFAULT NULL,
  `fkey45label` varchar(28) DEFAULT NULL,
  `fkey46label` varchar(28) DEFAULT NULL,
  `fkey47label` varchar(28) DEFAULT NULL,
  `fkey48label` varchar(28) DEFAULT NULL,
  `fkey49label` varchar(28) DEFAULT NULL,
  `fkey50label` varchar(28) DEFAULT NULL,
  `fkey51label` varchar(28) DEFAULT NULL,
  `fkey52label` varchar(28) DEFAULT NULL,
  `fkey53label` varchar(28) DEFAULT NULL,
  `fkey54label` varchar(28) DEFAULT NULL,
  `fkey55label` varchar(28) DEFAULT NULL,
  `fkey56label` varchar(28) DEFAULT NULL,
  `fkey57label` varchar(28) DEFAULT NULL,
  `fkey58label` varchar(28) DEFAULT NULL,
  `fkey59label` varchar(28) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_deskphones_mac` (`mac`),
  KEY `idx_deskphones_sipuser` (`sipuser`),
  KEY `idx_deskphones_phone_type` (`phone_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Bestehende Tischtelefone aus spbx_devices in die alte flache Struktur übernehmen.

INSERT INTO `deskphones` (`mac`, `phone_type`, `sipuser`, `eth_pc`, `language`)
SELECT d.mac, d.device_model, d.endpoint_id, 'auto', 'Deutsch'
FROM `spbx_devices` d
WHERE d.device_model IN ('snomD810','snomD812','snomD815','snomD862','snomD865','snomD892','snomD895','GigasetP810','GigasetP82x','GigasetP85x')
  AND d.mac IS NOT NULL AND d.mac<>''
  AND NOT EXISTS (SELECT 1 FROM `deskphones` dp WHERE dp.sipuser=d.endpoint_id OR dp.mac=d.mac);

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=0
SET dp.`fkey0action`=k.key_type, dp.`fkey0value`=k.key_value, dp.`fkey0label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=1
SET dp.`fkey1action`=k.key_type, dp.`fkey1value`=k.key_value, dp.`fkey1label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=2
SET dp.`fkey2action`=k.key_type, dp.`fkey2value`=k.key_value, dp.`fkey2label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=3
SET dp.`fkey3action`=k.key_type, dp.`fkey3value`=k.key_value, dp.`fkey3label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=4
SET dp.`fkey4action`=k.key_type, dp.`fkey4value`=k.key_value, dp.`fkey4label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=5
SET dp.`fkey5action`=k.key_type, dp.`fkey5value`=k.key_value, dp.`fkey5label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=6
SET dp.`fkey6action`=k.key_type, dp.`fkey6value`=k.key_value, dp.`fkey6label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=7
SET dp.`fkey7action`=k.key_type, dp.`fkey7value`=k.key_value, dp.`fkey7label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=8
SET dp.`fkey8action`=k.key_type, dp.`fkey8value`=k.key_value, dp.`fkey8label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=9
SET dp.`fkey9action`=k.key_type, dp.`fkey9value`=k.key_value, dp.`fkey9label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=10
SET dp.`fkey10action`=k.key_type, dp.`fkey10value`=k.key_value, dp.`fkey10label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=11
SET dp.`fkey11action`=k.key_type, dp.`fkey11value`=k.key_value, dp.`fkey11label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=12
SET dp.`fkey12action`=k.key_type, dp.`fkey12value`=k.key_value, dp.`fkey12label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=13
SET dp.`fkey13action`=k.key_type, dp.`fkey13value`=k.key_value, dp.`fkey13label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=14
SET dp.`fkey14action`=k.key_type, dp.`fkey14value`=k.key_value, dp.`fkey14label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=15
SET dp.`fkey15action`=k.key_type, dp.`fkey15value`=k.key_value, dp.`fkey15label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=16
SET dp.`fkey16action`=k.key_type, dp.`fkey16value`=k.key_value, dp.`fkey16label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=17
SET dp.`fkey17action`=k.key_type, dp.`fkey17value`=k.key_value, dp.`fkey17label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=18
SET dp.`fkey18action`=k.key_type, dp.`fkey18value`=k.key_value, dp.`fkey18label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=19
SET dp.`fkey19action`=k.key_type, dp.`fkey19value`=k.key_value, dp.`fkey19label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=20
SET dp.`fkey20action`=k.key_type, dp.`fkey20value`=k.key_value, dp.`fkey20label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=21
SET dp.`fkey21action`=k.key_type, dp.`fkey21value`=k.key_value, dp.`fkey21label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=22
SET dp.`fkey22action`=k.key_type, dp.`fkey22value`=k.key_value, dp.`fkey22label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=23
SET dp.`fkey23action`=k.key_type, dp.`fkey23value`=k.key_value, dp.`fkey23label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=24
SET dp.`fkey24action`=k.key_type, dp.`fkey24value`=k.key_value, dp.`fkey24label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=25
SET dp.`fkey25action`=k.key_type, dp.`fkey25value`=k.key_value, dp.`fkey25label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=26
SET dp.`fkey26action`=k.key_type, dp.`fkey26value`=k.key_value, dp.`fkey26label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=27
SET dp.`fkey27action`=k.key_type, dp.`fkey27value`=k.key_value, dp.`fkey27label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=28
SET dp.`fkey28action`=k.key_type, dp.`fkey28value`=k.key_value, dp.`fkey28label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=29
SET dp.`fkey29action`=k.key_type, dp.`fkey29value`=k.key_value, dp.`fkey29label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=30
SET dp.`fkey30action`=k.key_type, dp.`fkey30value`=k.key_value, dp.`fkey30label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=31
SET dp.`fkey31action`=k.key_type, dp.`fkey31value`=k.key_value, dp.`fkey31label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=32
SET dp.`fkey32action`=k.key_type, dp.`fkey32value`=k.key_value, dp.`fkey32label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=33
SET dp.`fkey33action`=k.key_type, dp.`fkey33value`=k.key_value, dp.`fkey33label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=34
SET dp.`fkey34action`=k.key_type, dp.`fkey34value`=k.key_value, dp.`fkey34label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=35
SET dp.`fkey35action`=k.key_type, dp.`fkey35value`=k.key_value, dp.`fkey35label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=36
SET dp.`fkey36action`=k.key_type, dp.`fkey36value`=k.key_value, dp.`fkey36label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=37
SET dp.`fkey37action`=k.key_type, dp.`fkey37value`=k.key_value, dp.`fkey37label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=38
SET dp.`fkey38action`=k.key_type, dp.`fkey38value`=k.key_value, dp.`fkey38label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=39
SET dp.`fkey39action`=k.key_type, dp.`fkey39value`=k.key_value, dp.`fkey39label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=40
SET dp.`fkey40action`=k.key_type, dp.`fkey40value`=k.key_value, dp.`fkey40label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=41
SET dp.`fkey41action`=k.key_type, dp.`fkey41value`=k.key_value, dp.`fkey41label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=42
SET dp.`fkey42action`=k.key_type, dp.`fkey42value`=k.key_value, dp.`fkey42label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=43
SET dp.`fkey43action`=k.key_type, dp.`fkey43value`=k.key_value, dp.`fkey43label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=44
SET dp.`fkey44action`=k.key_type, dp.`fkey44value`=k.key_value, dp.`fkey44label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=45
SET dp.`fkey45action`=k.key_type, dp.`fkey45value`=k.key_value, dp.`fkey45label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=46
SET dp.`fkey46action`=k.key_type, dp.`fkey46value`=k.key_value, dp.`fkey46label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=47
SET dp.`fkey47action`=k.key_type, dp.`fkey47value`=k.key_value, dp.`fkey47label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=48
SET dp.`fkey48action`=k.key_type, dp.`fkey48value`=k.key_value, dp.`fkey48label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=49
SET dp.`fkey49action`=k.key_type, dp.`fkey49value`=k.key_value, dp.`fkey49label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=50
SET dp.`fkey50action`=k.key_type, dp.`fkey50value`=k.key_value, dp.`fkey50label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=51
SET dp.`fkey51action`=k.key_type, dp.`fkey51value`=k.key_value, dp.`fkey51label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=52
SET dp.`fkey52action`=k.key_type, dp.`fkey52value`=k.key_value, dp.`fkey52label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=53
SET dp.`fkey53action`=k.key_type, dp.`fkey53value`=k.key_value, dp.`fkey53label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=54
SET dp.`fkey54action`=k.key_type, dp.`fkey54value`=k.key_value, dp.`fkey54label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=55
SET dp.`fkey55action`=k.key_type, dp.`fkey55value`=k.key_value, dp.`fkey55label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=56
SET dp.`fkey56action`=k.key_type, dp.`fkey56value`=k.key_value, dp.`fkey56label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=57
SET dp.`fkey57action`=k.key_type, dp.`fkey57value`=k.key_value, dp.`fkey57label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=58
SET dp.`fkey58action`=k.key_type, dp.`fkey58value`=k.key_value, dp.`fkey58label`=k.key_label;

UPDATE `deskphones` dp
JOIN `spbx_devices` d ON d.endpoint_id=dp.sipuser
JOIN `spbx_device_keys` k ON k.device_id=d.id AND k.fkey_idx=59
SET dp.`fkey59action`=k.key_type, dp.`fkey59value`=k.key_value, dp.`fkey59label`=k.key_label;


ALTER TABLE `spbx_device_types` MODIFY `device_model` enum('snomD810','snomD812','snomD815','snomD862','snomD865','snomD892','snomD895','GigasetP810','GigasetP82x','GigasetP85x','snomM400','snomM900','snom_dect_handset','sip_user') NOT NULL;

INSERT INTO `spbx_device_types` (`device_model`, `display_name`, `vendor`, `provision_file`, `key_count`, `page_count`, `physical_keys`, `supports_blf`, `supports_provisioning`) VALUES
('snomD810','snom D810','snom','snomD810.php',16,4,4,1,1),
('snomD812','snom D812','snom','snomD812.php',32,4,8,1,1),
('snomD815','snom D815','snom','snomD815.php',40,4,10,1,1),
('snomD862','snom D862','snom','snomD862.php',32,4,8,1,1),
('snomD865','snom D865','snom','snomD865.php',32,4,8,1,1),
('snomD892','snom D892M','snom','snomD892.php',32,4,8,1,1),
('snomD895','snom D895M','snom','snomD895.php',56,4,14,1,1),
('GigasetP810','Gigaset P810','Gigaset','GigasetP810.php',16,4,4,1,1),
('GigasetP82x','Gigaset P82x','Gigaset','GigasetP82x.php',32,4,8,1,1),
('GigasetP85x','Gigaset P85x','Gigaset','GigasetP85x.php',40,4,10,1,1),
('snomM400','snom M400','snom','snomM400.php',0,0,0,0,1),
('snomM900','snom M900','snom','snomM900.php',0,0,0,0,1),
('snom_dect_handset','SNOM DECT Handset','snom',NULL,0,0,0,0,0),
('sip_user','SIP User','generic',NULL,0,0,0,0,0)
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name), vendor=VALUES(vendor), provision_file=VALUES(provision_file), key_count=VALUES(key_count), page_count=VALUES(page_count), physical_keys=VALUES(physical_keys), supports_blf=VALUES(supports_blf), supports_provisioning=VALUES(supports_provisioning);
