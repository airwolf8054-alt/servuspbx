<?php
require_once __DIR__ . '/../inc/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 'Off');

header('Content-Type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';

function dx($value)
{
    return htmlspecialchars((string)$value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

$db = spbx_db();

echo '<settings>';
echo '<tbook>';

$res = $db->query("
    SELECT id, company, lastname, firstname, number, mobile
    FROM spbx_phonebook
    ORDER BY lastname, firstname, company, id
");

$index = 1;

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $name = trim((string)($row['firstname'] ?? '') . ' ' . (string)($row['lastname'] ?? ''));
        if ($name === '') $name = trim((string)($row['company'] ?? ''));
        if ($name === '') $name = 'Kontakt ' . $index;

        $numbers = [
            ['number' => trim((string)($row['number'] ?? '')), 'type' => 'office'],
            ['number' => trim((string)($row['mobile'] ?? '')), 'type' => 'mobile'],
        ];

        foreach ($numbers as $n) {
            if ($n['number'] === '') continue;

            echo "<item context='active' type='none' fav='false' index='" . $index . "'>";
            echo '<number>' . dx($n['number']) . '</number>';
            echo '<name>' . dx($name) . '</name>';
            echo '<number_type>' . dx($n['type']) . '</number_type>';
            echo '</item>';

            $index++;
        }
    }
}

echo '</tbook>';
echo '</settings>';
?>