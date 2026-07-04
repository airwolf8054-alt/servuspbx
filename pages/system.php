<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';

spbx_require_login();
$user = spbx_current_user();

function spbx_format_bytes($bytes)
{
    $bytes = (float)$bytes;
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return ($i === 0 ? number_format($bytes, 0, ',', '.') : number_format($bytes, 2, ',', '.')) . ' ' . $units[$i];
}

function spbx_percent($used, $total)
{
    if ($total <= 0) return 0;
    return round(($used / $total) * 100, 1);
}

function spbx_safe_cmd($command)
{
    if (!function_exists('shell_exec')) return '';
    $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
    if (in_array('shell_exec', $disabled, true)) return '';
    $out = @shell_exec($command . ' 2>/dev/null');
    return is_string($out) ? trim($out) : '';
}

function spbx_read_first_line($path)
{
    if (!is_readable($path)) return '';
    $line = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return $line ? trim((string)$line[0]) : '';
}

function spbx_meminfo()
{
    $data = [];
    if (!is_readable('/proc/meminfo')) return $data;
    foreach (@file('/proc/meminfo') as $line) {
        if (preg_match('/^([A-Za-z_()]+):\s+(\d+)\s+kB$/', trim($line), $m)) {
            $data[$m[1]] = (int)$m[2] * 1024;
        }
    }
    return $data;
}

function spbx_cpu_snapshot()
{
    if (!is_readable('/proc/stat')) return null;
    $line = spbx_read_first_line('/proc/stat');
    if (!preg_match('/^cpu\s+(.+)$/', $line, $m)) return null;
    $parts = array_map('intval', preg_split('/\s+/', trim($m[1])));
    $idle = ($parts[3] ?? 0) + ($parts[4] ?? 0);
    $total = array_sum($parts);
    return ['idle' => $idle, 'total' => $total];
}

function spbx_cpu_usage()
{
    $a = spbx_cpu_snapshot();
    if (!$a) return null;
    usleep(120000);
    $b = spbx_cpu_snapshot();
    if (!$b) return null;
    $total = $b['total'] - $a['total'];
    $idle = $b['idle'] - $a['idle'];
    if ($total <= 0) return null;
    return round((1 - ($idle / $total)) * 100, 1);
}

function spbx_cidr_to_netmask($cidr)
{
    $cidr = (int)$cidr;
    if ($cidr < 0 || $cidr > 32) return '';
    $mask = $cidr === 0 ? 0 : (0xffffffff << (32 - $cidr)) & 0xffffffff;
    return long2ip($mask);
}

function spbx_network_interfaces()
{
    $interfaces = [];
    $devs = glob('/sys/class/net/*') ?: [];

    foreach ($devs as $devPath) {
        $name = basename($devPath);
        if ($name === 'lo') continue;

        $ip = '';
        $cidr = '';
        $netmask = '';
        $addrOut = spbx_safe_cmd('ip -o -4 addr show dev ' . escapeshellarg($name));
        if ($addrOut && preg_match('/inet\s+(\d+\.\d+\.\d+\.\d+)\/(\d+)/', $addrOut, $m)) {
            $ip = $m[1];
            $cidr = $m[2];
            $netmask = spbx_cidr_to_netmask($cidr);
        }

        $gateway = '';
        $gwOut = spbx_safe_cmd('ip route show default dev ' . escapeshellarg($name));
        if ($gwOut && preg_match('/default via\s+(\d+\.\d+\.\d+\.\d+)/', $gwOut, $m)) {
            $gateway = $m[1];
        }

        $interfaces[] = [
            'name' => $name,
            'state' => spbx_read_first_line($devPath . '/operstate') ?: 'unbekannt',
            'mac' => spbx_read_first_line($devPath . '/address'),
            'ip' => $ip,
            'cidr' => $cidr,
            'netmask' => $netmask,
            'gateway' => $gateway,
            'speed' => spbx_read_first_line($devPath . '/speed'),
        ];
    }

    usort($interfaces, function ($a, $b) { return strcmp($a['name'], $b['name']); });
    return $interfaces;
}

function spbx_dns_servers()
{
    $servers = [];
    if (is_readable('/etc/resolv.conf')) {
        foreach (@file('/etc/resolv.conf', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if (preg_match('/^nameserver\s+(.+)$/', $line, $m)) {
                $servers[] = trim($m[1]);
            }
        }
    }
    return array_values(array_unique($servers));
}

$mem = spbx_meminfo();
$memTotal = $mem['MemTotal'] ?? 0;
$memAvailable = $mem['MemAvailable'] ?? (($mem['MemFree'] ?? 0) + ($mem['Buffers'] ?? 0) + ($mem['Cached'] ?? 0));
$memUsed = max(0, $memTotal - $memAvailable);
$memPercent = spbx_percent($memUsed, $memTotal);

$cpuPercent = spbx_cpu_usage();
$load = sys_getloadavg();
$cpuCount = (int)trim(spbx_safe_cmd('nproc'));
if ($cpuCount <= 0) {
    $cpuCount = preg_match_all('/^processor\s*:/m', is_readable('/proc/cpuinfo') ? file_get_contents('/proc/cpuinfo') : '', $tmp);
}

$diskPath = '/';
$diskTotal = @disk_total_space($diskPath) ?: 0;
$diskFree = @disk_free_space($diskPath) ?: 0;
$diskUsed = max(0, $diskTotal - $diskFree);
$diskPercent = spbx_percent($diskUsed, $diskTotal);

$hostname = gethostname() ?: php_uname('n');
$kernel = php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('m');
$uptimeRaw = is_readable('/proc/uptime') ? trim((string)@file_get_contents('/proc/uptime')) : '';
$uptimeSeconds = $uptimeRaw ? (int)floatval(explode(' ', $uptimeRaw)[0]) : 0;
$uptime = $uptimeSeconds > 0 ? floor($uptimeSeconds / 86400) . ' Tage, ' . gmdate('H:i:s', $uptimeSeconds % 86400) : 'unbekannt';

$interfaces = spbx_network_interfaces();
$dnsServers = spbx_dns_servers();
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>System - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/servuspbx.css">
    <style>
        .spbx-system-grid { display:grid; grid-template-columns: repeat(3, minmax(220px, 1fr)); gap:18px; margin-bottom:20px; }
        .spbx-system-metric { background:#fff; border:1px solid var(--spbx-border); border-radius:18px; padding:18px; box-shadow:var(--spbx-shadow); }
        .spbx-system-label { color:var(--spbx-muted); font-weight:900; font-size:13px; margin-bottom:8px; }
        .spbx-system-value { color:var(--spbx-navy); font-size:28px; font-weight:950; line-height:1.1; }
        .spbx-system-sub { color:var(--spbx-muted); font-size:13px; font-weight:700; margin-top:8px; }
        .spbx-progress { height:10px; background:#e5edf5; border-radius:99px; overflow:hidden; margin-top:14px; }
        .spbx-progress > span { display:block; height:100%; background:var(--spbx-accent); border-radius:99px; }
        .spbx-system-section { margin-bottom:20px; }
        .spbx-system-kv { display:grid; grid-template-columns: 220px 1fr; gap:10px 16px; margin-top:14px; }
        .spbx-system-kv div:nth-child(odd) { color:var(--spbx-muted); font-weight:900; }
        .spbx-system-kv div:nth-child(even) { color:#0f172a; font-weight:800; }
        @media (max-width: 1100px) { .spbx-system-grid { grid-template-columns:1fr; } .spbx-system-kv { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>

    <main class="spbx-main">
        <?php spbx_page_header('System', 'Auslastung, Speicher und Netzwerk'); ?>

        <div class="spbx-content">
            <div class="spbx-system-grid">
                <div class="spbx-system-metric">
                    <div class="spbx-system-label">CPU-Auslastung</div>
                    <div class="spbx-system-value"><?php echo $cpuPercent === null ? 'n/a' : spbx_h(number_format($cpuPercent, 1, ',', '.') . ' %'); ?></div>
                    <div class="spbx-system-sub">Load: <?php echo spbx_h(number_format($load[0] ?? 0, 2, ',', '.') . ' / ' . number_format($load[1] ?? 0, 2, ',', '.') . ' / ' . number_format($load[2] ?? 0, 2, ',', '.')); ?> · Kerne: <?php echo (int)$cpuCount; ?></div>
                    <div class="spbx-progress"><span style="width:<?php echo $cpuPercent === null ? 0 : min(100, max(0, $cpuPercent)); ?>%"></span></div>
                </div>

                <div class="spbx-system-metric">
                    <div class="spbx-system-label">RAM</div>
                    <div class="spbx-system-value"><?php echo spbx_h(number_format($memPercent, 1, ',', '.') . ' %'); ?></div>
                    <div class="spbx-system-sub"><?php echo spbx_h(spbx_format_bytes($memUsed)); ?> von <?php echo spbx_h(spbx_format_bytes($memTotal)); ?> belegt</div>
                    <div class="spbx-progress"><span style="width:<?php echo min(100, max(0, $memPercent)); ?>%"></span></div>
                </div>

                <div class="spbx-system-metric">
                    <div class="spbx-system-label">SSD / Systemdisk</div>
                    <div class="spbx-system-value"><?php echo spbx_h(number_format($diskPercent, 1, ',', '.') . ' %'); ?></div>
                    <div class="spbx-system-sub"><?php echo spbx_h(spbx_format_bytes($diskUsed)); ?> von <?php echo spbx_h(spbx_format_bytes($diskTotal)); ?> belegt</div>
                    <div class="spbx-progress"><span style="width:<?php echo min(100, max(0, $diskPercent)); ?>%"></span></div>
                </div>
            </div>

            <div class="spbx-card spbx-system-section">
                <div class="spbx-card-title">Systemdaten</div>
                <div class="spbx-system-kv">
                    <div>Hostname</div><div><?php echo spbx_h($hostname); ?></div>
                    <div>Betriebssystem / Kernel</div><div><?php echo spbx_h($kernel); ?></div>
                    <div>Uptime</div><div><?php echo spbx_h($uptime); ?></div>
                    <div>PHP-Version</div><div><?php echo spbx_h(PHP_VERSION); ?></div>
                    <div>DNS-Server</div><div><?php echo spbx_h($dnsServers ? implode(', ', $dnsServers) : 'nicht gefunden'); ?></div>
                </div>
            </div>

            <div class="spbx-card spbx-system-section">
                <div class="spbx-card-title">Netzwerkkarten</div>
                <div class="spbx-card-muted">IP-Adresse, Subnetz, Gateway, DNS und Link-Status der erkannten Interfaces.</div>
                <br>
                <div class="spbx-table-wrap">
                    <table class="spbx-table">
                        <thead>
                        <tr>
                            <th>Interface</th>
                            <th>Status</th>
                            <th>MAC</th>
                            <th>IP</th>
                            <th>Subnetz</th>
                            <th>Gateway</th>
                            <th>DNS</th>
                            <th>Speed</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!$interfaces): ?>
                            <tr><td colspan="8">Keine Netzwerkkarten gefunden.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($interfaces as $iface): ?>
                            <tr>
                                <td><?php echo spbx_h($iface['name']); ?></td>
                                <td><?php echo spbx_h($iface['state']); ?></td>
                                <td><?php echo spbx_h($iface['mac'] ?: '-'); ?></td>
                                <td><?php echo spbx_h($iface['ip'] ?: '-'); ?></td>
                                <td><?php echo spbx_h($iface['netmask'] ? $iface['netmask'] . ' /' . $iface['cidr'] : '-'); ?></td>
                                <td><?php echo spbx_h($iface['gateway'] ?: '-'); ?></td>
                                <td><?php echo spbx_h($dnsServers ? implode(', ', $dnsServers) : '-'); ?></td>
                                <td><?php echo spbx_h(($iface['speed'] && is_numeric($iface['speed'])) ? $iface['speed'] . ' Mbit/s' : '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
