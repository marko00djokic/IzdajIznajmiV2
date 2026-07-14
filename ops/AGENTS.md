# Ops lokalna pravila

Važe i root `AGENTS.md` pravila.

- Shell skripte moraju ostati neinteraktivne gde je moguće, sa jasnim exit
  statusom i bez ispisivanja tajni.
- Promena deploy/rollback/backup/Nginx ponašanja obavezno ažurira odgovarajući
  runbook u `docs/07-operations/`.
- Pre izmene proveri oba Compose fajla, env primere i GitHub deploy workflow-e.
- Destruktivne korake (restore, cleanup, rollback podataka) dokumentuj sa
  preduslovom, verifikacijom i jasnim recovery/escalation korakom.
- Lokalno validiraj shell sintaksu sa `bash -n` i Compose konfiguraciju sa
  `docker compose ... config --quiet`.
