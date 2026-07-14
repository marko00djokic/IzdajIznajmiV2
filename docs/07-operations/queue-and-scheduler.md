# Queue and Scheduler Operations

> Status: `active`
> Poslednja ciljana provera: 2026-07-15
> Source of truth: queue config, Jobs, `bootstrap/app.php` i Compose commands

## Health Endpoints
- Liveness: `GET /api/v1/health`
- Readiness: `GET /api/v1/health/ready`
- Queue health: `GET /api/v1/health/queue`

Queue health includes:
- queue driver connectivity check
- `failed_jobs` table availability
- `failed_jobs` count
- alert status (`enabled`, `threshold`, `triggered`)

## Failed Jobs CLI
- List failed jobs:
```bash
php artisan queue:failed
```
- Retry one failed job:
```bash
php artisan queue:retry <id>
```
- Retry all failed jobs:
```bash
php artisan queue:retry all
```
- Delete one failed job:
```bash
php artisan queue:forget <id>
```
- Flush all failed jobs:
```bash
php artisan queue:flush
```

## Alert Hook for Failed Jobs
Alert logging is env-gated and disabled by default:
- `QUEUE_FAILED_JOBS_ALERT_ENABLED=false`
- `QUEUE_FAILED_JOBS_ALERT_THRESHOLD=0`
- `QUEUE_FAILED_JOBS_ALERT_COOLDOWN_SECONDS=300`

When enabled, `/health/ready` and `/health/queue` will emit a structured error log (`queue_failed_jobs_alert`) once per cooldown window if `failed_jobs` exceeds threshold.

## Worker and Scheduler
- Queue worker:
```bash
php artisan queue:work
```
- Scheduler worker (local/dev):
```bash
php artisan schedule:work
```
- Production cron (every minute):
```bash
* * * * * www-data /usr/bin/php /var/www/izdaji/backend/artisan schedule:run >> /var/log/cron.log 2>&1
```

## Correlation IDs
- Every request carries `X-Request-Id` (generated or propagated).
- Error JSON payloads now include `request_id`.
- Structured logs include the same request id for traceability across API, queue failures, and 5xx exceptions.
