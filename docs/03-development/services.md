# Development services

> Status: `implemented`
> Poslednja ciljana provera: 2026-07-15
> Source of truth: Compose fajlovi, `backend/bootstrap/app.php`, Jobs i config

| Proces | Docker servis | Obaveznost | Potreban za |
| --- | --- | --- | --- |
| Laravel HTTP | `backend` | obavezno za real API | svi backend tokovi |
| PostgreSQL | `postgres` | obavezno u Compose | trajni podaci, queue |
| queue worker | `queue` | praktično obavezno | slike, notifications/push, search/geocode, KYC/attachments |
| scheduler | `scheduler` | obavezno za pun tok | expiry, saved searches, digesti, purge, backup verify |
| Reverb | `reverb` | feature-zavisno | broadcast događaji za chat/notifications |
| Meilisearch | `meilisearch` | za Search V2 | indeksirana search/suggest pretraga |
| frontend | `frontend` | za UI | Vue aplikacija |

Scheduler u `bootstrap/app.php` registruje dnevne/periodične komande za oglase,
badges, digest, saved-search matching, KYC/trusted-device/attachment/audit/
notification retention i backup verifikaciju. Potpun aktuelan prikaz:

```bash
cd backend
php artisan schedule:list
php artisan queue:monitor database:default --max=100
```

Za incidentne komande i health endpoint-e vidi
[queue runbook](../07-operations/queue-and-scheduler.md).
