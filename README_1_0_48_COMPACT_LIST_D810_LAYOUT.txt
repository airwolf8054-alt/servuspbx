ServusPBX Medical 1.0.48 - Compact List + D810 Layout

Korrigiert/angepasst:
- Nebenstellenliste kompakter für größere Praxen.
- Endpoint-ID bleibt bei Tischtelefonen die Durchwahl.
- DECT bleibt IPEI als ps_endpoints.id.
- D810 BLF-Editor:
  fkey0 bis fkey3 untereinander pro Ebene.
  Keine linke/rechte Spaltenlogik.
- D815 bleibt:
  links fkey0 bis fkey4,
  rechts fkey5 bis fkey9.

Installation:
1. SQL-Patch optional importieren:
   patch_servuspbx_medical_1_0_48_compact_list_d810_layout.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_48_compact_list_d810_layout.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
