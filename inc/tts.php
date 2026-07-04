<?php
// ServusPBX Text-to-Speech helper (Piper, local/offline)
// Zentraler TTS-Baustein: Text rein -> MP3 raus.

require_once __DIR__ . '/db.php';

function spbx_tts_install_schema()
{
    $db = spbx_db();
    $db->query("CREATE TABLE IF NOT EXISTS spbx_settings (
        setting_key varchar(100) NOT NULL,
        setting_value text DEFAULT NULL,
        description varchar(255) DEFAULT NULL,
        PRIMARY KEY (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $defaults = [
        'tts_engine' => ['piper', 'TTS Engine'],
        'tts_voice' => ['de_DE-thorsten-medium', 'Standard-Piper-Stimme für neue Ansagen'],
        'tts_piper_bin' => ['/usr/local/servuspbx/tts/piper/piper', 'Pfad zur Piper Binary'],
        'tts_voice_dir' => ['/usr/local/servuspbx/tts/voices', 'Verzeichnis für Piper Stimmen'],
        'tts_output_dir' => ['/var/lib/asterisk/sounds/custom/tts', 'Ausgabeverzeichnis für generierte MP3-Dateien'],
        'tts_ffmpeg_bin' => ['/usr/bin/ffmpeg', 'Pfad zu ffmpeg'],
        'tts_audio_format' => ['mp3', 'TTS Ausgabeformat'],
        'tts_length_scale' => ['1.00', 'Piper Sprechgeschwindigkeit: kleiner=schneller, größer=langsamer'],
        'tts_noise_scale' => ['0.667', 'Piper Ausdruck/Variation'],
        'tts_noise_w' => ['0.80', 'Piper Aussprachevariation'],
        'tts_sentence_silence' => ['0.20', 'Pause zwischen Sätzen in Sekunden'],
        'tts_volume_db' => ['0.0', 'MP3 Lautstärke in dB'],
    ];

    foreach ($defaults as $key => $row) {
        $stmt = $db->prepare("INSERT INTO spbx_settings (setting_key, setting_value, description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE description=VALUES(description)");
        if ($stmt) {
            $stmt->bind_param('sss', $key, $row[0], $row[1]);
            $stmt->execute();
        }
    }
}

function spbx_tts_setting($key, $default = '')
{
    spbx_tts_install_schema();
    $db = spbx_db();
    $stmt = $db->prepare("SELECT setting_value FROM spbx_settings WHERE setting_key=? LIMIT 1");
    if (!$stmt) return $default;
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && ($row = $res->fetch_assoc())) {
        return (string)$row['setting_value'];
    }
    return $default;
}

function spbx_tts_set_setting($key, $value, $description = '')
{
    spbx_tts_install_schema();
    $db = spbx_db();
    $stmt = $db->prepare("INSERT INTO spbx_settings (setting_key, setting_value, description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), description=IF(VALUES(description)='', description, VALUES(description))");
    if (!$stmt) return false;
    $stmt->bind_param('sss', $key, $value, $description);
    return $stmt->execute();
}

function spbx_tts_voices()
{
    // ServusPBX installiert automatisch mehrere bewährte Piper-Stimmen.
    // Hinweis: Offizielle Piper-Modelle für de_AT/de_CH sind derzeit nicht zuverlässig verfügbar,
    // deshalb bleiben wir bei stabilen deutschen Modellen und halten die Oberfläche simpel.
    return [
        'de_DE-thorsten-medium' => [
            'label' => 'Deutsch - Thorsten medium',
            'file' => 'de_DE-thorsten-medium.onnx',
            'lang' => 'Deutsch',
            'builtin_install' => true,
        ],
        'de_DE-thorsten-low' => [
            'label' => 'Deutsch - Thorsten low',
            'file' => 'de_DE-thorsten-low.onnx',
            'lang' => 'Deutsch',
            'builtin_install' => true,
        ],
        'de_DE-eva_k-x_low' => [
            'label' => 'Deutsch - Eva',
            'file' => 'de_DE-eva_k-x_low.onnx',
            'lang' => 'Deutsch',
            'builtin_install' => true,
        ],
        'de_DE-karlsson-low' => [
            'label' => 'Deutsch - Karlsson',
            'file' => 'de_DE-karlsson-low.onnx',
            'lang' => 'Deutsch',
            'builtin_install' => true,
        ],
        'de_DE-kerstin-low' => [
            'label' => 'Deutsch - Kerstin',
            'file' => 'de_DE-kerstin-low.onnx',
            'lang' => 'Deutsch',
            'builtin_install' => true,
        ],
    ];
}

function spbx_tts_current_voice_info()
{
    $voices = spbx_tts_voices();
    $voice = spbx_tts_setting('tts_voice', 'de_DE-thorsten-medium');
    if (!isset($voices[$voice])) $voice = 'de_DE-thorsten-medium';
    return [$voice, $voices[$voice]];
}

function spbx_tts_model_path($voiceKey = null)
{
    $voices = spbx_tts_voices();
    if ($voiceKey === null || !isset($voices[$voiceKey])) {
        list($voiceKey, $info) = spbx_tts_current_voice_info();
    } else {
        $info = $voices[$voiceKey];
    }
    $dir = rtrim(spbx_tts_setting('tts_voice_dir', '/usr/local/servuspbx/tts/voices'), '/');
    return $dir . '/' . $info['file'];
}

function spbx_tts_model_config_path($voiceKey = null)
{
    return spbx_tts_model_path($voiceKey) . '.json';
}

function spbx_tts_voice_installed($voiceKey)
{
    return is_file(spbx_tts_model_path($voiceKey)) && is_file(spbx_tts_model_config_path($voiceKey));
}

function spbx_tts_installed_voice_count()
{
    $cnt = 0;
    foreach (array_keys(spbx_tts_voices()) as $key) {
        if (spbx_tts_voice_installed($key)) $cnt++;
    }
    return $cnt;
}

function spbx_tts_status()
{
    $piper = spbx_tts_setting('tts_piper_bin', '/usr/local/servuspbx/tts/piper/piper');
    $ffmpeg = spbx_tts_setting('tts_ffmpeg_bin', '/usr/bin/ffmpeg');
    $model = spbx_tts_model_path();
    $cfg = spbx_tts_model_config_path();
    $out = spbx_tts_setting('tts_output_dir', '/var/lib/asterisk/sounds/custom/tts');
    return [
        'piper_bin' => $piper,
        'piper_ok' => is_file($piper) && is_executable($piper),
        'ffmpeg_bin' => $ffmpeg,
        'ffmpeg_ok' => is_file($ffmpeg) && is_executable($ffmpeg),
        'model' => $model,
        'model_ok' => is_file($model),
        'model_config' => $cfg,
        'model_config_ok' => is_file($cfg),
        'output_dir' => $out,
        'output_ok' => is_dir($out) && is_writable($out),
        'voice_count' => spbx_tts_installed_voice_count(),
    ];
}

function spbx_tts_ready()
{
    $s = spbx_tts_status();
    return $s['piper_ok'] && $s['ffmpeg_ok'] && $s['model_ok'] && $s['model_config_ok'] && $s['output_ok'];
}

function spbx_tts_ready_label()
{
    return spbx_tts_ready() ? 'bereit' : 'nicht bereit';
}

function spbx_tts_installer_script()
{
    return dirname(__DIR__) . '/scripts/install_piper_tts.sh';
}

function spbx_tts_safe_basename($name)
{
    $name = strtolower((string)$name);
    $name = preg_replace('/[^a-z0-9_\-]+/', '_', $name);
    $name = trim($name, '_-');
    if ($name === '') $name = 'tts';
    return $name;
}

function spbx_tts_output_path($basename)
{
    $dir = rtrim(spbx_tts_setting('tts_output_dir', '/var/lib/asterisk/sounds/custom/tts'), '/');
    return $dir . '/' . spbx_tts_safe_basename($basename) . '.mp3';
}


function spbx_tts_basename_from_path($path)
{
    $path = trim((string)$path);
    if ($path === '') return '';
    $base = basename($path);
    if (strtolower(substr($base, -4)) === '.mp3') {
        $base = substr($base, 0, -4);
    }
    return spbx_tts_safe_basename($base);
}

function spbx_tts_audio_url_for_file($path)
{
    $base = spbx_tts_basename_from_path($path);
    if ($base === '') return '';
    $full = spbx_tts_output_path($base);
    if (!is_file($full) || !is_readable($full)) return '';
    return 'tts_audio.php?file=' . rawurlencode($base) . '&v=' . @filemtime($full);
}

function spbx_tts_ensure_output_dir()
{
    $dir = rtrim(spbx_tts_setting('tts_output_dir', '/var/lib/asterisk/sounds/custom/tts'), '/');
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    @chmod($dir, 0775);
    @chown($dir, 'asterisk');
    @chgrp($dir, 'asterisk');
    return is_dir($dir) && is_writable($dir);
}


function spbx_tts_float_setting($key, $default, $min, $max)
{
    $raw = str_replace(',', '.', spbx_tts_setting($key, (string)$default));
    $val = is_numeric($raw) ? (float)$raw : (float)$default;
    if ($val < $min) $val = $min;
    if ($val > $max) $val = $max;
    return $val;
}


function spbx_tts_default_voice()
{
    return 'de_DE-thorsten-medium';
}

function spbx_tts_default_options()
{
    return [
        'length_scale' => 1.00,
        'noise_scale' => 0.667,
        'noise_w' => 0.80,
        'sentence_silence' => 0.20,
        'volume_db' => 0.0,
    ];
}

function spbx_tts_current_options()
{
    return [
        'length_scale' => spbx_tts_float_setting('tts_length_scale', 1.00, 0.50, 2.00),
        'noise_scale' => spbx_tts_float_setting('tts_noise_scale', 0.667, 0.00, 1.50),
        'noise_w' => spbx_tts_float_setting('tts_noise_w', 0.80, 0.00, 2.00),
        'sentence_silence' => spbx_tts_float_setting('tts_sentence_silence', 0.20, 0.00, 2.00),
        'volume_db' => spbx_tts_float_setting('tts_volume_db', 0.0, -12.0, 12.0),
    ];
}

function spbx_tts_normalize_options($options = null)
{
    $base = spbx_tts_current_options();
    if (!is_array($options)) return $base;
    foreach ($base as $k => $v) {
        if (array_key_exists($k, $options)) {
            $raw = str_replace(',', '.', (string)$options[$k]);
            if (is_numeric($raw)) $base[$k] = (float)$raw;
        }
    }
    $base['length_scale'] = max(0.50, min(2.00, $base['length_scale']));
    $base['noise_scale'] = max(0.00, min(1.50, $base['noise_scale']));
    $base['noise_w'] = max(0.00, min(2.00, $base['noise_w']));
    $base['sentence_silence'] = max(0.00, min(2.00, $base['sentence_silence']));
    $base['volume_db'] = max(-12.0, min(12.0, $base['volume_db']));
    return $base;
}

function spbx_tts_status_for_voice($voiceKey = null)
{
    $s = spbx_tts_status();
    if ($voiceKey !== null && isset(spbx_tts_voices()[$voiceKey])) {
        $s['model'] = spbx_tts_model_path($voiceKey);
        $s['model_config'] = spbx_tts_model_config_path($voiceKey);
        $s['model_ok'] = is_file($s['model']);
        $s['model_config_ok'] = is_file($s['model_config']);
    }
    return $s;
}

function spbx_tts_generate_mp3($text, $basename, &$error = '', $voiceKey = null, $options = null)
{
    $text = trim((string)$text);
    if ($text === '') {
        $error = 'Kein TTS-Text angegeben.';
        return false;
    }

    if (!spbx_tts_ensure_output_dir()) {
        $error = 'TTS-Ausgabeverzeichnis ist nicht beschreibbar.';
        return false;
    }

    $status = spbx_tts_status_for_voice($voiceKey);
    $opts = spbx_tts_normalize_options($options);
    if (!$status['piper_ok']) { $error = 'Piper ist noch nicht installiert.'; return false; }
    if (!$status['ffmpeg_ok']) { $error = 'ffmpeg ist noch nicht installiert.'; return false; }
    if (!$status['model_ok'] || !$status['model_config_ok']) { $error = 'Die ausgewählte Piper-Stimme ist noch nicht installiert.'; return false; }

    $mp3 = spbx_tts_output_path($basename);
    $tmpWav = tempnam(sys_get_temp_dir(), 'spbx_tts_') . '.wav';
    $tmpTxt = tempnam(sys_get_temp_dir(), 'spbx_tts_') . '.txt';
    file_put_contents($tmpTxt, $text . PHP_EOL);

    $cmd1 = escapeshellarg($status['piper_bin']) .
        ' --model ' . escapeshellarg($status['model']) .
        ' --config ' . escapeshellarg($status['model_config']) .
        ' --output_file ' . escapeshellarg($tmpWav) .
        ' --length_scale ' . escapeshellarg(sprintf('%.3F', $opts['length_scale'])) .
        ' --noise_scale ' . escapeshellarg(sprintf('%.3F', $opts['noise_scale'])) .
        ' --noise_w ' . escapeshellarg(sprintf('%.3F', $opts['noise_w'])) .
        ' --sentence_silence ' . escapeshellarg(sprintf('%.3F', $opts['sentence_silence'])) .
        ' < ' . escapeshellarg($tmpTxt) . ' 2>&1';
    exec($cmd1, $out1, $rc1);
    if ($rc1 !== 0 || !is_file($tmpWav) || filesize($tmpWav) < 100) {
        @unlink($tmpTxt); @unlink($tmpWav);
        $error = 'Piper konnte die WAV-Datei nicht erzeugen: ' . implode("\n", array_slice($out1, -5));
        return false;
    }

    $cmd2 = escapeshellarg($status['ffmpeg_bin']) .
        ' -y -i ' . escapeshellarg($tmpWav) .
        ' -ar 8000 -ac 1 -filter:a ' . escapeshellarg('volume=' . sprintf('%.2F', $opts['volume_db']) . 'dB') .
        ' -codec:a libmp3lame -b:a 64k ' . escapeshellarg($mp3) . ' 2>&1';
    exec($cmd2, $out2, $rc2);
    @unlink($tmpTxt); @unlink($tmpWav);
    if ($rc2 !== 0 || !is_file($mp3) || filesize($mp3) < 100) {
        $error = 'ffmpeg konnte die MP3-Datei nicht erzeugen: ' . implode("\n", array_slice($out2, -5));
        return false;
    }

    @chmod($mp3, 0644);
    @chown($mp3, 'asterisk');
    @chgrp($mp3, 'asterisk');
    return $mp3;
}

function spbx_tts_generate_if_text($text, $basename, $currentFile = '', &$error = '', $voiceKey = null, $options = null)
{
    $text = trim((string)$text);
    if ($text === '') return (string)$currentFile;
    $mp3 = spbx_tts_generate_mp3($text, $basename, $error, $voiceKey, $options);
    if ($mp3 === false) return (string)$currentFile;
    return $mp3;
}

function spbx_tts_valid_voice($voiceKey)
{
    $voices = spbx_tts_voices();
    return isset($voices[(string)$voiceKey]) ? (string)$voiceKey : spbx_tts_setting('tts_voice', 'de_DE-thorsten-medium');
}

function spbx_tts_announcement_voice_key($area, $id, $slot)
{
    $area = preg_replace('/[^a-z0-9_\-]+/i', '_', (string)$area);
    $slot = preg_replace('/[^a-z0-9_\-]+/i', '_', (string)$slot);
    return 'tts_voice_' . strtolower($area) . '_' . (int)$id . '_' . strtolower($slot);
}

function spbx_tts_get_announcement_voice($area, $id, $slot)
{
    $default = spbx_tts_setting('tts_voice', 'de_DE-thorsten-medium');
    if ((int)$id <= 0) return spbx_tts_valid_voice($default);
    return spbx_tts_valid_voice(spbx_tts_setting(spbx_tts_announcement_voice_key($area, $id, $slot), $default));
}

function spbx_tts_set_announcement_voice($area, $id, $slot, $voiceKey)
{
    if ((int)$id <= 0) return false;
    $voiceKey = spbx_tts_valid_voice($voiceKey);
    return spbx_tts_set_setting(spbx_tts_announcement_voice_key($area, $id, $slot), $voiceKey, 'Stimme für einzelne Ansage');
}

function spbx_tts_upload_mp3($fieldName, $basename, &$error = '')
{
    if (empty($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) return null;
    $f = $_FILES[$fieldName];
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($f['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $error = 'MP3-Upload fehlgeschlagen.';
        return false;
    }
    $name = (string)($f['name'] ?? '');
    if (!preg_match('/\.mp3$/i', $name)) {
        $error = 'Bitte eine MP3-Datei hochladen.';
        return false;
    }
    if (!spbx_tts_ensure_output_dir()) {
        $error = 'TTS-Ausgabeverzeichnis ist nicht beschreibbar.';
        return false;
    }
    $target = spbx_tts_output_path($basename);
    $tmp = (string)($f['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        $error = 'Upload-Datei ist ungültig.';
        return false;
    }
    if (!@move_uploaded_file($tmp, $target)) {
        $error = 'MP3 konnte nicht gespeichert werden.';
        return false;
    }
    @chmod($target, 0644);
    @chown($target, 'asterisk');
    @chgrp($target, 'asterisk');
    return $target;
}

function spbx_tts_announcement_setting_key($area, $id, $slot, $name)
{
    $area = preg_replace('/[^a-z0-9_\-]+/i', '_', (string)$area);
    $slot = preg_replace('/[^a-z0-9_\-]+/i', '_', (string)$slot);
    $name = preg_replace('/[^a-z0-9_\-]+/i', '_', (string)$name);
    return 'tts_ann_' . strtolower($area) . '_' . (int)$id . '_' . strtolower($slot) . '_' . strtolower($name);
}

function spbx_tts_get_announcement_type($area, $id, $slot, $fallback = 'tts')
{
    $type = spbx_tts_setting(spbx_tts_announcement_setting_key($area, $id, $slot, 'type'), $fallback);
    return $type === 'mp3' ? 'mp3' : 'tts';
}

function spbx_tts_set_announcement_type($area, $id, $slot, $type)
{
    if ((int)$id <= 0) return false;
    $type = ($type === 'mp3') ? 'mp3' : 'tts';
    return spbx_tts_set_setting(spbx_tts_announcement_setting_key($area, $id, $slot, 'type'), $type, 'Ansagetyp TTS oder MP3');
}

function spbx_tts_get_announcement_options($area, $id, $slot)
{
    $base = spbx_tts_current_options();
    if ((int)$id <= 0) return $base;
    foreach (array_keys($base) as $k) {
        $raw = spbx_tts_setting(spbx_tts_announcement_setting_key($area, $id, $slot, $k), '');
        if ($raw !== '' && is_numeric(str_replace(',', '.', $raw))) {
            $base[$k] = (float)str_replace(',', '.', $raw);
        }
    }
    return spbx_tts_normalize_options($base);
}

function spbx_tts_set_announcement_options($area, $id, $slot, $options)
{
    if ((int)$id <= 0) return false;
    $opts = spbx_tts_normalize_options($options);
    foreach ($opts as $k => $v) {
        spbx_tts_set_setting(spbx_tts_announcement_setting_key($area, $id, $slot, $k), sprintf('%.3F', $v), 'Option für einzelne Ansage');
    }
    return true;
}

function spbx_tts_post_options($prefix)
{
    $names = ['length_scale','noise_scale','noise_w','sentence_silence','volume_db'];
    $opts = [];
    foreach ($names as $n) {
        if (isset($_POST[$prefix . '_' . $n])) {
            $opts[$n] = (string)$_POST[$prefix . '_' . $n];
        }
    }
    return spbx_tts_normalize_options($opts);
}

function spbx_tts_html($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function spbx_tts_voice_select_html($name, $selected)
{
    $voices = spbx_tts_voices();
    $html = '<select class="spbx-select" name="' . spbx_tts_html($name) . '">';
    foreach ($voices as $key => $info) {
        $installed = spbx_tts_voice_installed($key);
        $sel = ((string)$selected === (string)$key) ? ' selected' : '';
        $dis = !$installed ? ' disabled' : '';
        $label = $info['label'] . (!$installed ? ' (nicht installiert)' : '');
        $html .= '<option value="' . spbx_tts_html($key) . '"' . $sel . $dis . '>' . spbx_tts_html($label) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

function spbx_tts_render_options_html($prefix, $options)
{
    $o = spbx_tts_normalize_options($options);
    $items = [
        'length_scale' => ['Sprechgeschwindigkeit', '0.50', '2.00', '0.05', 'kleiner = schneller, größer = langsamer'],
        'noise_scale' => ['Ausdruck / Variation', '0', '1.50', '0.05', 'höher = lebendiger, niedriger = neutraler'],
        'noise_w' => ['Aussprachevariation', '0', '2.00', '0.05', 'normalerweise 0.8 belassen'],
        'sentence_silence' => ['Pause zwischen Sätzen', '0', '2.00', '0.05', 'für Telefonansagen oft 0.2–0.5 Sekunden'],
        'volume_db' => ['Lautstärke', '-12', '12', '0.5', 'MP3-Pegel nach der Erzeugung'],
    ];
    $html = '<div class="spbx-ann-options-grid">';
    foreach ($items as $key => $meta) {
        [$label,$min,$max,$step,$hint] = $meta;
        $value = $o[$key];
        $suffix = $key === 'sentence_silence' ? ' s' : ($key === 'volume_db' ? ' dB' : '');
        $html .= '<div class="spbx-ann-option"><div class="spbx-ann-option-head"><span>' . spbx_tts_html($label) . '</span><span data-ann-out="' . spbx_tts_html($prefix . '_' . $key) . '">' . spbx_tts_html($value . $suffix) . '</span></div>';
        $html .= '<input type="range" name="' . spbx_tts_html($prefix . '_' . $key) . '" min="' . spbx_tts_html($min) . '" max="' . spbx_tts_html($max) . '" step="' . spbx_tts_html($step) . '" value="' . spbx_tts_html($value) . '">';
        $html .= '<small>' . spbx_tts_html($hint) . '</small></div>';
    }
    $html .= '</div>';
    return $html;
}

function spbx_tts_render_announcement_widget($args)
{
    $title = $args['title'] ?? 'Ansage';
    $typeName = $args['type_name'] ?? 'announcement_type';
    $typeValue = ($args['type_value'] ?? 'tts') === 'mp3' ? 'mp3' : 'tts';
    $textName = $args['text_name'] ?? 'tts_text';
    $textValue = $args['text_value'] ?? '';
    $voiceName = $args['voice_name'] ?? 'tts_voice';
    $voiceValue = $args['voice_value'] ?? spbx_tts_setting('tts_voice', 'de_DE-thorsten-medium');
    $uploadName = $args['upload_name'] ?? 'mp3_upload';
    $fileValue = $args['file_value'] ?? '';
    $prefix = $args['options_prefix'] ?? $textName;
    $options = $args['options'] ?? spbx_tts_current_options();
    $placeholder = $args['placeholder'] ?? 'Ansagetext eingeben...';
    $uid = 'ann_' . substr(md5($typeName . $textName . $uploadName), 0, 10);
    $audioUrl = spbx_tts_audio_url_for_file($fileValue);
    $fileLabel = $fileValue ?: 'noch keine Datei';

    $html = '<div class="spbx-ann-widget" id="' . spbx_tts_html($uid) . '">';
    $html .= '<div class="spbx-ann-title">' . spbx_tts_html($title) . '</div>';
    $html .= '<div class="spbx-field"><label>Ansagetyp</label><select class="spbx-select spbx-ann-type" name="' . spbx_tts_html($typeName) . '">';
    $html .= '<option value="tts"' . ($typeValue === 'tts' ? ' selected' : '') . '>Text-to-Speech</option>';
    $html .= '<option value="mp3"' . ($typeValue === 'mp3' ? ' selected' : '') . '>Eigene MP3-Datei hochladen</option>';
    $html .= '</select></div>';

    $html .= '<div class="spbx-ann-panel spbx-ann-panel-tts">';
    $html .= '<div class="spbx-field"><label>Text-to-Speech Text</label><textarea class="spbx-textarea" name="' . spbx_tts_html($textName) . '" rows="4" placeholder="' . spbx_tts_html($placeholder) . '">' . spbx_tts_html($textValue) . '</textarea></div>';
    $html .= '<div class="spbx-field"><label>Stimme für diese Ansage</label>' . spbx_tts_voice_select_html($voiceName, $voiceValue) . '</div>';
    $html .= spbx_tts_render_options_html($prefix, $options);
    $defaultVoice = spbx_tts_setting('tts_voice', spbx_tts_default_voice());
    $defaultOptions = spbx_tts_current_options();
    $html .= '<div class="spbx-ann-actions" style="margin-top:10px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;"><button class="spbx-btn spbx-ann-reset-defaults" type="button" data-voice-name="' . spbx_tts_html($voiceName) . '" data-default-voice="' . spbx_tts_html($defaultVoice) . '" data-prefix="' . spbx_tts_html($prefix) . '" data-length-scale="' . spbx_tts_html($defaultOptions['length_scale']) . '" data-noise-scale="' . spbx_tts_html($defaultOptions['noise_scale']) . '" data-noise-w="' . spbx_tts_html($defaultOptions['noise_w']) . '" data-sentence-silence="' . spbx_tts_html($defaultOptions['sentence_silence']) . '" data-volume-db="' . spbx_tts_html($defaultOptions['volume_db']) . '">Standardwerte wiederherstellen</button><span class="spbx-card-muted spbx-ann-reset-note">setzt Stimme und TTS-Optionen dieser Ansage zurück, Text bleibt erhalten</span></div>';
    $html .= '<div class="spbx-card-muted">Mit <strong>Speichern &amp; MP3 erzeugen</strong> wird die Ansage gespeichert und die MP3 automatisch neu geschrieben.</div>';
    $html .= '</div>';

    $html .= '<div class="spbx-ann-panel spbx-ann-panel-mp3">';
    $html .= '<div class="spbx-field"><label>Aktuelle Datei</label><input class="spbx-input" value="' . spbx_tts_html($fileLabel) . '" readonly></div>';
    $html .= '<div class="spbx-field"><label>MP3-Datei hochladen</label><input class="spbx-input" type="file" name="' . spbx_tts_html($uploadName) . '" accept="audio/mpeg,.mp3"><div class="spbx-card-muted">Eine hochgeladene MP3 überschreibt die bestehende Datei dieser Ansage beim Speichern.</div></div>';
    $html .= '</div>';

    $html .= '<div class="spbx-ann-actions" style="margin-top:12px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">';
    $html .= '<button class="spbx-btn spbx-btn-primary" type="submit">Speichern &amp; MP3 erzeugen</button>';
    if ($audioUrl !== '') {
        $html .= '<a class="spbx-btn" href="' . spbx_tts_html($audioUrl) . '" target="_blank" rel="noopener">Testhören</a>';
        $html .= '<audio controls preload="none" src="' . spbx_tts_html($audioUrl) . '" style="height:36px;max-width:100%;"></audio>';
    } else {
        $html .= '<span class="spbx-card-muted">Testhören ist verfügbar, sobald eine MP3 vorhanden ist.</span>';
    }
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

?>
