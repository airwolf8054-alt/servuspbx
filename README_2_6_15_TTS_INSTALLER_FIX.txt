ServusPBX Professional 2.6.15 - TTS Installer Fix

Änderung:
- Das fehlende Installationsscript scripts/install_piper_tts.sh wurde ergänzt.
- Die TTS-Seite verwendet weiterhin den bestehenden Pfad über inc/tts.php.
- Der Installer legt /usr/local/servuspbx/tts an, installiert ffmpeg/espeak-ng-data und lädt Piper + deutsche Thorsten-Stimme.

Sudoers-Eintrag:
www-data ALL=(root) NOPASSWD: /var/www/html/scripts/install_piper_tts.sh

Test:
sudo -u www-data sudo -n /var/www/html/scripts/install_piper_tts.sh
