<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/asterisk.php';
require_once __DIR__ . '/../inc/outbound_routes.php';
require_once __DIR__ . '/../inc/trunk_providers.php';
require_once __DIR__ . '/../inc/ast_config_writer.php';

spbx_require_admin();

$db = spbx_db();

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function spbx_trunks_v2_install_schema()
{
    $db = spbx_db();

    $db->query("CREATE TABLE IF NOT EXISTS spbx_trunks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        provider VARCHAR(40) NOT NULL DEFAULT 'A1',
        trunk_name VARCHAR(120) NOT NULL,
        name VARCHAR(120) DEFAULT NULL,
        main_number VARCHAR(40) NOT NULL,
        username VARCHAR(120) NOT NULL,
        auth_user VARCHAR(120) DEFAULT NULL,
        domain VARCHAR(160) DEFAULT NULL,
        server_uri VARCHAR(180) DEFAULT NULL,
        outbound_proxy VARCHAR(180) DEFAULT NULL,
        endpoint_id VARCHAR(120) DEFAULT NULL,
        contact_user VARCHAR(120) DEFAULT NULL,
        emergency_profile ENUM('AT','DE','CH','LI','CUSTOM') NOT NULL DEFAULT 'AT',
        clip_no_screening TINYINT(1) NOT NULL DEFAULT 1,
        ext_from INT DEFAULT NULL,
        ext_to INT DEFAULT NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_main_number (main_number),
        KEY idx_endpoint_id (endpoint_id),
        KEY idx_provider (provider),
        KEY idx_active (active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $cols = [
        'trunk_name' => "ALTER TABLE spbx_trunks ADD COLUMN trunk_name VARCHAR(120) NOT NULL DEFAULT '' AFTER provider",
        'name' => "ALTER TABLE spbx_trunks ADD COLUMN name VARCHAR(120) DEFAULT NULL AFTER trunk_name",
        'main_number' => "ALTER TABLE spbx_trunks ADD COLUMN main_number VARCHAR(40) NOT NULL DEFAULT '' AFTER name",
        'username' => "ALTER TABLE spbx_trunks ADD COLUMN username VARCHAR(120) NOT NULL DEFAULT '' AFTER main_number",
        'auth_user' => "ALTER TABLE spbx_trunks ADD COLUMN auth_user VARCHAR(120) DEFAULT NULL AFTER username",
        'domain' => "ALTER TABLE spbx_trunks ADD COLUMN domain VARCHAR(160) DEFAULT NULL AFTER auth_user",
        'server_uri' => "ALTER TABLE spbx_trunks ADD COLUMN server_uri VARCHAR(180) DEFAULT NULL AFTER domain",
        'outbound_proxy' => "ALTER TABLE spbx_trunks ADD COLUMN outbound_proxy VARCHAR(180) DEFAULT NULL AFTER server_uri",
        'endpoint_id' => "ALTER TABLE spbx_trunks ADD COLUMN endpoint_id VARCHAR(120) DEFAULT NULL AFTER outbound_proxy",
        'contact_user' => "ALTER TABLE spbx_trunks ADD COLUMN contact_user VARCHAR(120) DEFAULT NULL AFTER endpoint_id",
        'emergency_profile' => "ALTER TABLE spbx_trunks ADD COLUMN emergency_profile ENUM('AT','DE','CH','LI','CUSTOM') NOT NULL DEFAULT 'AT' AFTER contact_user",
        'clip_no_screening' => "ALTER TABLE spbx_trunks ADD COLUMN clip_no_screening TINYINT(1) NOT NULL DEFAULT 1 AFTER emergency_profile",
        'ext_from' => "ALTER TABLE spbx_trunks ADD COLUMN ext_from INT DEFAULT NULL AFTER clip_no_screening",
        'ext_to' => "ALTER TABLE spbx_trunks ADD COLUMN ext_to INT DEFAULT NULL AFTER ext_from",
        'active' => "ALTER TABLE spbx_trunks ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1 AFTER ext_to",
    ];

    foreach ($cols as $col => $sql) {
        $res = $db->query("SHOW COLUMNS FROM spbx_trunks LIKE '" . $db->real_escape_string($col) . "'");
        if (!$res || $res->num_rows === 0) {
            $db->query($sql);
        }
    }
}

function spbx_trunks_v2_save_pjsip($data, $password)
{
    $db = spbx_db();

    $provider = spbx_trunk_normalize_provider($data['provider']);
    $cfg = spbx_trunk_provider_defaults($provider);
    $endpointId = $data['endpoint_id'];
    $main = $data['main_number'];
    $username = $data['username'];
    $authUser = $data['auth_user'];
    $domain = $data['domain'];
    $serverUri = $data['server_uri'];
    $contactUser = $data['contact_user'];
    $transport = $cfg['transport'];

    // Auth nur aktualisieren, wenn ein Passwort eingegeben wurde oder Auth fehlt.
    $existingAuth = $db->prepare("SELECT id FROM ps_auths WHERE id=? LIMIT 1");
    $existingAuth->bind_param('s', $endpointId);
    $existingAuth->execute();
    $authExists = $existingAuth->get_result()->num_rows > 0;

    if ($password !== '' || !$authExists) {
        $stmt = $db->prepare("INSERT INTO ps_auths (id, auth_type, username, password)
            VALUES (?, 'userpass', ?, ?)
            ON DUPLICATE KEY UPDATE username=VALUES(username), password=IF(VALUES(password)='', password, VALUES(password))");
        $stmt->bind_param('sss', $endpointId, $authUser, $password);
        $stmt->execute();
    } else {
        $stmt = $db->prepare("UPDATE ps_auths SET username=? WHERE id=?");
        $stmt->bind_param('ss', $authUser, $endpointId);
        $stmt->execute();
    }

    // Legacy-safe: manche Installationen haben in ps_aors ein NOT NULL Feld "extension".
    $hasAorExtension = false;
    $colRes = $db->query("SHOW COLUMNS FROM ps_aors LIKE 'extension'");
    if ($colRes && $colRes->num_rows > 0) {
        $hasAorExtension = true;
    }

    $contact = $serverUri ?: ('sip:' . $domain);

    if ($hasAorExtension) {
        $stmt = $db->prepare("INSERT INTO ps_aors (id, extension, max_contacts, remove_existing, qualify_frequency, contact)
            VALUES (?, ?, 1, 'yes', 60, ?)
            ON DUPLICATE KEY UPDATE extension=VALUES(extension), max_contacts=1, remove_existing='yes', qualify_frequency=60, contact=VALUES(contact)");
        $stmt->bind_param('sss', $endpointId, $endpointId, $contact);
    } else {
        $stmt = $db->prepare("INSERT INTO ps_aors (id, max_contacts, remove_existing, qualify_frequency, contact)
            VALUES (?, 1, 'yes', 60, ?)
            ON DUPLICATE KEY UPDATE max_contacts=1, remove_existing='yes', qualify_frequency=60, contact=VALUES(contact)");
        $stmt->bind_param('ss', $endpointId, $contact);
    }

    $stmt->execute();

    // Provider-Trunk inbound ohne Auth, outbound_auth bleibt gesetzt.
    $context = 'incoming';
    $allow = 'alaw,ulaw';
    $dtmf = 'rfc4733';
    $language = 'de';
    $directMedia = 'no';
    $rewriteContact = 'yes';
    $rtpSymmetric = 'yes';
    $forceRport = 'yes';
    $sendPai = 'yes';
    $sendRpid = 'yes';
    $trustId = 'yes';
    $deviceType = 'trunk';

    // Legacy-safe: manche Installationen haben in ps_endpoints ein NOT NULL Feld "extension".
    $hasEndpointExtension = false;
    $colRes = $db->query("SHOW COLUMNS FROM ps_endpoints LIKE 'extension'");
    if ($colRes && $colRes->num_rows > 0) {
        $hasEndpointExtension = true;
    }

    $hasEndpointDisplayName = false;
    $colRes = $db->query("SHOW COLUMNS FROM ps_endpoints LIKE 'display_name'");
    if ($colRes && $colRes->num_rows > 0) {
        $hasEndpointDisplayName = true;
    }

    $hasEndpointFromDomain = false;
    $colRes = $db->query("SHOW COLUMNS FROM ps_endpoints LIKE 'from_domain'");
    if ($colRes && $colRes->num_rows > 0) {
        $hasEndpointFromDomain = true;
    }

    $hasEndpointContactUser = false;
    $colRes = $db->query("SHOW COLUMNS FROM ps_endpoints LIKE 'contact_user'");
    if ($colRes && $colRes->num_rows > 0) {
        $hasEndpointContactUser = true;
    }

    $endpointFromDomain = $domain;
    $endpointContactUser = $contactUser;

    $endpointDisplayName = trim((string)($data['trunk_name'] ?? $endpointId));
    if ($endpointDisplayName === '') {
        $endpointDisplayName = $endpointId;
    }

    $endpointColumns = [
        'id', 'transport', 'aors', 'auth', 'outbound_auth', 'context', 'disallow', 'allow', 'direct_media',
        'rewrite_contact', 'rtp_symmetric', 'force_rport', 'send_pai', 'send_rpid',
        'trust_id_inbound', 'trust_id_outbound', 'dtmf_mode', 'language', 'device_type', 'identify_by'
    ];

    $endpointValues = [
        $endpointId, $transport, $endpointId, null, $endpointId, $context, 'all', $allow, $directMedia,
        $rewriteContact, $rtpSymmetric, $forceRport, $sendPai, $sendRpid,
        $trustId, $trustId, $dtmf, $language, $deviceType, 'username,ip'
    ];

    if ($hasEndpointExtension) {
        array_splice($endpointColumns, 1, 0, ['extension']);
        array_splice($endpointValues, 1, 0, [$endpointId]);
    }

    if ($hasEndpointDisplayName) {
        $endpointColumns[] = 'display_name';
        $endpointValues[] = $endpointDisplayName;
    }

    if ($hasEndpointFromDomain) {
        $endpointColumns[] = 'from_domain';
        $endpointValues[] = $endpointFromDomain;
    }

    if ($hasEndpointContactUser) {
        $endpointColumns[] = 'contact_user';
        $endpointValues[] = $endpointContactUser;
    }

    $placeholders = [];
    foreach ($endpointValues as $v) {
        $placeholders[] = ($v === null) ? 'NULL' : '?';
    }

    $updates = [
        "transport=VALUES(transport)",
        "aors=VALUES(aors)",
        "auth=NULL",
        "outbound_auth=VALUES(outbound_auth)",
        "context='incoming'",
        "disallow='all'",
        "allow=VALUES(allow)",
        "direct_media=VALUES(direct_media)",
        "rewrite_contact=VALUES(rewrite_contact)",
        "rtp_symmetric=VALUES(rtp_symmetric)",
        "force_rport=VALUES(force_rport)",
        "send_pai=VALUES(send_pai)",
        "send_rpid=VALUES(send_rpid)",
        "trust_id_inbound=VALUES(trust_id_inbound)",
        "trust_id_outbound=VALUES(trust_id_outbound)",
        "dtmf_mode=VALUES(dtmf_mode)",
        "language=VALUES(language)",
        "device_type='trunk'",
        "identify_by='username,ip'"
    ];

    if ($hasEndpointExtension) {
        array_unshift($updates, "extension=VALUES(extension)");
    }
    if ($hasEndpointDisplayName) {
        $updates[] = "display_name=VALUES(display_name)";
    }

    if ($hasEndpointFromDomain) {
        $updates[] = "from_domain=VALUES(from_domain)";
    }

    if ($hasEndpointContactUser) {
        $updates[] = "contact_user=VALUES(contact_user)";
    }

    $sql = "INSERT INTO ps_endpoints (" . implode(', ', $endpointColumns) . ")
            VALUES (" . implode(', ', $placeholders) . ")
            ON DUPLICATE KEY UPDATE " . implode(', ', $updates);

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('ps_endpoints prepare failed: ' . $db->error);
    }

    $bindValues = [];
    foreach ($endpointValues as $v) {
        if ($v !== null) {
            $bindValues[] = (string)$v;
        }
    }

    if ($bindValues) {
        $types = str_repeat('s', count($bindValues));
        $stmt->bind_param($types, ...$bindValues);
    }

    $stmt->execute();

    $clientUri = 'sip:' . $username . '@' . $domain;
    $srv = $serverUri ?: ('sip:' . $domain);

    // Legacy-safe: manche Installationen haben in ps_registrations ein NOT NULL Feld "extension".
    $hasRegExtension = false;
    $colRes = $db->query("SHOW COLUMNS FROM ps_registrations LIKE 'extension'");
    if ($colRes && $colRes->num_rows > 0) {
        $hasRegExtension = true;
    }

    if ($hasRegExtension) {
        $stmt = $db->prepare("INSERT INTO ps_registrations
            (id, extension, transport, outbound_auth, server_uri, client_uri, contact_user, endpoint, retry_interval,
             forbidden_retry_interval, expiration, line)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 60, 300, 3600, 'yes')
            ON DUPLICATE KEY UPDATE
                extension=VALUES(extension),
                transport=VALUES(transport),
                outbound_auth=VALUES(outbound_auth),
                server_uri=VALUES(server_uri),
                client_uri=VALUES(client_uri),
                contact_user=VALUES(contact_user),
                endpoint=VALUES(endpoint),
                retry_interval=60,
                forbidden_retry_interval=300,
                expiration=3600,
                line='yes'");
        $stmt->bind_param('ssssssss', $endpointId, $endpointId, $transport, $endpointId, $srv, $clientUri, $contactUser, $endpointId);
    } else {
        $stmt = $db->prepare("INSERT INTO ps_registrations
            (id, transport, outbound_auth, server_uri, client_uri, contact_user, endpoint, retry_interval,
             forbidden_retry_interval, expiration, line)
            VALUES (?, ?, ?, ?, ?, ?, ?, 60, 300, 3600, 'yes')
            ON DUPLICATE KEY UPDATE
                transport=VALUES(transport),
                outbound_auth=VALUES(outbound_auth),
                server_uri=VALUES(server_uri),
                client_uri=VALUES(client_uri),
                contact_user=VALUES(contact_user),
                endpoint=VALUES(endpoint),
                retry_interval=60,
                forbidden_retry_interval=300,
                expiration=3600,
                line='yes'");
        $stmt->bind_param('sssssss', $endpointId, $transport, $endpointId, $srv, $clientUri, $contactUser, $endpointId);
    }

    $stmt->execute();

    // Kein leerer Identify-Eintrag.
    // A1 kommt über Registration line=yes / username/ip herein.
    // Ein Identify ohne match erzeugt:
    // "Identify is not configured to match anything".
    $stmt = $db->prepare("DELETE FROM ps_endpoint_id_ips WHERE id=? OR endpoint=?");
    if ($stmt) {
        $stmt->bind_param('ss', $endpointId, $endpointId);
        $stmt->execute();
    }
}

spbx_trunks_v2_install_schema();
spbx_outbound_install_schema();

$message = '';
$error = '';
$edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $provider = spbx_trunk_normalize_provider($_POST['provider'] ?? 'A1');
        $cfg = spbx_trunk_provider_defaults($provider);

        $trunkName = trim((string)($_POST['trunk_name'] ?? ''));
        $mainNumber = spbx_trunk_normalize_e164($_POST['main_number'] ?? ($_POST['username'] ?? ''), $cfg['default_country']);
        $username = trim((string)($_POST['username'] ?? $mainNumber));
        if ($username === '') $username = $mainNumber;

        $password = (string)($_POST['password'] ?? '');
        $authUser = spbx_trunk_auto_auth_user($provider, $username, $_POST['auth_user'] ?? '', $mainNumber);
        $contactUser = spbx_trunk_auto_contact_user($provider, $username, $mainNumber);
        $domain = trim((string)($_POST['domain'] ?? ''));
        if ($domain === '') $domain = $cfg['domain'];
        $serverUri = 'sip:' . $domain;
        $outboundProxy = trim((string)($_POST['outbound_proxy'] ?? ''));
        $emergencyProfile = strtoupper(trim((string)($_POST['emergency_profile'] ?? spbx_trunk_country_from_number($mainNumber, $cfg['default_country']))));
        if (!in_array($emergencyProfile, ['AT','DE','CH','LI','CUSTOM'], true)) $emergencyProfile = $cfg['default_country'];
        $clip = isset($_POST['clip_no_screening']) ? 1 : 0;
        $extFrom = ($_POST['ext_from'] ?? '') === '' ? null : (int)$_POST['ext_from'];
        $extTo = ($_POST['ext_to'] ?? '') === '' ? null : (int)$_POST['ext_to'];
        $active = isset($_POST['active']) ? 1 : 0;

        if ($trunkName === '') $trunkName = $cfg['label'] . ' ' . $mainNumber;
        if ($mainNumber === '') $error = 'Hauptnummer fehlt.';
        if ($username === '') $error = 'Benutzername fehlt.';
        if ($domain === '') $error = 'Domain fehlt.';
        if ($id === 0 && $password === '') $error = 'Passwort fehlt.';
        if ($extFrom !== null && $extTo !== null && $extFrom > $extTo) $error = 'Durchwahlbereich ist ungültig.';

        if ($error === '') {
            $endpointId = spbx_trunk_endpoint_id($provider, $mainNumber);

            if ($id > 0) {
                $stmt = $db->prepare("UPDATE spbx_trunks
                    SET provider=?, trunk_name=?, name=?, main_number=?, username=?, auth_user=?, domain=?,
                        server_uri=?, outbound_proxy=?, endpoint_id=?, contact_user=?, emergency_profile=?,
                        clip_no_screening=?, ext_from=?, ext_to=?, active=?
                    WHERE id=?");
                $name = $trunkName;
                $stmt->bind_param(
                    'ssssssssssssiiiii',
                    $provider, $trunkName, $name, $mainNumber, $username, $authUser, $domain,
                    $serverUri, $outboundProxy, $endpointId, $contactUser, $emergencyProfile,
                    $clip, $extFrom, $extTo, $active, $id
                );
                $stmt->execute();
            } else {
                $stmt = $db->prepare("INSERT INTO spbx_trunks
                    (provider, trunk_name, name, main_number, username, auth_user, domain, server_uri,
                     outbound_proxy, endpoint_id, contact_user, emergency_profile, clip_no_screening,
                     ext_from, ext_to, active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $name = $trunkName;
                $stmt->bind_param(
                    'ssssssssssssiiii',
                    $provider, $trunkName, $name, $mainNumber, $username, $authUser, $domain,
                    $serverUri, $outboundProxy, $endpointId, $contactUser, $emergencyProfile,
                    $clip, $extFrom, $extTo, $active
                );
                $stmt->execute();
                $id = (int)$db->insert_id;
            }

            $data = [
                'provider' => $provider,
                'trunk_name' => $trunkName,
                'main_number' => $mainNumber,
                'username' => $username,
                'auth_user' => $authUser,
                'domain' => $domain,
                'server_uri' => $serverUri,
                'outbound_proxy' => $outboundProxy,
                'endpoint_id' => $endpointId,
                'contact_user' => $contactUser,
                'emergency_profile' => $emergencyProfile,
                'clip_no_screening' => $clip,
            ];

            spbx_trunks_v2_save_pjsip($data, $password);
            spbx_trunk_ensure_contexts($mainNumber, $emergencyProfile);
            spbx_ast_config_writer_rebuild_from_outbound_routes();

            // Outbound-Route Detailwerte nachziehen.
            $outCtx = spbx_outbound_context_from_number($mainNumber);
            $stmt = $db->prepare("UPDATE spbx_outbound_routes
                SET clip_no_screening=?, callerid_mode='extension', emergency_profile=?, active=?
                WHERE outgoing_context=?");
            $stmt->bind_param('isis', $clip, $emergencyProfile, $active, $outCtx);
            $stmt->execute();

            spbx_ast_cli('pjsip reload');
            spbx_ast_cli('dialplan reload');

            $message = 'Trunk gespeichert. Contexts und Dialplan wurden automatisch erzeugt.';
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $res = $db->query("SELECT endpoint_id FROM spbx_trunks WHERE id=" . $id . " LIMIT 1");
        $row = $res ? $res->fetch_assoc() : null;
        if ($row) {
            $ep = $db->real_escape_string($row['endpoint_id']);
            $db->query("DELETE FROM ps_registrations WHERE id='{$ep}'");
            $db->query("DELETE FROM ps_endpoints WHERE id='{$ep}'");
            $db->query("DELETE FROM ps_aors WHERE id='{$ep}'");
            $db->query("DELETE FROM ps_auths WHERE id='{$ep}'");
            $db->query("DELETE FROM ps_endpoint_id_ips WHERE id='{$ep}'");
            $db->query("DELETE FROM spbx_trunks WHERE id=" . $id);
            spbx_outbound_rebuild_dialplan();
            spbx_ast_cli('pjsip reload');
            spbx_ast_cli('dialplan reload');
            $message = 'Trunk gelöscht.';
        }
    }
}

if (isset($_GET['new'])) {
    $edit = [
        'id' => 0,
        'provider' => 'A1',
        'trunk_name' => '',
        'main_number' => '',
        'username' => '',
        'auth_user' => '',
        'domain' => 'siptrunk.a1.net',
        'server_uri' => 'sip:siptrunk.a1.net',
        'outbound_proxy' => '',
        'emergency_profile' => 'AT',
        'clip_no_screening' => 1,
        'ext_from' => 10,
        'ext_to' => 99,
        'active' => 1,
    ];
} elseif (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $db->query("SELECT * FROM spbx_trunks WHERE id=" . $id . " LIMIT 1");
    $edit = $res ? $res->fetch_assoc() : null;
}

$rows = $db->query("SELECT * FROM spbx_trunks ORDER BY id ASC");
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>SIP-Trunks - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/servuspbx.css">

    <style>
        /* 1.4.0 trunk form css guard */
        .spbx-form .spbx-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px 22px;
        }
        .spbx-form .spbx-field {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }
        .spbx-form .spbx-field label {
            font-weight: 800;
            color: #071f45;
        }
        .spbx-form input[type="text"],
        .spbx-form input[type="password"],
        .spbx-form input[type="number"],
        .spbx-form input:not([type]),
        .spbx-form select {
            width: 100%;
            min-height: 44px;
            border: 1px solid #cbd8e8;
            border-radius: 12px;
            padding: 10px 14px;
            background: #fff;
            color: #061b3a;
            font: inherit;
            box-sizing: border-box;
        }
        .spbx-form input[type="checkbox"] {
            width: 22px;
            height: 22px;
            vertical-align: middle;
        }
        .spbx-form .spbx-card-muted {
            font-weight: 700;
            color: #5d7190;
            line-height: 1.35;
        }
        .spbx-form .spbx-actions {
            margin-top: 22px;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .spbx-form .spbx-range-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .spbx-form .spbx-range-row span {
            display: block;
            font-weight: 800;
            color: #5d7190;
            margin-bottom: 6px;
        }
        .spbx-form .spbx-range-field {
            grid-column: span 1;
        }

        @media (max-width: 900px) {
            .spbx-form .spbx-grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>

</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>

    <main class="spbx-main">
        <?php spbx_page_header('SIP-Trunks', 'Provider-Registrierungen und Standort-Kontexte'); ?>

        <div class="spbx-content">
            <div class="spbx-card">
                <div class="spbx-card-head">
                    <div>
                        <div class="spbx-card-title"><?php echo $edit ? 'SIP-Trunk bearbeiten' : 'SIP-Trunks'; ?></div>
                        <div class="spbx-card-muted">ServusPBX erzeugt incoming, internal_&lt;rufnummer&gt; und outgoing_&lt;rufnummer&gt; automatisch.</div>
                    </div>
                    <?php if (!$edit): ?>
                        <a class="spbx-button primary" href="trunk_a1.php?new=1">+ Neuer Trunk</a>
                    <?php endif; ?>
                </div>

                <?php if ($error): ?><div class="spbx-alert error"><?php echo h($error); ?></div><?php endif; ?>
                <?php if ($message): ?><div class="spbx-alert success"><?php echo h($message); ?></div><?php endif; ?>

                <?php if ($edit): ?>
                    <form method="post" class="spbx-form">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>">

                        <div class="spbx-grid-2">
                            <div class="spbx-field">
                                <label>Provider *</label>
                                <select name="provider" id="provider">
                                    <option value="A1" <?php echo (($edit['provider'] ?? 'A1')==='A1')?'selected':''; ?>>A1</option>
                                    <option value="MAGENTA" <?php echo (($edit['provider'] ?? '')==='MAGENTA')?'selected':''; ?>>Magenta</option>
                                    <option value="EASYBELL" <?php echo (($edit['provider'] ?? '')==='EASYBELL')?'selected':''; ?>>easybell</option>
                                </select>
                                <div class="spbx-card-muted" id="providerHint"></div>
                            </div>

                            <div class="spbx-field">
                                <label>Name *</label>
                                <input name="trunk_name" value="<?php echo h($edit['trunk_name'] ?? ($edit['name'] ?? '')); ?>" placeholder="Ordination Hauptnummer">
                            </div>

                            <div class="spbx-field">
                                <label>Hauptnummer / Rufnummer *</label>
                                <input name="main_number" id="mainNumber" value="<?php echo h($edit['main_number'] ?? ''); ?>" placeholder="+43312423826">
                                <div class="spbx-card-muted">ServusPBX erstellt automatisch incoming, internal_&lt;rufnummer&gt; und outgoing_&lt;rufnummer&gt;.</div>
                            </div>

                            <div class="spbx-field">
                                <label>Benutzername *</label>
                                <input name="username" value="<?php echo h($edit['username'] ?? ($edit['main_number'] ?? '')); ?>" placeholder="+43312423826">
                            </div>

                            <div class="spbx-field provider-extra provider-easybell">
                                <label>Auth-User</label>
                                <input name="auth_user" value="<?php echo h($edit['auth_user'] ?? ''); ?>" placeholder="falls abweichend">
                            </div>

                            <div class="spbx-field">
                                <label>Passwort <?php echo ((int)($edit['id'] ?? 0) > 0) ? '' : '*'; ?></label>
                                <input name="password" type="password" autocomplete="new-password" placeholder="<?php echo ((int)($edit['id'] ?? 0) > 0) ? 'leer lassen, wenn unverändert' : 'vom Provider'; ?>">
                            </div>

                            <div class="spbx-field">
                                <label>Domain *</label>
                                <input name="domain" id="domain" value="<?php echo h($edit['domain'] ?? 'siptrunk.a1.net'); ?>" placeholder="siptrunk.a1.net">
                            </div>

                            <div class="spbx-field">
                                <label>Server URI</label>
                                <input name="server_uri" id="serverUri" value="<?php echo h($edit['server_uri'] ?? 'sip:siptrunk.a1.net'); ?>" placeholder="sip:siptrunk.a1.net">
                            </div>

                            <div class="spbx-field">
                                <label>Outbound Proxy</label>
                                <input name="outbound_proxy" value="<?php echo h($edit['outbound_proxy'] ?? ''); ?>" placeholder="optional">
                            </div>

                            <div class="spbx-field">
                                <label>Land / Notrufprofil *</label>
                                <select name="emergency_profile" id="emergencyProfile">
                                    <option value="AT" <?php echo (($edit['emergency_profile'] ?? 'AT')==='AT')?'selected':''; ?>>Österreich</option>
                                    <option value="DE" <?php echo (($edit['emergency_profile'] ?? '')==='DE')?'selected':''; ?>>Deutschland</option>
                                    <option value="CH" <?php echo (($edit['emergency_profile'] ?? '')==='CH')?'selected':''; ?>>Schweiz</option>
                                    <option value="LI" <?php echo (($edit['emergency_profile'] ?? '')==='LI')?'selected':''; ?>>Liechtenstein</option>
                                </select>
                            </div>

                            <div class="spbx-field spbx-range-field">
                                <label>Durchwahlbereich</label>
                                <div class="spbx-range-row">
                                    <div>
                                        <span>Von</span>
                                        <input name="ext_from" type="number" min="1" max="9999" value="<?php echo h($edit['ext_from'] ?? '10'); ?>">
                                    </div>
                                    <div>
                                        <span>Bis</span>
                                        <input name="ext_to" type="number" min="1" max="9999" value="<?php echo h($edit['ext_to'] ?? '99'); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="spbx-field">
                                <label>CLIP no Screening</label>
                                <label><input type="checkbox" name="clip_no_screening" <?php echo (int)($edit['clip_no_screening'] ?? 1)===1?'checked':''; ?>> aktiv</label>
                                <div class="spbx-card-muted">Bei A1 wird damit Hauptnummer + Nebenstelle als ausgehende CallerID verwendet.</div>
                            </div>

                            <div class="spbx-field">
                                <label>Status</label>
                                <label><input type="checkbox" name="active" <?php echo (int)($edit['active'] ?? 1)===1?'checked':''; ?>> aktiv</label>
                            </div>
                        </div>

                        <div class="spbx-actions">
                            <a class="spbx-button secondary" href="trunk_a1.php">Abbrechen</a>
                            <button class="spbx-button primary" type="submit">Speichern</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="spbx-table-wrap">
                        <table class="spbx-table">
                            <thead>
                                <tr>
                                    <th>Provider</th>
                                    <th>Name</th>
                                    <th>Hauptnummer</th>
                                    <th>Internal</th>
                                    <th>Outgoing</th>
                                    <th>DW-Bereich</th>
                                    <th>Land</th>
                                    <th>Status</th>
                                    <th>Aktion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($rows && $rows->num_rows): while ($r = $rows->fetch_assoc()):
                                    $main = $r['main_number'];
                                    $internal = function_exists('spbx_internal_context_from_number') ? spbx_internal_context_from_number($main) : 'internal_' . preg_replace('/[^0-9]/', '', $main);
                                    $outgoing = function_exists('spbx_outbound_context_from_number') ? spbx_outbound_context_from_number($main) : 'outgoing_' . preg_replace('/[^0-9]/', '', $main);
                                ?>
                                    <tr>
                                        <td><?php echo h($r['provider']); ?></td>
                                        <td><?php echo h($r['trunk_name'] ?: ($r['name'] ?? '')); ?></td>
                                        <td><?php echo h($main); ?></td>
                                        <td><?php echo h($internal); ?></td>
                                        <td><?php echo h($outgoing); ?></td>
                                        <td><?php echo h(($r['ext_from'] ?? '') . ' - ' . ($r['ext_to'] ?? '')); ?></td>
                                        <td><?php echo h($r['emergency_profile'] ?? 'AT'); ?></td>
                                        <td><?php echo (int)$r['active']===1 ? 'Aktiv' : 'Inaktiv'; ?></td>
                                        <td>
                                            <a class="spbx-button small secondary" href="trunk_a1.php?edit=<?php echo (int)$r['id']; ?>">Bearbeiten</a>
                                            <form method="post" style="display:inline" onsubmit="return confirm('Trunk wirklich löschen?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                                <button class="spbx-button small danger" type="submit">Löschen</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="9">Keine Trunks vorhanden.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
(function(){
    const defaults = {
        A1: {domain:'siptrunk.a1.net', country:'AT', hint:'A1: Auth-User und Contact-User werden automatisch aus der Hauptnummer gebildet.'},
        MAGENTA: {domain:'', country:'AT', hint:'Magenta: vorerst minimal/generisch. Nur notwendige Felder eintragen.'},
        EASYBELL: {domain:'sip.easybell.de', country:'DE', hint:'easybell: Auth-User kann abweichen und bleibt deshalb sichtbar.'}
    };
    function applyProvider(changed){
        const p = document.getElementById('provider');
        if(!p) return;
        const cfg = defaults[p.value] || defaults.A1;
        const domain = document.getElementById('domain');
        const country = document.getElementById('emergencyProfile');
        const hint = document.getElementById('providerHint');
        if(changed){
            if(domain) domain.value = cfg.domain;
            if(country) country.value = cfg.country;
        }
        if(hint) hint.textContent = cfg.hint;
        document.querySelectorAll('.provider-extra').forEach(el => el.style.display = 'none');
        if(p.value === 'EASYBELL') {
            document.querySelectorAll('.provider-easybell').forEach(el => el.style.display = '');
        }
    }
    document.addEventListener('DOMContentLoaded', () => applyProvider(false));
    document.addEventListener('change', e => {
        if(e.target && e.target.id === 'provider') applyProvider(true);
    });
})();
</script>
</body>
</html>
