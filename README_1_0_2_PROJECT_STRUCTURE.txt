ServusPBX Medical 1.0.2 - Projektstruktur

Neu:
- index.php als Einstiegspunkt
- css/servuspbx.css
- pages/ für kommende Funktionsseiten
- js/ und images/ vorbereitet
- Sidebar-Links zeigen auf pages/*
- Automatische Weiterleitung:
  / -> login.php oder dashboard.php

Installation empfohlen:
cd /tmp
unzip servuspbx_medical_1_0_2_project_structure.zip
sudo rsync -av --delete servuspbx_medical_1_0_2_project_structure/ /var/www/html/
sudo chown -R www-data:www-data /var/www/html
