<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';
require_once __DIR__ . '/../inc/tts.php';

spbx_require_admin();
spbx_tts_install_schema();

function tts_h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function tts_badge($ok, $good = 'OK', $bad = 'fehlt') {
    return '<span class="spbx-pill ' . ($ok ? 'success' : 'danger') . '">' . tts_h($ok ? $good : $bad) . '</span>';
}
function tts_post_float($name, $default, $min, $max) {
    $raw = str_replace(',', '.', (string)($_POST[$name] ?? $default));
    $val = is_numeric($raw) ? (float)$raw : (float)$default;
    if ($val < $min) $val = $min;
    if ($val > $max) $val = $max;
    return $val;
}

$message = '';
$error = '';
$installOutput = '';
$audioUrl = '';
$voices = spbx_tts_voices();
$centralOptions = spbx_tts_current_options();
$factoryOptions = spbx_tts_default_options();
$factoryVoice = spbx_tts_default_voice();
list($currentVoice,) = spbx_tts_current_voice_info();

$testText = spbx_tts_setting('tts_test_text', 'Dies ist eine Testansage von ServusPBX.');
$testVoice = spbx_tts_setting('tts_test_voice', $currentVoice);
if (!isset($voices[$testVoice])) $testVoice = $currentVoice;
$testOptions = [
    'length_scale' => spbx_tts_float_setting('tts_test_length_scale', $centralOptions['length_scale'], 0.50, 2.00),
    'noise_scale' => spbx_tts_float_setting('tts_test_noise_scale', $centralOptions['noise_scale'], 0.00, 1.50),
    'noise_w' => spbx_tts_float_setting('tts_test_noise_w', $centralOptions['noise_w'], 0.00, 2.00),
    'sentence_silence' => spbx_tts_float_setting('tts_test_sentence_silence', $centralOptions['sentence_silence'], 0.00, 2.00),
    'volume_db' => spbx_tts_float_setting('tts_test_volume_db', $centralOptions['volume_db'], -12.0, 12.0),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = (string)($_POST['form_action'] ?? 'save');

    if ($formAction === 'install') {
        $script = spbx_tts_installer_script();
        if (!is_file($script)) {
            $error = 'Installationsscript wurde nicht gefunden.';
        } else {
            @chmod($script, 0755);
            $cmd = 'sudo -n ' . escapeshellarg($script) . ' 2>&1';
            exec($cmd, $out, $rc);
            $installOutput = implode("\n", array_slice($out, -40));
            if ($rc === 0) {
                spbx_tts_ensure_output_dir();
                $message = 'Piper/ffmpeg Installation wurde ausgeführt. Bitte Status prüfen.';
            } else {
                $error = 'Installation konnte nicht automatisch ausgeführt werden. Details siehe Installationsausgabe. Sudo-Rechte und Internetzugriff prüfen.';
            }
        }
    } elseif ($formAction === 'install_voices') {
        $script = spbx_tts_installer_script();
        if (!is_file($script)) {
            $error = 'Installationsscript wurde nicht gefunden.';
        } else {
            @chmod($script, 0755);
            $cmd = 'sudo -n ' . escapeshellarg($script) . ' --voices-only 2>&1';
            exec($cmd, $out, $rc);
            $installOutput = implode("\n", array_slice($out, -60));
            if ($rc === 0) {
                $message = 'Vordefinierte Stimmen wurden nachinstalliert. Bitte Status prüfen.';
            } else {
                $error = 'Stimmen konnten nicht automatisch nachinstalliert werden. Details siehe Ausgabe.';
            }
        }
    } elseif ($formAction === 'prepare') {
        spbx_tts_ensure_output_dir();
        $message = 'TTS-Verzeichnisse wurden vorbereitet.';
    } elseif ($formAction === 'save_voice') {
        $voice = trim((string)($_POST['tts_voice'] ?? 'de_DE-thorsten-medium'));
        if (!isset($voices[$voice])) $voice = 'de_DE-thorsten-medium';
        if (!spbx_tts_voice_installed($voice)) {
            $error = 'Diese Stimme ist noch nicht installiert.';
        } else {
            $opts = [
                'length_scale' => tts_post_float('tts_length_scale', 1.00, 0.50, 2.00),
                'noise_scale' => tts_post_float('tts_noise_scale', 0.667, 0.00, 1.50),
                'noise_w' => tts_post_float('tts_noise_w', 0.80, 0.00, 2.00),
                'sentence_silence' => tts_post_float('tts_sentence_silence', 0.20, 0.00, 2.00),
                'volume_db' => tts_post_float('tts_volume_db', 0.0, -12.0, 12.0),
            ];
            spbx_tts_set_setting('tts_voice', $voice, 'Standard-Piper-Stimme für neue Ansagen');
            spbx_tts_set_setting('tts_length_scale', sprintf('%.3F', $opts['length_scale']), 'Piper Sprechgeschwindigkeit');
            spbx_tts_set_setting('tts_noise_scale', sprintf('%.3F', $opts['noise_scale']), 'Piper Ausdruck/Variation');
            spbx_tts_set_setting('tts_noise_w', sprintf('%.3F', $opts['noise_w']), 'Piper Aussprachevariation');
            spbx_tts_set_setting('tts_sentence_silence', sprintf('%.3F', $opts['sentence_silence']), 'Pause zwischen Sätzen');
            spbx_tts_set_setting('tts_volume_db', sprintf('%.2F', $opts['volume_db']), 'MP3 Lautstärke');
            $message = 'Standard-Stimme und Optionen gespeichert.';
            $currentVoice = $voice;
            $centralOptions = spbx_tts_current_options();
        }
    } elseif ($formAction === 'test') {
        $testText = trim((string)($_POST['test_text'] ?? $testText));
        if ($testText === '') $testText = spbx_tts_setting('tts_test_text', 'Dies ist eine Testansage von ServusPBX.');
        $testVoice = trim((string)($_POST['test_voice'] ?? $testVoice));
        if (!isset($voices[$testVoice])) $testVoice = $currentVoice;
        $testOptions = [
            'length_scale' => tts_post_float('test_length_scale', $centralOptions['length_scale'], 0.50, 2.00),
            'noise_scale' => tts_post_float('test_noise_scale', $centralOptions['noise_scale'], 0.00, 1.50),
            'noise_w' => tts_post_float('test_noise_w', $centralOptions['noise_w'], 0.00, 2.00),
            'sentence_silence' => tts_post_float('test_sentence_silence', $centralOptions['sentence_silence'], 0.00, 2.00),
            'volume_db' => tts_post_float('test_volume_db', $centralOptions['volume_db'], -12.0, 12.0),
        ];
        spbx_tts_set_setting('tts_test_text', $testText, 'Letzter TTS-Testtext');
        spbx_tts_set_setting('tts_test_voice', $testVoice, 'Letzte Teststimme');
        spbx_tts_set_setting('tts_test_length_scale', sprintf('%.3F', $testOptions['length_scale']), 'Letzte Testgeschwindigkeit');
        spbx_tts_set_setting('tts_test_noise_scale', sprintf('%.3F', $testOptions['noise_scale']), 'Letzte Testvariation');
        spbx_tts_set_setting('tts_test_noise_w', sprintf('%.3F', $testOptions['noise_w']), 'Letzte Testaussprachevariation');
        spbx_tts_set_setting('tts_test_sentence_silence', sprintf('%.3F', $testOptions['sentence_silence']), 'Letzte Testsatzpause');
        spbx_tts_set_setting('tts_test_volume_db', sprintf('%.2F', $testOptions['volume_db']), 'Letzte Testlautstärke');

        $err = '';
        $file = spbx_tts_generate_mp3($testText, 'test_tts', $err, $testVoice, $testOptions);
        if ($file) {
            $message = 'Testansage wurde erzeugt und kann direkt hier abgespielt werden.';
            $audioUrl = 'tts_audio.php?file=test_tts&v=' . urlencode((string)@filemtime($file));
        } else {
            $error = $err ?: 'Test-MP3 konnte nicht erzeugt werden.';
        }
    }
}

$status = spbx_tts_status();
$voices = spbx_tts_voices();
list($currentVoice,) = spbx_tts_current_voice_info();
$centralOptions = spbx_tts_current_options();
$ready = spbx_tts_ready();
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Text-to-Speech - ServusPBX Professional</title>
<link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
<link rel="stylesheet" href="../css/servuspbx.css">
<style>
.tts-status-grid{display:grid;grid-template-columns:repeat(4,minmax(150px,1fr));gap:12px;margin-top:14px}.tts-status-box{border:1px solid rgba(148,163,184,.25);border-radius:14px;padding:14px;background:rgba(255,255,255,.03)}.tts-status-box strong{display:block;margin-bottom:8px}.spbx-pill{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-weight:700;font-size:12px}.spbx-pill.success{background:#dcfce7;color:#166534}.spbx-pill.danger{background:#fee2e2;color:#991b1b}.spbx-pill.warn{background:#fef3c7;color:#92400e}.tts-voice-row{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 0;border-bottom:1px solid rgba(148,163,184,.2)}.tts-voice-row:last-child{border-bottom:0}.tts-muted{opacity:.75;font-size:13px}.tts-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}.tts-pre{white-space:pre-wrap;max-height:260px;overflow:auto;background:#0f172a;color:#e5e7eb;border-radius:12px;padding:12px;font-size:12px}.tts-options-grid{display:grid;grid-template-columns:repeat(2,minmax(220px,1fr));gap:14px;margin-top:14px}.tts-option input[type=range]{width:100%}.tts-option-head{display:flex;justify-content:space-between;gap:12px;font-weight:700}.tts-option small{display:block;opacity:.7;margin-top:2px}.tts-select{width:100%;max-width:420px}
</style>
</head>
<body>
<div class="spbx-app">
<?php spbx_sidebar(); ?>
<main class="spbx-main">
<?php spbx_page_header('Text-to-Speech', 'Piper-Stimmen, Standardwerte und Testansage'); ?>
<div class="spbx-content">
<?php if ($message): ?><div class="spbx-alert success"><?php echo tts_h($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="spbx-alert error"><?php echo nl2br(tts_h($error)); ?></div><?php endif; ?>

<div class="spbx-card" style="padding:18px;">
    <div class="spbx-card-title">Status</div>
    <p class="spbx-card-muted">Diese Seite richtet Piper, verfügbare Stimmen und Standardwerte ein. Jede Ansage kann in Eingehende Regeln und Queues ihre eigene Stimme oder eine manuell hochgeladene MP3 verwenden.</p>
    <div class="tts-status-grid">
        <div class="tts-status-box"><strong>Piper</strong><?php echo tts_badge($status['piper_ok'], 'installiert', 'nicht installiert'); ?></div>
        <div class="tts-status-box"><strong>ffmpeg</strong><?php echo tts_badge($status['ffmpeg_ok'], 'installiert', 'nicht installiert'); ?></div>
        <div class="tts-status-box"><strong>Stimmen</strong><?php echo $status['voice_count'] > 0 ? '<span class="spbx-pill success">' . (int)$status['voice_count'] . ' installiert</span>' : '<span class="spbx-pill danger">keine installiert</span>'; ?></div>
        <div class="tts-status-box"><strong>MP3-Ausgabe</strong><?php echo tts_badge($status['output_ok'], 'bereit', 'nicht bereit'); ?></div>
    </div>

    <div class="tts-actions">
        <?php if (!$status['piper_ok'] || !$status['ffmpeg_ok'] || $status['voice_count'] < 1): ?>
            <form method="post" onsubmit="return confirm('Piper und ffmpeg jetzt installieren?');">
                <input type="hidden" name="form_action" value="install">
                <button class="spbx-button primary" type="submit">Piper installieren</button>
            </form>
        <?php endif; ?>
        <form method="post" onsubmit="return confirm('Fehlende vordefinierte Stimmen jetzt nachinstallieren?');">
            <input type="hidden" name="form_action" value="install_voices">
            <button class="spbx-button primary" type="submit" <?php echo (!$status['piper_ok'] || !$status['ffmpeg_ok']) ? 'disabled' : ''; ?>>Stimmen nachinstallieren</button>
        </form>
        <form method="post">
            <input type="hidden" name="form_action" value="prepare">
            <button class="spbx-button secondary" type="submit">Verzeichnisse vorbereiten</button>
        </form>
    </div>

    <?php if ($installOutput): ?>
        <h3>Installationsausgabe</h3>
        <pre class="tts-pre"><?php echo tts_h($installOutput); ?></pre>
    <?php endif; ?>
</div>

<form method="post" class="spbx-card" style="padding:18px;margin-top:16px;">
    <input type="hidden" name="form_action" value="save_voice">
    <div class="spbx-card-title">Standard-Stimme & Optionen</div>
    <p class="spbx-card-muted">Diese Werte werden als Standard für neue Ansagen verwendet. Bestehende Ansagen können jeweils eine eigene Stimme haben.</p>

    <?php foreach ($voices as $key => $info): ?>
        <?php $installed = spbx_tts_voice_installed($key); ?>
        <label class="tts-voice-row">
            <span>
                <input type="radio" name="tts_voice" value="<?php echo tts_h($key); ?>" <?php echo $currentVoice === $key ? 'checked' : ''; ?> <?php echo !$installed ? 'disabled' : ''; ?>>
                <strong><?php echo tts_h($info['label']); ?></strong>
                <span class="tts-muted"> · <?php echo tts_h($info['lang']); ?></span>
            </span>
            <?php echo $installed ? '<span class="spbx-pill success">bereit</span>' : '<span class="spbx-pill warn">nicht installiert</span>'; ?>
        </label>
    <?php endforeach; ?>

    <div class="tts-options-grid">
        <div class="tts-option"><div class="tts-option-head"><span>Sprechgeschwindigkeit</span><span data-out="tts_length_scale"><?php echo tts_h($centralOptions['length_scale']); ?></span></div><input type="range" name="tts_length_scale" min="0.50" max="2.00" step="0.05" value="<?php echo tts_h($centralOptions['length_scale']); ?>"><small>kleiner = schneller, größer = langsamer</small></div>
        <div class="tts-option"><div class="tts-option-head"><span>Ausdruck / Variation</span><span data-out="tts_noise_scale"><?php echo tts_h($centralOptions['noise_scale']); ?></span></div><input type="range" name="tts_noise_scale" min="0" max="1.50" step="0.05" value="<?php echo tts_h($centralOptions['noise_scale']); ?>"><small>höher = lebendiger, niedriger = neutraler</small></div>
        <div class="tts-option"><div class="tts-option-head"><span>Aussprachevariation</span><span data-out="tts_noise_w"><?php echo tts_h($centralOptions['noise_w']); ?></span></div><input type="range" name="tts_noise_w" min="0" max="2.00" step="0.05" value="<?php echo tts_h($centralOptions['noise_w']); ?>"><small>normalerweise 0.8 belassen</small></div>
        <div class="tts-option"><div class="tts-option-head"><span>Pause zwischen Sätzen</span><span data-out="tts_sentence_silence"><?php echo tts_h($centralOptions['sentence_silence']); ?> s</span></div><input type="range" name="tts_sentence_silence" min="0" max="2.00" step="0.05" value="<?php echo tts_h($centralOptions['sentence_silence']); ?>"><small>für Telefonansagen oft 0.2–0.5 Sekunden</small></div>
        <div class="tts-option"><div class="tts-option-head"><span>Lautstärke</span><span data-out="tts_volume_db"><?php echo tts_h($centralOptions['volume_db']); ?> dB</span></div><input type="range" name="tts_volume_db" min="-12" max="12" step="0.5" value="<?php echo tts_h($centralOptions['volume_db']); ?>"><small>MP3-Pegel nach der Erzeugung</small></div>
    </div>

    <div class="tts-actions">
        <button class="spbx-button secondary" type="button" id="tts_reset_standard">Standardwerte wiederherstellen</button>
        <button class="spbx-button primary" type="submit" <?php echo $status['voice_count'] < 1 ? 'disabled' : ''; ?>>Standard-Stimme & Optionen speichern</button>
    </div>
</form>

<form method="post" class="spbx-card" style="padding:18px;margin-top:16px;">
    <input type="hidden" name="form_action" value="test">
    <div class="spbx-card-title">Test</div>
    <p class="spbx-card-muted">Die Testauswahl dient nur zum Probehören und ändert keine Ansagen.</p>
    <div class="spbx-field">
        <label>Teststimme</label>
        <select class="spbx-input tts-select" name="test_voice">
            <?php foreach ($voices as $key => $info): $installed = spbx_tts_voice_installed($key); ?>
                <option value="<?php echo tts_h($key); ?>" <?php echo $testVoice === $key ? 'selected' : ''; ?> <?php echo !$installed ? 'disabled' : ''; ?>><?php echo tts_h($info['label'] . (!$installed ? ' (nicht installiert)' : '')); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="spbx-field">
        <label>Testtext</label>
        <textarea class="spbx-textarea" name="test_text" rows="3"><?php echo tts_h($testText); ?></textarea>
    </div>

    <div class="tts-options-grid">
        <div class="tts-option"><div class="tts-option-head"><span>Sprechgeschwindigkeit</span><span data-out="test_length_scale"><?php echo tts_h($testOptions['length_scale']); ?></span></div><input type="range" name="test_length_scale" min="0.50" max="2.00" step="0.05" value="<?php echo tts_h($testOptions['length_scale']); ?>"><small>kleiner = schneller, größer = langsamer</small></div>
        <div class="tts-option"><div class="tts-option-head"><span>Ausdruck / Variation</span><span data-out="test_noise_scale"><?php echo tts_h($testOptions['noise_scale']); ?></span></div><input type="range" name="test_noise_scale" min="0" max="1.50" step="0.05" value="<?php echo tts_h($testOptions['noise_scale']); ?>"><small>zum Vergleichen der Stimme</small></div>
        <div class="tts-option"><div class="tts-option-head"><span>Aussprachevariation</span><span data-out="test_noise_w"><?php echo tts_h($testOptions['noise_w']); ?></span></div><input type="range" name="test_noise_w" min="0" max="2.00" step="0.05" value="<?php echo tts_h($testOptions['noise_w']); ?>"><small>normalerweise 0.8 belassen</small></div>
        <div class="tts-option"><div class="tts-option-head"><span>Pause zwischen Sätzen</span><span data-out="test_sentence_silence"><?php echo tts_h($testOptions['sentence_silence']); ?> s</span></div><input type="range" name="test_sentence_silence" min="0" max="2.00" step="0.05" value="<?php echo tts_h($testOptions['sentence_silence']); ?>"><small>wirkt besonders bei längeren Texten</small></div>
        <div class="tts-option"><div class="tts-option-head"><span>Lautstärke</span><span data-out="test_volume_db"><?php echo tts_h($testOptions['volume_db']); ?> dB</span></div><input type="range" name="test_volume_db" min="-12" max="12" step="0.5" value="<?php echo tts_h($testOptions['volume_db']); ?>"><small>MP3-Pegel der Testansage</small></div>
    </div>

    <div class="tts-actions"><button class="spbx-button secondary" type="button" id="tts_reset_test">Testwerte auf Standard setzen</button><button class="spbx-button secondary" type="submit" <?php echo !$ready ? 'disabled' : ''; ?>>Testansage erzeugen & anhören</button></div>
    <?php if ($audioUrl): ?>
        <div class="spbx-card" style="margin-top:14px;padding:14px;background:rgba(148,163,184,.08);">
            <strong>Testansage</strong>
            <div class="spbx-card-muted" style="margin:6px 0 10px;">Die erzeugte MP3 wird direkt aus ServusPBX abgespielt.</div>
            <audio controls preload="none" style="width:100%;max-width:620px;">
                <source src="<?php echo tts_h($audioUrl); ?>" type="audio/mpeg">
                Dein Browser kann dieses Audioelement nicht abspielen.
            </audio>
        </div>
    <?php endif; ?>
    <?php if (!$ready): ?><div class="spbx-card-muted" style="margin-top:8px;">Test ist verfügbar, sobald Piper, ffmpeg, Stimme und Ausgabeverzeichnis bereit sind.</div><?php endif; ?>
</form>

<div class="spbx-card" style="padding:18px;margin-top:16px;">
    <div class="spbx-card-title">Hinweis</div>
    <p class="spbx-card-muted">Die bestehenden Textfelder bleiben unverändert: Eingehende Regeln und Queues. Beim Speichern wird aus dem Text automatisch eine MP3 erzeugt und im Callflow verwendet.</p>
</div>
</div>
</main>
</div>
<script>
(function(){
    function updateRange(input){
        var out = document.querySelector('[data-out="' + input.name + '"]');
        if (!out) return;
        var suffix = input.name.indexOf('silence') !== -1 ? ' s' : (input.name.indexOf('volume') !== -1 ? ' dB' : '');
        out.textContent = input.value + suffix;
    }
    document.querySelectorAll('input[type="range"]').forEach(function(input){
        updateRange(input);
        input.addEventListener('input', function(){ updateRange(input); });
    });
    var ta = document.querySelector('textarea[name="test_text"]');
    if (!ta) return;
    var key = 'servuspbx_tts_test_text';
    if (!ta.value && localStorage.getItem(key)) ta.value = localStorage.getItem(key);
    ta.addEventListener('input', function(){ localStorage.setItem(key, ta.value); });
    var form = ta.closest('form');
    if (form) form.addEventListener('submit', function(){ localStorage.setItem(key, ta.value); });

    function setRange(name, value){
        var inp = document.querySelector('[name="' + name + '"]');
        if (!inp) return;
        inp.value = value;
        updateRange(inp);
    }
    function setRadio(name, value){
        var inp = document.querySelector('[name="' + name + '"][value="' + value + '"]');
        if (inp && !inp.disabled) inp.checked = true;
    }
    function setSelect(name, value){
        var sel = document.querySelector('[name="' + name + '"]');
        if (!sel) return;
        var opt = sel.querySelector('option[value="' + value + '"]');
        if (opt && !opt.disabled) sel.value = value;
    }
    var factoryVoice = <?php echo json_encode($factoryVoice); ?>;
    var centralVoice = <?php echo json_encode($currentVoice); ?>;
    var factoryOptions = <?php echo json_encode($factoryOptions); ?>;
    var centralOptions = <?php echo json_encode($centralOptions); ?>;
    var resetStandard = document.getElementById('tts_reset_standard');
    if (resetStandard) resetStandard.addEventListener('click', function(){
        setRadio('tts_voice', factoryVoice);
        setRange('tts_length_scale', factoryOptions.length_scale);
        setRange('tts_noise_scale', factoryOptions.noise_scale);
        setRange('tts_noise_w', factoryOptions.noise_w);
        setRange('tts_sentence_silence', factoryOptions.sentence_silence);
        setRange('tts_volume_db', factoryOptions.volume_db);
    });
    var resetTest = document.getElementById('tts_reset_test');
    if (resetTest) resetTest.addEventListener('click', function(){
        setSelect('test_voice', centralVoice);
        setRange('test_length_scale', centralOptions.length_scale);
        setRange('test_noise_scale', centralOptions.noise_scale);
        setRange('test_noise_w', centralOptions.noise_w);
        setRange('test_sentence_silence', centralOptions.sentence_silence);
        setRange('test_volume_db', centralOptions.volume_db);
    });
})();
</script>
</body>
</html>
