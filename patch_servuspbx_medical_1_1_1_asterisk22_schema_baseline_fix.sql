-- ============================================================
-- ServusPBX Medical 1.1.1
-- Asterisk 22 Realtime Schema Baseline
-- ============================================================
-- VORHER BACKUP:
-- mysqldump -u root -p general > /root/general_before_1_1_0.sql
-- ============================================================

-- ------------------------------------------------------------
-- 1. extensions / Dialplan
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `extensions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `context` varchar(40) NOT NULL,
  `exten` varchar(40) NOT NULL,
  `priority` int(11) NOT NULL,
  `app` varchar(40) NOT NULL,
  `appdata` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_context_exten_priority` (`context`,`exten`,`priority`),
  KEY `idx_context_exten` (`context`,`exten`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `extensions`
WHERE `context`='internal'
  AND `exten` IN ('_XX','_XXX','0')
  AND `priority` IN (1,2,3);

INSERT INTO `extensions` (`context`,`exten`,`priority`,`app`,`appdata`) VALUES
('internal','_XX',1,'NoOp','ServusPBX internal 2-digit call ${EXTEN}'),
('internal','_XX',2,'Dial','PJSIP/${EXTEN},30'),
('internal','_XX',3,'Goto','internal,0,1'),
('internal','_XXX',1,'NoOp','ServusPBX internal 3-digit call ${EXTEN}'),
('internal','_XXX',2,'Dial','PJSIP/${EXTEN},30'),
('internal','_XXX',3,'Goto','internal,0,1'),
('internal','0',1,'NoOp','ServusPBX fallback reception 0'),
('internal','0',2,'Dial','PJSIP/0,30'),
('internal','0',3,'Hangup','');

-- ------------------------------------------------------------
-- 2. ps_transports
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ps_transports` (
  `id` varchar(40) NOT NULL,
  `async_operations` int(11) DEFAULT NULL,
  `bind` varchar(40) DEFAULT NULL,
  `ca_list_file` varchar(200) DEFAULT NULL,
  `cert_file` varchar(200) DEFAULT NULL,
  `cipher` varchar(200) DEFAULT NULL,
  `domain` varchar(40) DEFAULT NULL,
  `external_media_address` varchar(40) DEFAULT NULL,
  `external_signaling_address` varchar(40) DEFAULT NULL,
  `external_signaling_port` int(11) DEFAULT NULL,
  `method` varchar(20) DEFAULT NULL,
  `local_net` varchar(40) DEFAULT NULL,
  `password` varchar(40) DEFAULT NULL,
  `priv_key_file` varchar(200) DEFAULT NULL,
  `protocol` varchar(20) DEFAULT NULL,
  `require_client_cert` varchar(10) DEFAULT NULL,
  `verify_client` varchar(10) DEFAULT NULL,
  `verify_server` varchar(10) DEFAULT NULL,
  `tos` varchar(10) DEFAULT NULL,
  `cos` int(11) DEFAULT NULL,
  `websocket_write_timeout` int(11) DEFAULT NULL,
  `allow_reload` varchar(10) DEFAULT NULL,
  `symmetric_transport` varchar(10) DEFAULT NULL,
  `allow_wildcard_certs` varchar(10) DEFAULT NULL,
  `tcp_keepalive_enable` varchar(10) DEFAULT NULL,
  `tcp_keepalive_idle_time` int(11) DEFAULT NULL,
  `tcp_keepalive_interval_time` int(11) DEFAULT NULL,
  `tcp_keepalive_probe_count` int(11) DEFAULT NULL,
  `allow_unauthenticated_options` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `ps_transports`
  ADD COLUMN IF NOT EXISTS `allow_unauthenticated_options` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `symmetric_transport` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `websocket_write_timeout` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `tcp_keepalive_enable` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `tcp_keepalive_idle_time` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `tcp_keepalive_interval_time` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `tcp_keepalive_probe_count` int(11) DEFAULT NULL;

INSERT INTO `ps_transports` (`id`,`protocol`,`bind`,`allow_reload`)
VALUES ('transport-udp','udp','0.0.0.0:5060','yes')
ON DUPLICATE KEY UPDATE
  `protocol`=VALUES(`protocol`),
  `bind`=VALUES(`bind`),
  `allow_reload`=VALUES(`allow_reload`);

-- ------------------------------------------------------------
-- 3. ps_auths
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ps_auths` (
  `id` varchar(80) NOT NULL,
  `auth_type` varchar(20) DEFAULT 'userpass',
  `nonce_lifetime` int(11) DEFAULT 32,
  `md5_cred` varchar(80) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `realm` varchar(160) DEFAULT NULL,
  `username` varchar(120) DEFAULT NULL,
  `password_digest` varchar(255) DEFAULT NULL,
  `oauth_clientid` varchar(255) DEFAULT NULL,
  `oauth_secret` varchar(255) DEFAULT NULL,
  `refresh_token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `ps_auths`
  ADD COLUMN IF NOT EXISTS `nonce_lifetime` int(11) DEFAULT 32,
  ADD COLUMN IF NOT EXISTS `md5_cred` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `password_digest` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `oauth_clientid` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `oauth_secret` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `refresh_token` varchar(255) DEFAULT NULL;

UPDATE `ps_auths`
SET
  `auth_type` = COALESCE(NULLIF(`auth_type`, ''), 'userpass'),
  `nonce_lifetime` = COALESCE(`nonce_lifetime`, 32),
  `username` = COALESCE(NULLIF(`username`, ''), `id`);

-- ------------------------------------------------------------
-- 4. ps_aors
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ps_aors` (
  `id` varchar(80) NOT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `default_expiration` int(11) DEFAULT 3600,
  `mailboxes` varchar(80) DEFAULT NULL,
  `max_contacts` int(11) DEFAULT 1,
  `minimum_expiration` int(11) DEFAULT 60,
  `remove_existing` varchar(10) DEFAULT 'yes',
  `qualify_frequency` int(11) DEFAULT 60,
  `authenticate_qualify` varchar(10) DEFAULT 'no',
  `maximum_expiration` int(11) DEFAULT 7200,
  `outbound_proxy` varchar(255) DEFAULT NULL,
  `support_path` varchar(10) DEFAULT 'no',
  `qualify_timeout` float DEFAULT NULL,
  `voicemail_extension` varchar(40) DEFAULT NULL,
  `remove_unavailable` varchar(10) DEFAULT 'no',
  `qualify_2xx_only` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `ps_aors`
  ADD COLUMN IF NOT EXISTS `outbound_proxy` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `support_path` varchar(10) DEFAULT 'no',
  ADD COLUMN IF NOT EXISTS `qualify_timeout` float DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `voicemail_extension` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `remove_unavailable` varchar(10) DEFAULT 'no',
  ADD COLUMN IF NOT EXISTS `qualify_2xx_only` varchar(10) DEFAULT NULL;

UPDATE `ps_aors`
SET
  `max_contacts` = COALESCE(`max_contacts`, 1),
  `remove_existing` = COALESCE(NULLIF(`remove_existing`, ''), 'yes'),
  `qualify_frequency` = COALESCE(`qualify_frequency`, 60),
  `authenticate_qualify` = COALESCE(NULLIF(`authenticate_qualify`, ''), 'no'),
  `minimum_expiration` = COALESCE(`minimum_expiration`, 60),
  `maximum_expiration` = COALESCE(`maximum_expiration`, 7200),
  `default_expiration` = COALESCE(`default_expiration`, 3600),
  `support_path` = COALESCE(NULLIF(`support_path`, ''), 'no'),
  `remove_unavailable` = COALESCE(NULLIF(`remove_unavailable`, ''), 'no');

-- ------------------------------------------------------------
-- 5. ps_contacts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ps_contacts` (
  `id` varchar(255) NOT NULL,
  `uri` varchar(255) DEFAULT NULL,
  `expiration_time` bigint(20) DEFAULT NULL,
  `qualify_frequency` int(11) DEFAULT NULL,
  `outbound_proxy` varchar(255) DEFAULT NULL,
  `path` text DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `qualify_timeout` float DEFAULT NULL,
  `qualify_2xx_only` varchar(10) DEFAULT NULL,
  `reg_server` varchar(20) DEFAULT NULL,
  `authenticate_qualify` varchar(10) DEFAULT NULL,
  `via_addr` varchar(40) DEFAULT NULL,
  `via_port` int(11) DEFAULT NULL,
  `call_id` varchar(255) DEFAULT NULL,
  `endpoint` varchar(80) DEFAULT NULL,
  `prune_on_boot` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ps_contacts_endpoint` (`endpoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `ps_contacts`
  ADD COLUMN IF NOT EXISTS `uri` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `expiration_time` bigint(20) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `qualify_frequency` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `outbound_proxy` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `path` text DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `user_agent` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `qualify_timeout` float DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `qualify_2xx_only` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `reg_server` varchar(20) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `authenticate_qualify` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `via_addr` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `via_port` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `call_id` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `endpoint` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `prune_on_boot` varchar(10) DEFAULT NULL;

ALTER TABLE `ps_contacts`
  ADD INDEX IF NOT EXISTS `idx_ps_contacts_endpoint` (`endpoint`);

-- ------------------------------------------------------------
-- 6. ps_endpoints
-- ------------------------------------------------------------
ALTER TABLE `ps_endpoints`
  ADD COLUMN IF NOT EXISTS `connected_line_method` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `direct_media_method` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `direct_media_glare_mitigation` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `disable_direct_media_on_nat` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `identify_by` varchar(80) DEFAULT 'username,ip',
  ADD COLUMN IF NOT EXISTS `outbound_proxy` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `rtp_ipv6` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `send_diversion` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `timers_min_se` int(11) DEFAULT 90,
  ADD COLUMN IF NOT EXISTS `timers_sess_expires` int(11) DEFAULT 1800,
  ADD COLUMN IF NOT EXISTS `callerid_privacy` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `callerid_tag` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `use_ptime` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `inband_progress` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `call_group` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `pickup_group` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `named_call_group` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `named_pickup_group` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `device_state_busy_at` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `fax_detect` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `tone_zone` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `one_touch_recording` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `record_on_feature` varchar(80) DEFAULT 'automixmon',
  ADD COLUMN IF NOT EXISTS `record_off_feature` varchar(80) DEFAULT 'automixmon',
  ADD COLUMN IF NOT EXISTS `rtp_engine` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `allow_transfer` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `allow_subscribe` varchar(10) DEFAULT 'yes',
  ADD COLUMN IF NOT EXISTS `sub_min_expiry` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `mwi_from_user` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `dtls_verify` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `dtls_setup` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `srtp_tag_32` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `media_address` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `redirect_method` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `set_var` text DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `message_context` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `force_avp` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `media_use_received_transport` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `accountcode` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `user_eq_phone` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `moh_passthrough` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `media_encryption_optimistic` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `rpid_immediate` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `g726_non_standard` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `rtp_keepalive` int(11) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `rtp_timeout` int(11) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `rtp_timeout_hold` int(11) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `bind_rtp_to_media_address` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `voicemail_extension` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `mwi_subscribe_replaces_unsolicited` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `subscribe_context` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `contact_user` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `asymmetric_rtp_codec` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `rtcp_mux` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `allow_overlap` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `refer_blind_progress` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `notify_early_inuse_ringing` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `max_audio_streams` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `max_video_streams` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `webrtc` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `incoming_mwi_mailbox` varchar(120) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `bundle` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `follow_early_media_fork` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `accept_multiple_sdp_answers` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `suppress_q850_reason_headers` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `trust_connected_line` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `send_connected_line` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `ignore_183_without_sdp` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `allow_unauthenticated_options` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `tenantid` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `follow_redirect_methods` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `security_negotiation` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `incoming_call_offer_pref` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `outgoing_call_offer_pref` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `stir_shaken_profile` varchar(80) DEFAULT NULL;

UPDATE `ps_endpoints`
SET
  `context` = 'internal',
  `transport` = COALESCE(NULLIF(`transport`, ''), 'transport-udp'),
  `aors` = COALESCE(NULLIF(`aors`, ''), `id`),
  `auth` = COALESCE(NULLIF(`auth`, ''), `id`),
  `disallow` = COALESCE(NULLIF(`disallow`, ''), 'all'),
  `allow` = COALESCE(NULLIF(`allow`, ''), 'alaw,ulaw,g722'),
  `direct_media` = COALESCE(NULLIF(`direct_media`, ''), 'no'),
  `force_rport` = COALESCE(NULLIF(`force_rport`, ''), 'yes'),
  `rewrite_contact` = COALESCE(NULLIF(`rewrite_contact`, ''), 'yes'),
  `rtp_symmetric` = COALESCE(NULLIF(`rtp_symmetric`, ''), 'yes'),
  `dtmf_mode` = COALESCE(NULLIF(`dtmf_mode`, ''), 'rfc4733'),
  `language` = COALESCE(NULLIF(`language`, ''), 'de'),
  `send_pai` = COALESCE(NULLIF(`send_pai`, ''), 'yes'),
  `send_rpid` = COALESCE(NULLIF(`send_rpid`, ''), 'yes'),
  `trust_id_inbound` = COALESCE(NULLIF(`trust_id_inbound`, ''), 'yes'),
  `trust_id_outbound` = COALESCE(NULLIF(`trust_id_outbound`, ''), 'yes'),
  `ice_support` = COALESCE(NULLIF(`ice_support`, ''), 'no'),
  `use_avpf` = COALESCE(NULLIF(`use_avpf`, ''), 'no'),
  `media_encryption` = COALESCE(NULLIF(`media_encryption`, ''), 'no'),
  `timers` = COALESCE(NULLIF(`timers`, ''), 'yes'),
  `identify_by` = COALESCE(NULLIF(`identify_by`, ''), 'username,ip'),
  `allow_subscribe` = COALESCE(NULLIF(`allow_subscribe`, ''), 'yes'),
  `record_on_feature` = COALESCE(NULLIF(`record_on_feature`, ''), 'automixmon'),
  `record_off_feature` = COALESCE(NULLIF(`record_off_feature`, ''), 'automixmon');

-- ------------------------------------------------------------
-- 7. Additional realtime tables
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ps_endpoint_id_ips` (
  `id` varchar(80) NOT NULL,
  `endpoint` varchar(80) DEFAULT NULL,
  `match` varchar(80) DEFAULT NULL,
  `srv_lookups` varchar(10) DEFAULT NULL,
  `match_header` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `endpoint` (`endpoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ps_globals` (
  `id` varchar(40) NOT NULL DEFAULT 'global',
  `max_forwards` int(11) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `default_outbound_endpoint` varchar(80) DEFAULT NULL,
  `debug` varchar(40) DEFAULT NULL,
  `endpoint_identifier_order` varchar(255) DEFAULT NULL,
  `default_from_user` varchar(80) DEFAULT NULL,
  `keep_alive_interval` int(11) DEFAULT NULL,
  `regcontext` varchar(80) DEFAULT NULL,
  `contact_expiration_check_interval` int(11) DEFAULT NULL,
  `default_voicemail_extension` varchar(40) DEFAULT NULL,
  `disable_multi_domain` varchar(10) DEFAULT NULL,
  `unidentified_request_count` int(11) DEFAULT NULL,
  `unidentified_request_period` int(11) DEFAULT NULL,
  `unidentified_request_prune_interval` int(11) DEFAULT NULL,
  `default_realm` varchar(80) DEFAULT NULL,
  `mwi_tps_queue_high` int(11) DEFAULT NULL,
  `mwi_tps_queue_low` int(11) DEFAULT NULL,
  `mwi_disable_initial_unsolicited` varchar(10) DEFAULT NULL,
  `ignore_uri_user_options` varchar(10) DEFAULT NULL,
  `use_callerid_contact` varchar(10) DEFAULT NULL,
  `send_contact_status_on_update_registration` varchar(10) DEFAULT NULL,
  `taskprocessor_overload_trigger` varchar(80) DEFAULT NULL,
  `norefersub` varchar(10) DEFAULT NULL,
  `allow_sending_180_after_183` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ps_globals bei bestehender Tabelle vollständig ergänzen, bevor INSERT erfolgt.
ALTER TABLE `ps_globals`
  ADD COLUMN IF NOT EXISTS `max_forwards` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `user_agent` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `default_outbound_endpoint` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `debug` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `endpoint_identifier_order` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `default_from_user` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `keep_alive_interval` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `regcontext` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `contact_expiration_check_interval` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `default_voicemail_extension` varchar(40) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `disable_multi_domain` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `unidentified_request_count` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `unidentified_request_period` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `unidentified_request_prune_interval` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `default_realm` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `mwi_tps_queue_high` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `mwi_tps_queue_low` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `mwi_disable_initial_unsolicited` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `ignore_uri_user_options` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `use_callerid_contact` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `send_contact_status_on_update_registration` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `taskprocessor_overload_trigger` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `norefersub` varchar(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `allow_sending_180_after_183` varchar(10) DEFAULT NULL;

INSERT INTO `ps_globals` (`id`,`endpoint_identifier_order`,`default_realm`,`user_agent`)
VALUES ('global','username,ip','asterisk','Asterisk PBX')
ON DUPLICATE KEY UPDATE
  `endpoint_identifier_order`=COALESCE(NULLIF(`endpoint_identifier_order`,''),VALUES(`endpoint_identifier_order`)),
  `default_realm`=COALESCE(NULLIF(`default_realm`,''),VALUES(`default_realm`)),
  `user_agent`=COALESCE(NULLIF(`user_agent`,''),VALUES(`user_agent`));

CREATE TABLE IF NOT EXISTS `ps_systems` (
  `id` varchar(40) NOT NULL,
  `timer_t1` int(11) DEFAULT NULL,
  `timer_b` int(11) DEFAULT NULL,
  `compact_headers` varchar(10) DEFAULT NULL,
  `threadpool_initial_size` int(11) DEFAULT NULL,
  `threadpool_auto_increment` int(11) DEFAULT NULL,
  `threadpool_idle_timeout` int(11) DEFAULT NULL,
  `threadpool_max_size` int(11) DEFAULT NULL,
  `disable_tcp_switch` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ps_systems` (`id`) VALUES ('system')
ON DUPLICATE KEY UPDATE `id`=VALUES(`id`);

CREATE TABLE IF NOT EXISTS `ps_domain_aliases` (
  `id` varchar(80) NOT NULL,
  `domain` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ps_subscription_persistence` (
  `id` varchar(80) NOT NULL,
  `packet` text DEFAULT NULL,
  `src_name` varchar(128) DEFAULT NULL,
  `src_port` int(11) DEFAULT NULL,
  `transport_key` varchar(64) DEFAULT NULL,
  `local_name` varchar(128) DEFAULT NULL,
  `local_port` int(11) DEFAULT NULL,
  `cseq` int(11) DEFAULT NULL,
  `tag` varchar(128) DEFAULT NULL,
  `endpoint` varchar(80) DEFAULT NULL,
  `expires` int(11) DEFAULT NULL,
  `contact_uri` varchar(255) DEFAULT NULL,
  `prune_on_boot` varchar(10) DEFAULT NULL,
  `generator_data` varchar(255) DEFAULT NULL,
  `serializer` varchar(80) DEFAULT NULL,
  `event` varchar(40) DEFAULT NULL,
  `notification_batch_interval` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `endpoint` (`endpoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 8. CDR
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cdr` (
  `calldate` datetime NOT NULL DEFAULT current_timestamp(),
  `clid` varchar(255) DEFAULT NULL,
  `src` varchar(80) DEFAULT NULL,
  `dst` varchar(80) DEFAULT NULL,
  `dcontext` varchar(80) DEFAULT NULL,
  `channel` varchar(255) DEFAULT NULL,
  `dstchannel` varchar(255) DEFAULT NULL,
  `lastapp` varchar(80) DEFAULT NULL,
  `lastdata` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `billsec` int(11) DEFAULT NULL,
  `disposition` varchar(45) DEFAULT NULL,
  `amaflags` int(11) DEFAULT NULL,
  `accountcode` varchar(80) DEFAULT NULL,
  `uniqueid` varchar(150) DEFAULT NULL,
  `userfield` varchar(255) DEFAULT NULL,
  `sequence` int(11) DEFAULT NULL,
  KEY `calldate` (`calldate`),
  KEY `src` (`src`),
  KEY `dst` (`dst`),
  KEY `uniqueid` (`uniqueid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `cdr`
  ADD COLUMN IF NOT EXISTS `sequence` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `accountcode` varchar(80) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `userfield` varchar(255) DEFAULT NULL;

-- ------------------------------------------------------------
-- 9. Endpoint runtime consistency
-- ------------------------------------------------------------
INSERT IGNORE INTO `ps_aors` (`id`,`max_contacts`,`remove_existing`,`qualify_frequency`)
SELECT `id`, 1, 'yes', 60 FROM `ps_endpoints`;

INSERT IGNORE INTO `ps_auths` (`id`,`auth_type`,`username`,`password`)
SELECT `id`, 'userpass', `id`, ''
FROM `ps_endpoints`
WHERE `id` NOT IN (SELECT `id` FROM `ps_auths`);

UPDATE `ps_endpoints`
SET
  `aors` = COALESCE(NULLIF(`aors`, ''), `id`),
  `auth` = COALESCE(NULLIF(`auth`, ''), `id`),
  `context` = 'internal';

TRUNCATE TABLE `ps_contacts`;

-- ------------------------------------------------------------
-- 10. Marker
-- ------------------------------------------------------------
INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('servuspbx_schema_baseline', '1.1.1', 'Asterisk 22 Realtime Tabellen-Baseline korrigiert'),
('asterisk_realtime_version', '22', 'Realtime Schema auf Asterisk 22 ausgerichtet'),
('ps_contacts_schema', 'asterisk22', 'ps_contacts kompatibel mit Asterisk 22 Register-INSERT'),
('schema_baseline_fix', '1.1.1', 'ps_globals Spalten werden vor INSERT ergänzt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- ============================================================
-- Ende ServusPBX Medical 1.1.1
-- ============================================================
