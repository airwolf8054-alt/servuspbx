ServusPBX Professional 2.1.1 - M400 Sync + DECT Directory

Neu:
- M400 Sync Button in der DECT-Basisliste.
- /provision/dectdirectory.php erzeugt XML-Telefonbuch aus spbx_phonebook.
- snomM400.php verweist auf dectdirectory.php.

Hinweis:
- Sync nutzt http://<M400-IP>/CfgResync.
- Die IP kommt aus spbx_dect_bases.ip_address und wird beim Provisioning aktualisiert.
- SQL optional.
