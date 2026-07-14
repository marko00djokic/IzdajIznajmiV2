# PostgreSQL Backups and Restore

> Status: `active`
> Poslednja ciljana provera: 2026-07-15
> Source of truth: `ops/backup_pg.sh`, `ops/restore_pg.sh` i systemd jedinice

This runbook covers automated Postgres backups for production/staging.

## Scripts
- Backup: `ops/backup_pg.sh`
- Restore: `ops/restore_pg.sh`

Both scripts are env-driven and safe by default:
- `restore_pg.sh` refuses to run unless `CONFIRM_RESTORE=1` is set.
- Backup retention is controlled by `BACKUP_RETENTION_DAYS` (default `14`).

## Required Environment Variables
- `PGHOST` (default `127.0.0.1`)
- `PGPORT` (default `5432`)
- `PGDATABASE` (required)
- `PGUSER` (required)
- `PGPASSWORD` (required for password auth)

Optional:
- `BACKUP_DIR` (default `/var/backups/izdaji/postgres`)
- `BACKUP_RETENTION_DAYS` (default `14`)

## Frequency and Retention Strategy
- Minimum baseline: run daily full logical backup.
- Recommended: keep 14 daily backups locally, then offload to object storage with longer retention.
- Recommended restore drill: at least once per month to staging.

## Run Backup Manually
```bash
PGHOST=127.0.0.1 \
PGPORT=5432 \
PGDATABASE=izdaji \
PGUSER=izdaji \
PGPASSWORD='***' \
BACKUP_DIR=/var/backups/izdaji/postgres \
BACKUP_RETENTION_DAYS=14 \
./ops/backup_pg.sh
```

Output:
- `*.sql.gz` dump file
- `*.sha256` checksum file

## Restore to Staging (Recommended Drill)
1. Pick a backup archive (for example `/var/backups/izdaji/postgres/izdaji_20260216T033000Z.sql.gz`).
2. Restore into a staging database:

```bash
CONFIRM_RESTORE=1 \
PGHOST=127.0.0.1 \
PGPORT=5432 \
PGDATABASE=izdaji_staging \
PGUSER=izdaji \
PGPASSWORD='***' \
RECREATE_DB=true \
./ops/restore_pg.sh /var/backups/izdaji/postgres/izdaji_20260216T033000Z.sql.gz
```

3. Run post-restore checks:
- `php artisan migrate:status`
- `php artisan db:report-indexes`
- `curl -f http://staging-host/api/v1/health/ready`

## Automation Options
- Cron sample: see `ops/cron.txt`.
- Systemd sample units:
  - `ops/systemd/pg-backup.service`
  - `ops/systemd/pg-backup.timer`

Enable timer example:
```bash
sudo cp ops/systemd/pg-backup.service /etc/systemd/system/
sudo cp ops/systemd/pg-backup.timer /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now pg-backup.timer
sudo systemctl list-timers pg-backup.timer
```
