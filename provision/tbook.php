<?php
require_once __DIR__ . '/../inc/db.php';
header('Content-Type: text/xml; charset=utf-8');

$db = spbx_db();

function x($v) {
    return htmlspecialchars((string)$v, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<tbook complete=\"true\" e=\"2\">\n";

$res = $db->query("SHOW TABLES LIKE 'spbx_phonebook'");
if ($res && $res->num_rows > 0) {
    $cols = [];
    $cr = $db->query("SHOW COLUMNS FROM spbx_phonebook");
    if ($cr) {
        while ($c = $cr->fetch_assoc()) $cols[] = $c['Field'];
    }

    $first = in_array('first_name', $cols, true) ? 'first_name' : (in_array('firstname', $cols, true) ? 'firstname' : '');
    $last = in_array('last_name', $cols, true) ? 'last_name' : (in_array('lastname', $cols, true) ? 'lastname' : '');
    $num = in_array('number', $cols, true) ? 'number' : (in_array('phone_number', $cols, true) ? 'phone_number' : '');
    $type = in_array('type', $cols, true) ? 'type' : '';
    $active = in_array('active', $cols, true) ? 'active' : '';

    if ($num !== '') {
        $where = $active !== '' ? " WHERE `$active`=1 " : "";
        $order = [];
        if ($last !== '') $order[] = "`$last`";
        if ($first !== '') $order[] = "`$first`";
        $order[] = "`$num`";
        $rr = $db->query("SELECT * FROM spbx_phonebook" . $where . " ORDER BY " . implode(", ", $order));
        if ($rr) {
            while ($r = $rr->fetch_assoc()) {
                $n = (string)($r[$num] ?? '');
                if (trim($n) === '') continue;
                $t = $type !== '' ? (string)($r[$type] ?? 'office') : 'office';
                if (trim($t) === '') $t = 'office';
                echo "  <item context=\"active\" type=\"" . x($t) . "\">\n";
                echo "    <first_name>" . x($first !== '' ? ($r[$first] ?? '') : '') . "</first_name>\n";
                echo "    <last_name>" . x($last !== '' ? ($r[$last] ?? '') : '') . "</last_name>\n";
                echo "    <number>" . x($n) . "</number>\n";
                echo "  </item>\n";
            }
        }
    }
}
echo "</tbook>\n";
