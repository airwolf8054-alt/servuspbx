-- ServusPBX Professional 2.4.8 - Asterisk Realtime Queues

CREATE TABLE IF NOT EXISTS queues (
  name VARCHAR(128) NOT NULL PRIMARY KEY,
  musiconhold VARCHAR(128) DEFAULT 'default',
  announce VARCHAR(128) DEFAULT NULL,
  context VARCHAR(128) DEFAULT NULL,
  timeout INT DEFAULT 20,
  ringinuse VARCHAR(8) DEFAULT 'no',
  setinterfacevar VARCHAR(8) DEFAULT 'yes',
  setqueuevar VARCHAR(8) DEFAULT 'yes',
  setqueueentryvar VARCHAR(8) DEFAULT 'yes',
  monitor_format VARCHAR(8) DEFAULT NULL,
  membermacro VARCHAR(512) DEFAULT NULL,
  membergosub VARCHAR(512) DEFAULT NULL,
  queue_youarenext VARCHAR(128) DEFAULT NULL,
  queue_thereare VARCHAR(128) DEFAULT NULL,
  queue_callswaiting VARCHAR(128) DEFAULT NULL,
  queue_quantity1 VARCHAR(128) DEFAULT NULL,
  queue_quantity2 VARCHAR(128) DEFAULT NULL,
  queue_holdtime VARCHAR(128) DEFAULT NULL,
  queue_minutes VARCHAR(128) DEFAULT NULL,
  queue_minute VARCHAR(128) DEFAULT NULL,
  queue_seconds VARCHAR(128) DEFAULT NULL,
  queue_thankyou VARCHAR(128) DEFAULT NULL,
  queue_callerannounce VARCHAR(128) DEFAULT NULL,
  queue_reporthold VARCHAR(128) DEFAULT NULL,
  announce_frequency INT DEFAULT 0,
  announce_to_first_user VARCHAR(8) DEFAULT 'no',
  min_announce_frequency INT DEFAULT 0,
  announce_round_seconds INT DEFAULT 0,
  announce_holdtime VARCHAR(16) DEFAULT 'no',
  retry INT DEFAULT 5,
  wrapuptime INT DEFAULT 0,
  maxlen INT DEFAULT 0,
  servicelevel INT DEFAULT 60,
  strategy VARCHAR(32) DEFAULT 'ringall',
  joinempty VARCHAR(64) DEFAULT 'yes',
  leavewhenempty VARCHAR(64) DEFAULT 'no',
  eventmemberstatus VARCHAR(8) DEFAULT 'yes',
  eventwhencalled VARCHAR(8) DEFAULT 'yes',
  reportholdtime VARCHAR(8) DEFAULT 'no',
  memberdelay INT DEFAULT 0,
  weight INT DEFAULT 0,
  timeoutrestart VARCHAR(8) DEFAULT 'no',
  defaultrule VARCHAR(128) DEFAULT NULL,
  timeoutpriority VARCHAR(32) DEFAULT 'app'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS queue_members (
  uniqueid INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  queue_name VARCHAR(128) NOT NULL,
  interface VARCHAR(128) NOT NULL,
  membername VARCHAR(128) DEFAULT NULL,
  state_interface VARCHAR(128) DEFAULT NULL,
  penalty INT DEFAULT 0,
  paused INT DEFAULT 0,
  UNIQUE KEY uniq_queue_interface (queue_name, interface),
  KEY idx_queue_name (queue_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM queue_members WHERE queue_name LIKE 'spbxq_%';
DELETE FROM queues WHERE name LIKE 'spbxq_%';

INSERT INTO queues
(name, musiconhold, timeout, ringinuse, setinterfacevar, setqueuevar, setqueueentryvar, retry, wrapuptime, maxlen, servicelevel, strategy, joinempty, leavewhenempty, eventmemberstatus, eventwhencalled, reportholdtime)
SELECT CONCAT('spbxq_', queue_number), COALESCE(NULLIF(musicclass,''),'default'), timeout_seconds, 'no', 'yes', 'yes', 'yes', 5, 0, 0, 60, strategy, 'yes', 'no', 'yes', 'yes', 'no'
FROM spbx_queues
WHERE active=1;

INSERT INTO queue_members
(queue_name, interface, membername, state_interface, penalty, paused)
SELECT
  CONCAT('spbxq_', q.queue_number),
  CONCAT('Local/', e.extension, '@', COALESCE(NULLIF(p.context,''),'internal'), '/n'),
  COALESCE(NULLIF(e.display_name,''), e.extension),
  CONCAT('PJSIP/', e.extension),
  COALESCE(m.penalty,0),
  0
FROM spbx_queue_members m
JOIN spbx_queues q ON q.id=m.queue_id
JOIN spbx_extensions e ON e.endpoint_id=m.endpoint_id
JOIN ps_endpoints p ON p.id=e.endpoint_id
WHERE q.active=1
  AND m.active=1
  AND m.member_type='fixed'
ON DUPLICATE KEY UPDATE
  membername=VALUES(membername),
  state_interface=VALUES(state_interface),
  penalty=VALUES(penalty),
  paused=0;

INSERT INTO spbx_settings (setting_key, setting_value, description) VALUES
('asterisk_realtime_queues', '2.4.8', 'Asterisk Realtime Tabellen queues und queue_members für app_queue')
ON DUPLICATE KEY UPDATE
  setting_value=VALUES(setting_value),
  description=VALUES(description);
