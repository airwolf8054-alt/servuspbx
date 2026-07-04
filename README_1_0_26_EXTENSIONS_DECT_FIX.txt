ServusPBX Medical 1.0.26 - Extensions DECT Fix

Behoben:
- Memory Exhausted in pages/extensions.php durch rekursiven Funktionsaufruf.
- spbx_ext_dect_bases() ruft sich nicht mehr selbst auf.
- DECT-Handset verwendet korrekt snom_dect_handset.
- IPEI bleibt die ps_endpoints.id.
- DECT-Basis-Auswahl bleibt sichtbar nur bei DECT.
- Beim Speichern wird das Handset der gewählten Basis mit automatisch vergebener IDX zugeordnet.
- Beim Löschen wird die DECT-Zuordnung entfernt.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_26_extensions_dect_fix.zip
sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
sudo chown -R www-data:www-data /var/www/html
