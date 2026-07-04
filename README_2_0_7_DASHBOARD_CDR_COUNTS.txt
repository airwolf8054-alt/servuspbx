ServusPBX Professional 2.0.7 - Dashboard CDR Counts

Korrigiert:
- Ausgehende Anrufe wurden bisher über src REGEXP gezählt.
  Das ist mit Goto/outgoing_<hauptnummer> nicht zuverlässig.
- Neue Zählung:
  Eingehend: dcontext='incoming' oder PJSIP/trunk-* Kanal
  Ausgehend: dcontext LIKE outgoing_% oder Dial über @trunk-*
  Verpasst: nur eingehende NO ANSWER/BUSY/FAILED
- Wenn linkedid in CDR vorhanden ist, wird pro Gespräch gezählt statt pro CDR-Zeile.

SQL optional.
Browser hart neu laden.
