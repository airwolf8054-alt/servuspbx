ServusPBX Professional 2.6.2 - Legacy Deskphone Provisioning

Ziel:
- Provisioning wieder nach bewaehrter alter Methode.
- Keine statisch generierten XML-Dateien pro Telefon.
- Tischtelefone provisionieren ueber PHP-Dateien in /provision/, z.B.:
  /provision/snomD810.php?mac={mac}
  /provision/snomD895.php?mac={mac}

Datenbank:
- Neue Tabelle deskphones als flache Legacy-Struktur.
- fkey0 bis fkey41 bleiben absichtlich genau so benannt.
- MyISAM + utf8mb3 wie beim alten phones-Konzept, damit die flache Struktur ohne InnoDB-Zeilengroessenproblem funktioniert.

Tastenlogik:
- fkey-Index beginnt bei 0.
- Taste 1 Ebene 1 = fkey0.
- Taste 10 Ebene 1 = fkey9.
- Taste 1 Ebene 2 = fkey10 beim 10-Tasten-Modell.
- Formel: fkey = ((Ebene - 1) * physische_Tasten) + (Taste - 1)

Modelle:
- snomD810 / GigasetP810: 4 Tasten x 4 Ebenen = 16
- snomD812 / snomD862 / snomD865 / snomD892 / GigasetP82x: 8 x 4 = 32
- snomD815 / GigasetP85x: 10 x 4 = 40
- snomD895: 14 x 3 = 42

Korrekturen in 2.6.2:
- SQL-Patch nutzt MyISAM und entfernt den doppelten fkey28label-Eintrag.
- Provisioning-Loader korrigiert: deskphones wird nur mit MAC + phone_type gesucht.
- Tastenbelegungsseite zeigt die korrekte Ebenenanzahl (D895 = 3 Ebenen).
- Provisioning bleibt dynamisches PHP, keine statischen XML-Ausgaben.

Installation:
1. Dateien entpacken / deployen.
2. SQL-Patch importieren:
   mysql -u root -p general < patch_servuspbx_professional_2_6_2_legacy_provisioning_final.sql
3. Test:
   http://SERVER/provision/snomD810.php?mac=MACADRESSE
