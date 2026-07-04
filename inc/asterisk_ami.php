<?php
require_once __DIR__ . '/db.php';

function spbx_ami_setting($key, $default = '') {
    $db = spbx_db();
    $stmt = $db->prepare("SELECT setting_value FROM spbx_settings WHERE setting_key=? LIMIT 1");
    if (!$stmt) return $default;
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? (string)$row['setting_value'] : $default;
}

function spbx_ami_command($action, $params = [], $timeout = 2.0) {
    $host = spbx_ami_setting('ami_host', '127.0.0.1');
    $port = (int)spbx_ami_setting('ami_port', '5038');
    $user = spbx_ami_setting('ami_user', 'ServusPBX');
    $secret = spbx_ami_setting('ami_secret', 'Phonesystem2020!');

    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$fp) throw new RuntimeException('AMI Verbindung fehlgeschlagen: ' . $errstr);
    stream_set_timeout($fp, (int)ceil($timeout), 0);
    fgets($fp, 4096);

    $actionId = 'spbx_' . bin2hex(random_bytes(6));
    fwrite($fp, "Action: Login\r\nActionID: {$actionId}_login\r\nUsername: {$user}\r\nSecret: {$secret}\r\nEvents: off\r\n\r\n");

    $cmd = "Action: {$action}\r\nActionID: {$actionId}\r\n";
    foreach ($params as $k => $v) $cmd .= $k . ": " . $v . "\r\n";
    fwrite($fp, $cmd . "\r\n");
    fwrite($fp, "Action: Logoff\r\nActionID: {$actionId}_logoff\r\n\r\n");

    $raw = '';
    $start = microtime(true);
    while (!feof($fp) && (microtime(true) - $start) < $timeout) {
        $line = fgets($fp, 8192);
        if ($line === false) break;
        $raw .= $line;
        if (strpos($raw, 'Event: CoreShowChannelsComplete') !== false) break;
    }
    fclose($fp);
    return $raw;
}

function spbx_ami_parse_messages($raw) {
    $out = [];
    foreach (preg_split("/\r?\n\r?\n/", trim((string)$raw)) as $msg) {
        $item = [];
        foreach (preg_split("/\r?\n/", trim($msg)) as $line) {
            if (strpos($line, ':') === false) continue;
            [$k,$v] = explode(':', $line, 2);
            $item[trim($k)] = trim($v);
        }
        if ($item) $out[] = $item;
    }
    return $out;
}

function spbx_ami_duration($s) {
    $s = max(0, (int)$s);
    $h = intdiv($s, 3600); $m = intdiv($s % 3600, 60); $sec = $s % 60;
    return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $sec) : sprintf('%02d:%02d', $m, $sec);
}

function spbx_ami_party($channel, $fallback = '') {
    $channel = (string)$channel;
    $fallback = trim((string)$fallback);

    if (preg_match('/^PJSIP\\/(trunk[^\\-]*)-/', $channel)) {
        return ($fallback !== '' && $fallback !== '<unknown>') ? $fallback : 'Trunk';
    }

    if (preg_match('/^PJSIP\\/([^\\/\\-]+)-/', $channel, $m)) return $m[1];
    if (preg_match('/^Local\\/([^@;]+)/', $channel, $m)) return $m[1];

    return ($fallback !== '' && $fallback !== '<unknown>') ? $fallback : $channel;
}

function spbx_ami_is_internal_party($value) {
    return (bool)preg_match('/^[0-9]{2,5}$/', (string)$value);
}

function spbx_ami_is_trunk_channel($channel) {
    return (bool)preg_match('/^PJSIP\\/trunk/i', (string)$channel);
}

function spbx_ami_active_calls() {
    $messages = spbx_ami_parse_messages(spbx_ami_command('CoreShowChannels', [], 2.0));
    $channels = [];
    foreach ($messages as $m) {
        if (($m['Event'] ?? '') !== 'CoreShowChannel') continue;
        $ch = (string)($m['Channel'] ?? '');
        if ($ch === '' || strpos($ch, 'ARI/') === 0 || strpos($ch, 'CBAnn/') === 0) continue;

        $state = (string)($m['ChannelStateDesc'] ?? '');
        $app = (string)($m['Application'] ?? '');
        $bridge = (string)($m['BridgeId'] ?? '');
        $interesting = in_array($state, ['Up','Ring','Ringing','Dialing','Progress'], true) || $bridge !== '' || in_array($app, ['Dial','AppDial','Queue'], true);
        if (!$interesting) continue;

        $to = (string)($m['ConnectedLineNum'] ?? '');
        if ($to === '' || $to === '<unknown>') $to = (string)($m['Exten'] ?? '-');
        if ($to === '' || $to === '<unknown>') $to = '-';

        $status = ($state === 'Up' || $bridge !== '') ? 'Verbunden' : (($state === 'Ring' || $state === 'Ringing') ? 'Klingelt' : (($state === 'Dialing' || $state === 'Progress') ? 'Wählt' : 'Aktiv'));

        $channels[] = [
            'channel'=>$ch,
            'from'=>spbx_ami_party($ch, $m['CallerIDNum'] ?? ''),
            'to'=>$to,
            'duration_seconds'=>(int)($m['Duration'] ?? 0),
            'duration'=>spbx_ami_duration($m['Duration'] ?? 0),
            'status'=>$status,
            'bridge_id'=>$bridge
        ];
    }

    $bridges = []; $standalone = [];
    foreach ($channels as $c) {
        if ($c['bridge_id'] !== '') $bridges[$c['bridge_id']][] = $c;
        else $standalone[] = $c;
    }

    $calls = [];
    foreach ($bridges as $bridge => $items) {
        usort($items, fn($a,$b)=>$b['duration_seconds'] <=> $a['duration_seconds']);
        if (count($items) >= 2) {
            $a = $items[0];
            $b = $items[1];

            $trunk = spbx_ami_is_trunk_channel($a['channel']) ? $a : (spbx_ami_is_trunk_channel($b['channel']) ? $b : null);
            $peer = null;
            if ($trunk !== null) {
                $peer = ($trunk === $a) ? $b : $a;
            }

            if ($trunk !== null && $peer !== null) {
                // Eingehend: Trunk-Ziel ist eine interne Nebenstelle.
                // Anzeige: externe Rufnummer -> Nebenstelle.
                if (spbx_ami_is_internal_party($trunk['to'])) {
                    $from = $trunk['from'];
                    $to = $trunk['to'];
                } else {
                    // Ausgehend: Nebenstelle -> externe Zielrufnummer.
                    $from = spbx_ami_is_internal_party($peer['from']) ? $peer['from'] : $trunk['from'];
                    $to = ($trunk['to'] !== '-' && $trunk['to'] !== '') ? $trunk['to'] : $peer['to'];
                }
            } else {
                $from = $a['from'];
                $to = $b['from'];
            }

            $calls[] = ['from'=>$from, 'to'=>$to, 'duration'=>spbx_ami_duration(max($a['duration_seconds'],$b['duration_seconds'])), 'status'=>'Verbunden'];
        } else {
            $calls[] = ['from'=>$items[0]['from'], 'to'=>$items[0]['to'], 'duration'=>$items[0]['duration'], 'status'=>$items[0]['status']];
        }
    }
    foreach ($standalone as $c) $calls[] = ['from'=>$c['from'], 'to'=>$c['to'], 'duration'=>$c['duration'], 'status'=>$c['status']];
    usort($calls, fn($a,$b)=>strcmp($a['from'],$b['from']));
    return $calls;
}
?>