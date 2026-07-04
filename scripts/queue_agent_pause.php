#!/usr/bin/env php
<?php
require_once __DIR__ . '/../inc/queues.php';

$endpoint = preg_replace('/[^0-9A-Za-z_\-]/', '', (string)($argv[1] ?? ''));
if ($endpoint === '') exit(1);

$db = spbx_db();
$reason = $db->query("SELECT id FROM spbx_queue_pause_reasons WHERE active=1 ORDER BY sort_order,id LIMIT 1")->fetch_assoc();
$reasonId = $reason ? (int)$reason['id'] : null;

$stmt = $db->prepare("
    SELECT s.*, q.queue_number, e.extension, p.context
    FROM spbx_queue_agent_status s
    JOIN spbx_queues q ON q.id=s.queue_id
    JOIN spbx_extensions e ON e.endpoint_id=s.endpoint_id
    JOIN ps_endpoints p ON p.id=e.endpoint_id
    WHERE s.endpoint_id=? AND s.logged_in=1
");
$stmt->bind_param('s', $endpoint);
$stmt->execute();
$res = $stmt->get_result();

$changed = 0;
while ($r = $res->fetch_assoc()) {
    $paused = (int)$r['paused'];
    $qname = 'spbxq_' . preg_replace('/[^0-9A-Za-z_\-]/', '', (string)$r['queue_number']);
    $iface = 'Local/' . $endpoint . '@' . ((string)($r['context'] ?? '') ?: 'internal') . '/n';
    spbx_ast_cli('queue pause member ' . $iface . ' queue ' . $qname . ' ' . ($paused ? 'reason none' : 'reason Arbeitspause'));

    if ($paused) {
        $upd = $db->prepare("UPDATE spbx_queue_agent_status SET paused=0, pause_reason_id=NULL WHERE id=?");
    } else {
        $upd = $db->prepare("UPDATE spbx_queue_agent_status SET paused=1, pause_reason_id=? WHERE id=?");
    }
    if ($upd) {
        $id = (int)$r['id'];
        if ($paused) $upd->bind_param('i', $id);
        else $upd->bind_param('ii', $reasonId, $id);
        $upd->execute();
    }
    $changed++;
}

echo $changed > 0 ? "OK\n" : "NO_ACTIVE_AGENT\n";
exit($changed > 0 ? 0 : 2);
?>