ServusPBX Medical 1.2.22 - Snom Context-Key Beschriftung

Korrigiert:
- Die 4 Context-Keys unter dem Display waren korrekt belegt,
  zeigten aber nur Icons und keine Beschriftung.

Ursache:
- context_key_text war auf off gesetzt.

Änderung:
- inc/provisioning_snom.php setzt jetzt:
  <context_key_text perm="R">on</context_key_text>

Gilt für:
- snomD815
- snomD810

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_22_context_key_text_labels.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_22_context_key_text_labels.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Eine Nebenstelle speichern, damit snom-check-cfg ausgelöst wird.
