<?php
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/branding.php';

spbx_require_login();
$user = spbx_current_user();
$isAdmin = ($user['role'] ?? '') === 'admin';

$db = spbx_db();

function spbx_count_value($db, $sql)
{
    $res = $db->query($sql);
    if (!$res) return 0;
    $row = $res->fetch_row();
    return (int)($row[0] ?? 0);
}

function spbx_cdr_has_column($db, $column)
{
    $column = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$column);
    $res = $db->query("SHOW COLUMNS FROM cdr LIKE '" . $db->real_escape_string($column) . "'");
    return $res && $res->num_rows > 0;
}

function spbx_cdr_count_expr($db)
{
    // linkedid fasst beide Call-Legs sauber zusammen. Wenn nicht vorhanden,
    // fallback auf uniqueid und zuletzt auf rohe Zeilen.
    if (spbx_cdr_has_column($db, 'linkedid')) {
        return "COUNT(DISTINCT linkedid)";
    }

    if (spbx_cdr_has_column($db, 'uniqueid')) {
        return "COUNT(DISTINCT uniqueid)";
    }

    return "COUNT(*)";
}

$today = date('Y-m-d');
$cdrCount = spbx_cdr_count_expr($db);

// Eingehend:
// - Trunk kommt in den incoming Context
// - oder Channel ist ein PJSIP/trunk-* Kanal
// - oder DID landet in incoming
$incomingCalls = spbx_count_value($db, "
    SELECT {$cdrCount}
    FROM cdr
    WHERE DATE(calldate)=CURDATE()
      AND (
            dcontext='incoming'
         OR channel LIKE 'PJSIP/trunk-%'
         OR dstchannel LIKE 'PJSIP/trunk-%'
      )
");

// Ausgehend:
// - unsere ausgehenden Routen laufen über outgoing_<hauptnummer>
// - zusätzlich Trunk-Dial über lastdata/dstchannel absichern
$outgoingCalls = spbx_count_value($db, "
    SELECT {$cdrCount}
    FROM cdr
    WHERE DATE(calldate)=CURDATE()
      AND (
            dcontext LIKE 'outgoing\\_%'
         OR lastdata LIKE 'PJSIP/%@trunk-%'
         OR dstchannel LIKE 'PJSIP/%@trunk-%'
      )
");

// Verpasst:
// - nur eingehende verpasste Gespräche zählen, keine nicht angenommenen ausgehenden Tests.
$missedCalls = spbx_count_value($db, "
    SELECT {$cdrCount}
    FROM cdr
    WHERE DATE(calldate)=CURDATE()
      AND (
            dcontext='incoming'
         OR channel LIKE 'PJSIP/trunk-%'
         OR dstchannel LIKE 'PJSIP/trunk-%'
      )
      AND (
            disposition='NO ANSWER'
         OR disposition='BUSY'
         OR disposition='FAILED'
      )
");

$extensions = spbx_count_value($db, "SELECT COUNT(*) FROM spbx_extensions WHERE active=1");

// Nur echte Nebenstellen zählen.
// ps_contacts enthält auch Trunks; diese dürfen bei "Registrierte Nebenstellen" nicht mitgezählt werden.
$registered = spbx_count_value($db, "
    SELECT COUNT(DISTINCT e.id)
    FROM spbx_extensions e
    JOIN ps_contacts c
      ON c.endpoint = e.endpoint_id
    WHERE e.active=1
      AND c.endpoint IS NOT NULL
      AND c.endpoint <> ''
");


function spbx_dashboard_icon($name)
{
    return '<span class="spbx-dashboard-icon spbx-dashboard-icon-' . spbx_h($name) . '">' . spbx_icon($name) . '</span>';
}

?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Dashboard - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="stylesheet" href="css/servuspbx.css">
</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>

    <main class="spbx-main">
        <?php spbx_page_header('Dashboard', $isAdmin ? 'Administration' : 'Benutzerbereich'); ?>

        <div class="spbx-content">
            <div class="spbx-dashboard-grid">
                <div class="spbx-stat-card">
                    <div class="spbx-stat-icon"><?php echo spbx_dashboard_icon('phone-incoming'); ?></div>
                    <div>
                        <div class="spbx-stat-value"><?php echo (int)$incomingCalls; ?></div>
                        <div class="spbx-stat-label">Eingehende Anrufe heute</div>
                    </div>
                </div>

                <div class="spbx-stat-card">
                    <div class="spbx-stat-icon"><?php echo spbx_dashboard_icon('phone-outgoing'); ?></div>
                    <div>
                        <div class="spbx-stat-value"><?php echo (int)$outgoingCalls; ?></div>
                        <div class="spbx-stat-label">Ausgehende Anrufe heute</div>
                    </div>
                </div>

                <div class="spbx-stat-card">
                    <div class="spbx-stat-icon"><?php echo spbx_dashboard_icon('triangle-alert'); ?></div>
                    <div>
                        <div class="spbx-stat-value"><?php echo (int)$missedCalls; ?></div>
                        <div class="spbx-stat-label">Verpasste Anrufe heute</div>
                    </div>
                </div>

                <div class="spbx-stat-card">
                    <div class="spbx-stat-icon"><?php echo spbx_dashboard_icon('phone'); ?></div>
                    <div>
                        <div class="spbx-stat-value"><?php echo (int)$registered; ?>/<?php echo (int)$extensions; ?></div>
                        <div class="spbx-stat-label">Registrierte Nebenstellen</div>
                    </div>
                </div>
            </div>

            <div class="spbx-card spbx-active-calls-card">
                <div class="spbx-card-head">
                    <div>
                        <div class="spbx-card-title"><?php echo spbx_dashboard_icon('phone'); ?> Aktuelle Gespräche</div>
                        <div class="spbx-card-muted">Nur laufende Gespräche aus Asterisk AMI.</div>
                    </div>
                    <div class="spbx-active-calls-count" id="spbxActiveCallsCount">0 aktiv</div>
                </div>
                <div class="spbx-active-calls-wrap">
                    <table class="spbx-table spbx-active-calls-table">
                        <thead><tr><th>Von</th><th>Zu</th><th>Dauer</th><th>Status</th></tr></thead>
                        <tbody id="spbxActiveCallsBody"><tr><td colspan="4" class="spbx-active-calls-empty">Lade aktuelle Gespräche...</td></tr></tbody>
                    </table>
                </div>
            </div>

            <?php if ($isAdmin): ?>
                <div class="spbx-action-grid">
                    <a class="spbx-card spbx-action-card" href="pages/extensions.php">
                        <div class="spbx-card-title"><?php echo spbx_dashboard_icon('users'); ?> Nebenstellen</div>
                        <div class="spbx-card-muted">Durchwahl, Telefonmodell, MAC/IPEI, Voicemail und SIP-Zugang in einer Maske.</div>
                    </a>

                    <a class="spbx-card spbx-action-card" href="pages/trunk_a1.php">
                        <div class="spbx-card-title"><?php echo spbx_dashboard_icon('phone-forwarded'); ?> SIP-Trunks</div>
                        <div class="spbx-card-muted">A1 Registrierung, Rufnummern und Routing.</div>
                    </a>

                    <a class="spbx-card spbx-action-card" href="pages/phonebook.php">
                        <div class="spbx-card-title"><?php echo spbx_dashboard_icon('book-user'); ?> Telefonbuch</div>
                        <div class="spbx-card-muted">Einfaches zentrales Telefonbuch für die Ordination.</div>
                    </a>

                    <a class="spbx-card spbx-action-card" href="pages/opening_hours.php">
                        <div class="spbx-card-title"><?php echo spbx_dashboard_icon('clock'); ?> Öffnungszeiten</div>
                        <div class="spbx-card-muted">Ordinationszeiten, Feiertage und Ansagen.</div>
                    </a>

                    <a class="spbx-card spbx-action-card" href="pages/network.php">
                        <div class="spbx-card-title"><?php echo spbx_dashboard_icon('network'); ?> Netzwerk</div>
                        <div class="spbx-card-muted">LAN 1 Management, LAN 2 SIP-Trunks.</div>
                    </a>

                    <a class="spbx-card spbx-action-card" href="pages/system.php">
                        <div class="spbx-card-title"><?php echo spbx_dashboard_icon('settings'); ?> System</div>
                        <div class="spbx-card-muted">Backup, Dienste, Updates und Systemstatus.</div>
                    </a>
                </div>
            <?php else: ?>
                <div class="spbx-action-grid">
                    <a class="spbx-card spbx-action-card" href="pages/phonebook.php">
                        <div class="spbx-card-title"><?php echo spbx_dashboard_icon('book-user'); ?> Telefonbuch</div>
                        <div class="spbx-card-muted">Kontakte suchen.</div>
                    </a>

                    <a class="spbx-card spbx-action-card" href="pages/call_history.php">
                        <div class="spbx-card-title"><?php echo spbx_dashboard_icon('list'); ?> Anrufliste</div>
                        <div class="spbx-card-muted">Letzte Anrufe anzeigen.</div>
                    </a>
                </div>
            <?php endif; ?>

            <div class="spbx-tablet-hint">
                Oberfläche optimiert für Desktop und aktuelle iPads.
            </div>
        </div>
    </main>
</div>

<script>
(function() {
    const body = document.getElementById('spbxActiveCallsBody');
    const count = document.getElementById('spbxActiveCallsCount');
    if (!body || !count) return;
    function esc(v){return String(v ?? '').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));}
    async function loadActiveCalls() {
        try {
            const res = await fetch('pages/api_active_calls.php?_=' + Date.now(), {cache:'no-store'});
            const data = await res.json();
            if (!data.ok) {
                count.textContent = 'AMI Fehler';
                body.innerHTML = '<tr><td colspan="4" class="spbx-active-calls-empty">AMI nicht erreichbar: ' + esc(data.error || 'unbekannter Fehler') + '</td></tr>';
                return;
            }
            count.textContent = data.count === 1 ? '1 aktiv' : data.count + ' aktiv';
            if (!data.calls || data.calls.length === 0) {
                body.innerHTML = '<tr><td colspan="4" class="spbx-active-calls-empty">Keine aktiven Gespräche</td></tr>';
                return;
            }
            body.innerHTML = data.calls.map(c => '<tr><td><strong>'+esc(c.from)+'</strong></td><td>'+esc(c.to)+'</td><td>'+esc(c.duration)+'</td><td><span class="spbx-call-status">'+esc(c.status)+'</span></td></tr>').join('');
        } catch(e) {
            count.textContent = 'Fehler';
            body.innerHTML = '<tr><td colspan="4" class="spbx-active-calls-empty">Aktuelle Gespräche konnten nicht geladen werden.</td></tr>';
        }
    }
    loadActiveCalls();
    setInterval(loadActiveCalls, 3000);
})();
</script>

</body>
</html>
