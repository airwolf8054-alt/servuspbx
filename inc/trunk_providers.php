<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/outbound_routes.php';
require_once __DIR__ . '/asterisk.php';

function spbx_trunk_allowed_providers()
{
    return ['A1', 'MAGENTA', 'EASYBELL'];
}

function spbx_trunk_provider_defaults($provider)
{
    $provider = strtoupper(trim((string)$provider));

    $defaults = [
        'A1' => [
            'label' => 'A1',
            'domain' => 'siptrunk.a1.net',
            'server_uri' => 'sip:siptrunk.a1.net',
            'transport' => 'transport-a1',
            'needs_auth_user' => false,
            'needs_contact_user' => false,
            'auth_user_mode' => 'username',
            'contact_user_mode' => 'main_number',
            'inbound_context' => 'incoming',
            'endpoint_auth_inbound' => false,
            'identify_by' => 'username,ip',
            'default_country' => 'AT',
        ],
        'MAGENTA' => [
            'label' => 'Magenta',
            'domain' => '',
            'server_uri' => '',
            'transport' => 'transport-udp',
            'needs_auth_user' => false,
            'needs_contact_user' => false,
            'auth_user_mode' => 'username',
            'contact_user_mode' => 'username',
            'inbound_context' => 'incoming',
            'endpoint_auth_inbound' => false,
            'identify_by' => 'username,ip',
            'default_country' => 'AT',
        ],
        'EASYBELL' => [
            'label' => 'easybell',
            'domain' => 'sip.easybell.de',
            'server_uri' => 'sip:sip.easybell.de',
            'transport' => 'transport-udp',
            'needs_auth_user' => true,
            'needs_contact_user' => false,
            'auth_user_mode' => 'custom',
            'contact_user_mode' => 'username',
            'inbound_context' => 'incoming',
            'endpoint_auth_inbound' => false,
            'identify_by' => 'username,ip',
            'default_country' => 'DE',
        ],
    ];

    return $defaults[$provider] ?? $defaults['A1'];
}

function spbx_trunk_normalize_provider($provider)
{
    $provider = strtoupper(trim((string)$provider));
    return in_array($provider, spbx_trunk_allowed_providers(), true) ? $provider : 'A1';
}

function spbx_trunk_normalize_e164($number, $defaultCountry = 'AT')
{
    $number = trim((string)$number);
    if ($number === '') return '';

    if ($number[0] === '+') {
        return '+' . preg_replace('/[^0-9]/', '', $number);
    }

    $digits = preg_replace('/[^0-9]/', '', $number);
    if ($digits === '') return '';

    if (strpos($digits, '00') === 0) {
        return '+' . substr($digits, 2);
    }

    if (preg_match('/^(43|49|41|423)/', $digits)) {
        return '+' . $digits;
    }

    if ($digits[0] === '0') {
        switch (strtoupper($defaultCountry)) {
            case 'DE': return '+49' . substr($digits, 1);
            case 'CH': return '+41' . substr($digits, 1);
            case 'LI': return '+423' . substr($digits, 1);
            case 'AT':
            default: return '+43' . substr($digits, 1);
        }
    }

    return '+' . $digits;
}

function spbx_trunk_country_from_number($number, $fallback = 'AT')
{
    $digits = preg_replace('/[^0-9]/', '', spbx_trunk_normalize_e164($number, $fallback));
    if (strpos($digits, '423') === 0) return 'LI';
    if (strpos($digits, '49') === 0) return 'DE';
    if (strpos($digits, '41') === 0) return 'CH';
    if (strpos($digits, '43') === 0) return 'AT';
    return strtoupper((string)$fallback ?: 'AT');
}

function spbx_trunk_endpoint_id($provider, $mainNumber)
{
    $provider = strtolower(spbx_trunk_normalize_provider($provider));
    $digits = preg_replace('/[^0-9]/', '', spbx_trunk_normalize_e164($mainNumber));
    return 'trunk-' . $provider . '-' . $digits;
}

function spbx_trunk_auto_auth_user($provider, $username, $authUser, $mainNumber)
{
    $cfg = spbx_trunk_provider_defaults($provider);
    if ($cfg['auth_user_mode'] === 'custom' && trim((string)$authUser) !== '') {
        return trim((string)$authUser);
    }
    return trim((string)$username);
}

function spbx_trunk_auto_contact_user($provider, $username, $mainNumber)
{
    $cfg = spbx_trunk_provider_defaults($provider);

    if ($cfg['contact_user_mode'] === 'main_number') {
        return spbx_trunk_normalize_e164($mainNumber, $cfg['default_country']);
    }

    return trim((string)$username);
}

function spbx_trunk_ensure_contexts($mainNumber, $emergencyProfile = null)
{
    // Contexts werden durch den Outbound-Dialplan-Generator erzeugt.
    spbx_outbound_install_schema();
    spbx_outbound_sync_from_trunks();

    if ($emergencyProfile) {
        $db = spbx_db();
        $ctx = spbx_outbound_context_from_number($mainNumber);
        $stmt = $db->prepare("UPDATE spbx_outbound_routes SET emergency_profile=? WHERE outgoing_context=?");
        if ($stmt) {
            $stmt->bind_param('ss', $emergencyProfile, $ctx);
            $stmt->execute();
        }
    }

    spbx_outbound_rebuild_dialplan();
}

function spbx_trunk_provider_hint($provider)
{
    $provider = spbx_trunk_normalize_provider($provider);
    if ($provider === 'A1') {
        return 'A1: benötigt nur Hauptnummer/Benutzername und Passwort. Auth-User und Contact-User werden automatisch gesetzt.';
    }
    if ($provider === 'MAGENTA') {
        return 'Magenta: vorerst generisch, nur notwendige SIP-Daten. Providerdetails können später verfeinert werden.';
    }
    if ($provider === 'EASYBELL') {
        return 'easybell: Auth-User kann abweichend sein und wird deshalb angezeigt.';
    }
    return '';
}
?>