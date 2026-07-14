# Operacije i runbookovi

> Status: `active`; okruženje je production-like, ne potvrđena poslovna produkcija
> Poslednja ciljana provera indeksa: 2026-07-15
> Source of truth: `ops/`, Compose fajlovi, workflow-i i backend health/commands

- [Deployment i rollback](deployment.md)
- [PostgreSQL backup i restore](backups.md)
- [Queue i scheduler](queue-and-scheduler.md)
- [Chat i realtime podrška](chat-realtime.md)
- [Load testing](load-testing.md)
- [Performance i DB observability](performance.md)

## Pre operativne izmene

1. Proveri source skriptu/config i oba Compose fajla.
2. Ne stavljaj stvarne tajne u komandu, log ili dokument.
3. Definiši signal, preduslove, korake, verifikaciju i rollback/recovery.
4. Produkcioni destructive korak zahteva eksplicitnu procenu podataka i backup.
5. Ažuriraj runbook u istom zadatku.

Health endpoint-i su `/api/v1/health`, `/health/ready` i `/health/queue`.
