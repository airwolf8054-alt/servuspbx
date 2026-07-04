<?php
function spbx_base_path()
{
    return (strpos($_SERVER['PHP_SELF'] ?? '', '/pages/') !== false) ? '../' : '';
}

function spbx_logo_path()
{
    return spbx_base_path() . 'assets/servuspbx-medical-logo.svg';
}

function spbx_url($path)
{
    return spbx_base_path() . ltrim($path, '/');
}

function spbx_nav_active($file)
{
    $current = basename($_SERVER['PHP_SELF']);
    if ($file === 'call_rules.php' && in_array($current, ['call_rules.php', 'outbound_routes.php'], true)) {
        return 'active';
    }
    return $current === $file ? 'active' : '';
}


function spbx_icon($name)
{
    $icons = [
        'phone-incoming' => '<svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.1 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.77.63 2.61a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6.27 6.27l1.29-1.29a2 2 0 0 1 2.11-.45c.84.3 1.72.51 2.61.63A2 2 0 0 1 22 16.92z"></path><path d="m15 9-4 4"></path><path d="M15 13h-4V9"></path></svg>',
        'phone-outgoing' => '<svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.1 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.77.63 2.61a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6.27 6.27l1.29-1.29a2 2 0 0 1 2.11-.45c.84.3 1.72.51 2.61.63A2 2 0 0 1 22 16.92z"></path><path d="M11 9h4v4"></path><path d="m15 9-4 4"></path></svg>',
        'triangle-alert' => '<svg viewBox="0 0 24 24"><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>',
        'phone' => '<svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.1 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.77.63 2.61a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6.27 6.27l1.29-1.29a2 2 0 0 1 2.11-.45c.84.3 1.72.51 2.61.63A2 2 0 0 1 22 16.92z"></path></svg>',
        'clock' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>',
        'layout-dashboard' => '<svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="8" rx="1.5"></rect><rect x="14" y="3" width="7" height="5" rx="1.5"></rect><rect x="14" y="12" width="7" height="9" rx="1.5"></rect><rect x="3" y="15" width="7" height="6" rx="1.5"></rect></svg>',
        'activity' => '<svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 8L9 4l-3 8H2"></path></svg>',
        'users' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
        'phone-forwarded' => '<svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.1 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.77.63 2.61a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6.27 6.27l1.29-1.29a2 2 0 0 1 2.11-.45c.84.3 1.72.51 2.61.63A2 2 0 0 1 22 16.92z"></path><path d="M14 3h7v7"></path><path d="m14 10 7-7"></path></svg>',
        'users-round' => '<svg viewBox="0 0 24 24"><path d="M18 21a8 8 0 0 0-16 0"></path><circle cx="10" cy="8" r="5"></circle><path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"></path></svg>',
        'radio' => '<svg viewBox="0 0 24 24"><path d="M4.9 19.1C1 15.2 1 8.8 4.9 4.9"></path><path d="M7.8 16.2a6 6 0 0 1 0-8.5"></path><circle cx="12" cy="12" r="2"></circle><path d="M16.2 7.8a6 6 0 0 1 0 8.5"></path><path d="M19.1 4.9c3.9 3.9 3.9 10.2 0 14.1"></path></svg>',
        'send' => '<svg viewBox="0 0 24 24"><path d="m22 2-7 20-4-9-9-4Z"></path><path d="M22 2 11 13"></path></svg>',
        'workflow' => '<svg viewBox="0 0 24 24"><rect x="3" y="3" width="6" height="6" rx="1"></rect><rect x="15" y="15" width="6" height="6" rx="1"></rect><path d="M9 6h4a3 3 0 0 1 3 3v6"></path><path d="M6 9v2a3 3 0 0 0 3 3h6"></path></svg>',
        'calendar-days' => '<svg viewBox="0 0 24 24"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M3 10h18"></path><path d="M8 14h.01"></path><path d="M12 14h.01"></path><path d="M16 14h.01"></path><path d="M8 18h.01"></path><path d="M12 18h.01"></path></svg>',
        'book-user' => '<svg viewBox="0 0 24 24"><path d="M15 13a3 3 0 1 0-6 0"></path><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H17"></path><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"></path></svg>',
        'clipboard-list' => '<svg viewBox="0 0 24 24"><rect x="8" y="2" width="8" height="4" rx="1"></rect><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><path d="M9 12h6"></path><path d="M9 16h6"></path><path d="M8 12h.01"></path><path d="M8 16h.01"></path></svg>',
        'network' => '<svg viewBox="0 0 24 24"><rect x="16" y="16" width="6" height="6" rx="1"></rect><rect x="2" y="16" width="6" height="6" rx="1"></rect><rect x="9" y="2" width="6" height="6" rx="1"></rect><path d="M12 8v4"></path><path d="M5 16v-2a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v2"></path></svg>',
        'shield-check' => '<svg viewBox="0 0 24 24"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67 0C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.2 1.2 0 0 1 1.52 0C14.5 3.8 17 5 19 5a1 1 0 0 1 1 1z"></path><path d="m9 12 2 2 4-4"></path></svg>',
        'settings' => '<svg viewBox="0 0 24 24"><path d="M12.22 2h-.44a2 2 0 0 0-2 2l-.2 1.36a2 2 0 0 1-1 1.42l-1.27.73a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 1 0 1.76l-.22.38a2 2 0 0 0 .73 2.73l1.27.73a2 2 0 0 1 1 1.42l.2 1.36a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2l.2-1.36a2 2 0 0 1 1-1.42l1.27-.73a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 1 0-1.76l.22-.38a2 2 0 0 0-.73-2.73l-1.27-.73a2 2 0 0 1-1-1.42L14.22 4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
        'headphones' => '<svg viewBox="0 0 24 24"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"></path></svg>',
        'download' => '<svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><path d="M7 10l5 5 5-5"></path><path d="M12 15V3"></path></svg>',
        'list' => '<svg viewBox="0 0 24 24"><path d="M8 6h13"></path><path d="M8 12h13"></path><path d="M8 18h13"></path><path d="M3 6h.01"></path><path d="M3 12h.01"></path><path d="M3 18h.01"></path></svg>',
        'power' => '<svg viewBox="0 0 24 24"><path d="M12 2v10"></path><path d="M18.4 6.6a9 9 0 1 1-12.8 0"></path></svg>',
    ];

    return $icons[$name] ?? $icons['settings'];
}


function spbx_sidebar()
{
    $user = $_SESSION['spbx_user'] ?? null;
    $role = $user['role'] ?? 'user';

    $adminItems = [
        ['section', 'Übersicht'],
        ['dashboard.php', 'Dashboard', 'layout-dashboard'],

        ['section', 'Telefonie'],
        ['pages/call_rules.php', 'Anrufregeln', 'workflow'],
        ['pages/extensions.php', 'Nebenstellen', 'users'],
        ['pages/trunk_a1.php', 'SIP-Trunks', 'phone-forwarded'],
        ['pages/ring_groups.php', 'Rufgruppen', 'users-round'],
        ['pages/queues.php', 'Queues', 'headphones'],
        ['pages/dect.php', 'DECT', 'radio'],
        ['pages/phonebook.php', 'Telefonbuch', 'book-user'],
        ['pages/holidays.php', 'Feiertage', 'calendar-days'],

        ['section', 'System'],
        ['pages/system.php', 'System', 'settings'],
        ['pages/network.php', 'Netzwerk', 'network'],
        ['pages/security_2fa.php', '2FA Sicherheit', 'shield-check'],
        ['pages/audit_log.php', 'Audit-Log', 'clipboard-list'],
        ['pages/tts.php', 'Text-to-Speech', 'headphones'],
    ];

    $userItems = [
        ['section', 'Übersicht'],
        ['dashboard.php', 'Dashboard', 'layout-dashboard'],
        ['section', 'Telefonie'],
        ['pages/phonebook.php', 'Telefonbuch', 'book-user'],
        ['pages/call_history.php', 'Anrufliste', 'list'],
    ];

    $items = $role === 'admin' ? $adminItems : $userItems;
    ?>
    <aside class="spbx-sidebar">
        <div class="spbx-sidebar-logo">
            <div class="spbx-wordmark"><span class="spbx-wordmark-servus">Servus</span><span class="spbx-wordmark-pbx">PBX</span><sup class="spbx-wordmark-registered">®</sup></div>
            <div class="spbx-wordmark-sub">Professional Edition</div>
        </div>
        <nav class="spbx-nav">
            <?php foreach ($items as $item): ?>
                <?php if (($item[0] ?? '') === 'section'): ?>
                    <div class="spbx-nav-section"><?php echo spbx_h($item[1]); ?></div>
                    <?php continue; ?>
                <?php endif; ?>
                <?php [$url, $label, $icon] = $item; ?>
                <a class="<?php echo spbx_nav_active(basename($url)); ?>" href="<?php echo spbx_h(spbx_url($url)); ?>">
                    <span class="spbx-nav-icon"><?php echo spbx_icon($icon); ?></span>
                    <strong><?php echo spbx_h($label); ?></strong>
                </a>
            <?php endforeach; ?>
            <a href="<?php echo spbx_h(spbx_url('logout.php')); ?>">
                <span class="spbx-nav-icon"><?php echo spbx_icon('power'); ?></span>
                <strong>Abmelden</strong>
            </a>
        </nav>
    </aside>
    <?php
}

function spbx_page_header($title, $subtitle = '')
{
    $user = $_SESSION['spbx_user'] ?? null;
    ?>
    <header class="spbx-topbar">
        <div>
            <div class="spbx-page-title"><?php echo spbx_h($title); ?></div>
            <?php if ($subtitle): ?>
                <div class="spbx-page-subtitle"><?php echo spbx_h($subtitle); ?></div>
            <?php endif; ?>
        </div>
        <div class="spbx-page-subtitle">
            <?php echo spbx_h($user['display_name'] ?? ''); ?>
        </div>
    </header>
    <?php
}
?>