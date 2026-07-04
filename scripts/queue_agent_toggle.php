#!/usr/bin/env php
<?php
require_once __DIR__ . '/../inc/queues.php';

$endpoint = preg_replace('/[^0-9A-Za-z_\-]/', '', (string)($argv[1] ?? ''));
if ($endpoint === '') exit(1);

$db = spbx_db();
$res = spbx_queue_agent_allowed_queues($endpoint);
$changed = 0;

while ($q = $res->fetch_assoc()) {
    $qid = (int)$q['id'];
    $qname = 'spbxq_' . preg_replace('/[^0-9A-Za-z_\-]/', '', (string)$q['queue_number']);
    $iface = 'Local/' . $endpoint . '@' . ((string)($q['context'] ?? '') ?: 'internal') . '/n';

    $stmt = $db->prepare("SELECT * FROM spbx_queue_agent_status WHERE endpoint_id=? AND queue_id=? LIMIT 1");
    $stmt->bind_param('si', $endpoint, $qid);
    $stmt->execute();
    $st = $stmt->get_result()->fetch_assoc();

    $logged = $st ? (int)$st['logged_in'] : 0;

    if ($logged) {
        spbx_ast_cli('queue remove member ' . $iface . ' from ' . $qname);
        $upd = $db->prepare("INSERT INTO spbx_queue_agent_status (endpoint_id, queue_id, logged_in, paused) VALUES (?, ?, 0, 0) ON DUPLICATE KEY UPDATE logged_in=0, paused=0, pause_reason_id=NULL");
    } else {
        spbx_ast_cli('queue add member ' . $iface . ' to ' . $qname);
        $upd = $db->prepare("INSERT INTO spbx_queue_agent_status (endpoint_id, queue_id, logged_in, paused) VALUES (?, ?, 1, 0) ON DUPLICATE KEY UPDATE logged_in=1, paused=0, pause_reason_id=NULL");
    }

    if ($upd) {
        $upd->bind_param('si', $endpoint, $qid);
        $upd->execute();
    }
    $changed++;
}

echo $changed > 0 ? "OK\n" : "NOT_ALLOWED\n";
exit($changed > 0 ? 0 : 2);
?>