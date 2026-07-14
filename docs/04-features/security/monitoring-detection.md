# Monitoring & Detection

> Status: `implemented reference`; spoljašnji monitoring zavisi od okruženja
> Poslednja strukturna provera: 2026-07-15
> Source of truth: structured logging/fraud/health/backup kod, config i testovi

Security Phase 6 — implemented 2026-03-02.

---

## 1. Security Event Log

### Channel

All security events are written to the **`structured`** log channel (`storage/logs/structured-YYYY-MM-DD.log`), which emits one JSON object per line. Every entry automatically includes:

| Field | Source |
|---|---|
| `action` | Event identifier (see table below) |
| `user_id` | Authenticated user at time of event |
| `ip` | Request IP |
| `user_agent` | Request User-Agent |
| `route` | Request path |
| `request_id` | X-Request-Id header |
| `release` | App version |
| `severity` | `info`, `warning`, or `error` |
| `security_event` | Always `true` for security events |

### Event Types

| Action | Severity | Trigger | Controller |
|---|---|---|---|
| `auth.login_failed` | warning | Failed credential check | `AuthController::login` |
| `auth.login_blocked` | warning | Lockout already active (429 returned) | `AuthController::login` |
| `auth.brute_force_lockout` | warning | Lockout threshold just hit | `AuthController::login` |
| `auth.mfa_failed` | warning | Invalid TOTP or recovery code | `MfaController::verify` |
| `auth.account_deleted` | warning | GDPR account deletion confirmed | `UserAccountController::deleteAccount` |
| `auth.session_revoked` | info | User manually revoked one session | `SessionController::revoke` |
| `auth.sessions_bulk_revoked` | warning | User revoked all other sessions | `SessionController::revokeOthers` |
| `auth.impersonation_started` | warning | Admin starts impersonation | `ImpersonationController::start` |
| `auth.impersonation_stopped` | warning | Admin ends impersonation | `ImpersonationController::stop` |
| `kyc.document_accessed_by_admin` | info | Admin downloads a KYC document | `KycDocumentController::show` |
| `security.ip_failed_logins_threshold` | warning | IP-level failed login threshold exceeded | `FraudSignalService::recordFailedLoginIp` |
| `fraud.signal_recorded` | info | Any fraud signal persisted for a user | `FraudSignalService::recordSignal` |
| `backup.verified` | info | Backup file exists and is fresh | `VerifyBackupCommand` |
| `backup.verify_failed` | error | Backup dir missing or no file found | `VerifyBackupCommand` |
| `backup.stale` | error | Latest backup exceeds staleness threshold | `VerifyBackupCommand` |
| `backup.checksum_failed` | error | SHA-256 checksum mismatch | `VerifyBackupCommand` |
| `queue_failed_jobs_alert` | error | Failed jobs exceed threshold | `HealthController` |
| `queue_job_failed` | error | Individual job failure | `AppServiceProvider` (Queue::failing) |
| `unhandled_exception` | error | Unhandled 5xx exception | `bootstrap/app.php` |

### Example log entry

```json
{
  "message": "auth.login_failed",
  "context": {
    "action": "auth.login_failed",
    "severity": "warning",
    "security_event": true,
    "user_id": null,
    "ip": "192.168.1.100",
    "user_agent": "Mozilla/5.0 ...",
    "route": "api/v1/auth/login",
    "request_id": "req-abc123",
    "release": "1.4.2",
    "attempt_count": 3,
    "max_attempts": 10
  },
  "level": 300,
  "level_name": "WARNING",
  "channel": "structured",
  "datetime": "2026-03-02T08:15:42.000000+00:00"
}
```

### Log level

The structured channel uses `LOG_STRUCTURED_LEVEL` (default: `info`) independently of the general
`LOG_LEVEL` setting. This prevents the production Docker Compose override (`LOG_LEVEL=warning`) from
silently discarding `info`-level security events.

**Do not set `LOG_STRUCTURED_LEVEL` above `info`** — doing so will cause fraud signals, KYC access
events, impersonation events, and session revocations to be silently dropped.

### Log retention

Configured via `LOG_DAILY_DAYS` (default: 14 days). Increase for compliance requirements.

### Querying security events

```bash
# All security events from today
grep '"security_event":true' storage/logs/structured-$(date +%Y-%m-%d).log | jq .

# Failed logins
grep '"auth.login_failed"' storage/logs/structured-$(date +%Y-%m-%d).log | jq .

# Admin KYC access
grep '"kyc.document_accessed_by_admin"' storage/logs/structured-*.log | jq .

# Impersonation events
grep '"auth.impersonation_' storage/logs/structured-*.log | jq .

# Fraud signals
grep '"fraud.signal_recorded"' storage/logs/structured-$(date +%Y-%m-%d).log | jq .
```

### Admin UI log viewer

Admins can browse and filter the structured log without terminal access at:
`/admin/logs` — see [Admin log viewer](admin-log-viewer.md).

---

## 2. Fraud Signals

### How signals work

1. An event triggers a `FraudSignalService` method.
2. The signal is persisted in the `fraud_signals` table with a `weight` and optional `cooldown`.
3. `FraudScore` is recalculated by summing all signals in the rolling window (`FRAUD_SCORE_WINDOW_DAYS`, default 30 days).
4. If `score >= FRAUD_SCORE_THRESHOLD` (default 60) and `is_suspicious` is not yet set:
   - `User.is_suspicious = true` is saved.
   - All admins receive a `TYPE_ADMIN_NOTICE` in-app notification.

### Complete signal reference

| Signal key | Weight | Trigger condition | Cooldown | Method |
|---|---|---|---|---|
| `failed_mfa` | 8 | ≥ 3 failed TOTP/recovery attempts in 10 min window | 30 min | `recordFailedMfaAttempt` |
| `failed_mfa_rate_limited` | 8 | MFA rate limiter (5/min) hit | 30 min | `recordFailedMfaRateLimit` |
| `rapid_messages` | 5 | ≥ 12 chat messages in 5 min | — | `AppServiceProvider` (chat_messages limiter) |
| `rapid_applications` | 10 | ≥ 4 applications in 30 min | — | Applications rate limiter |
| `duplicate_address_attempt` | 15 | Duplicate address detected | 60 min | Domain logic |
| `session_anomaly` | 6 | ≥ 3 distinct IPs or user-agents in 24 h | `window_hours * 60` min | `AuthController::recordSessionAnomaly` |
| `kyc_multi_user_ip` | 20 | Same IP submits KYC for ≥ 2 distinct users in 24 h | 240 min | `FraudSignalService::recordKycMultiUserIp` |
| `rapid_uploads` | 5 | Chat attachment rate limit (10/10 min) hit | 60 min | `ChatAttachmentRateLimit` via `FraudSignalService::recordRapidUploads` |

> **IP-level failed login** (`security.ip_failed_logins_threshold`) is **not** a user-scoped signal — it is written only to the structured log because no authenticated user context is available during failed anonymous login attempts.

### Admin notification flow

When `FraudSignalService::recalculateScore()` detects threshold breach:
- All users with role `admin` receive `Notification::TYPE_ADMIN_NOTICE`.
- Notification body: `"User <name> (ID <n>) reached fraud score <score>."`
- Link: `/admin/users/<id>`

When individual high-risk signals fire (`failed_mfa`, `kyc_multi_user_ip`, `rapid_uploads`), `notifyAdminsOfSignal()` sends an immediate `TYPE_ADMIN_NOTICE` without waiting for score threshold.

### Adding a new signal

1. Add configuration in `config/security.php` under `fraud.signals.<key>`:
   ```php
   'my_signal' => [
       'weight' => (int) env('FRAUD_SIGNAL_MY_SIGNAL_WEIGHT', 10),
       'cooldown_minutes' => (int) env('FRAUD_SIGNAL_MY_SIGNAL_COOLDOWN', 60),
   ],
   ```
2. Add env vars to `.env.example`.
3. Add a public method to `FraudSignalService` or call `recordSignal()` directly:
   ```php
   $this->fraudSignals->recordSignal($user, 'my_signal', $weight, $meta, $cooldown);
   ```
4. Document the new signal in this file.

---

## 3. Anomaly Alerting

### IP-level failed login anomaly

**Trigger**: `FRAUD_SIGNAL_FAILED_LOGIN_IP_THRESHOLD` (default 20) failed login attempts from a single IP within `FRAUD_SIGNAL_FAILED_LOGIN_IP_WINDOW` (default 10) minutes.

**Action**: Structured log warning at `security.ip_failed_logins_threshold`. The IP is stored only as its SHA-256 hash for privacy. No user-scoped signal is created.

**To investigate**: Query the structured log for `ip_hash` and correlate with your WAF or nginx access logs using the same time window.

### KYC multi-user IP

**Trigger**: The same IP submits KYC documents for ≥ 2 distinct users within 24 hours.

**Action**: `kyc_multi_user_ip` fraud signal (weight 20) on the submitting user. Immediate admin notification via `TYPE_ADMIN_NOTICE`.

### Rapid upload blocking

**Trigger**: `ChatAttachmentRateLimit` middleware blocks a user who exceeds 10 chat attachment uploads in 10 minutes.

**Action**: `rapid_uploads` fraud signal (weight 5) on the authenticated user.

### When fraud score threshold is met

Threshold: `FRAUD_SCORE_THRESHOLD` (default 60). All admins receive an in-app `TYPE_ADMIN_NOTICE` notification. The user's `is_suspicious` flag is set to `true`, which may restrict certain actions (depending on policy).

To confirm the admin notification path works end-to-end:
```bash
php artisan tinker
# In tinker:
$user = App\Models\User::find(<id>);
$svc = app(App\Services\FraudSignalService::class);
$svc->recordSignal($user, 'test_signal', 100, ['test' => true]);
# Check admin notifications table or in-app notification panel
```

---

## 4. Sentry Integration

### Backend

| Setting | Config key | Default |
|---|---|---|
| Enable/disable | `services.sentry.enabled` | `false` |
| DSN | `services.sentry.dsn` | `SENTRY_DSN` env |
| Release | `services.sentry.release` | `APP_VERSION` |
| Environment | `services.sentry.environment` | `APP_ENV` |

**Note**: The backend does NOT use the Sentry log channel (`config/logging.php`). Sentry capture is handled manually via `App\Services\SentryReporter`, which is injected into controllers and services that need it. This avoids double-reporting and gives full control over PII scrubbing.

**What reaches Sentry**:
- All unhandled HTTP 5xx exceptions (via `bootstrap/app.php` exception handler).
- All failed queue jobs (via `AppServiceProvider::Queue::failing`).
- Failed jobs count alert (via `HealthController::emitFailedJobsAlert`).
- Backup staleness and checksum failures (via `VerifyBackupCommand`).

**PII scrubbing**: `App\Logging\PiiSanitizer` (Monolog processor) sanitises all logged fields — but this applies to the log channel, not to Sentry directly. `SentryReporter` only sets `user_id` and `role` as user context (via `SetSentryUserContext` middleware); no email, name, or phone is ever included.

**User context per request**: The `SetSentryUserContext` middleware runs on every API request. If a user is authenticated it calls `$hub->configureScope(fn ($scope) => $scope->setUser(['id' => $user->id, 'segment' => $user->role]))`.

**To verify Sentry is working**:
```bash
# Set SENTRY_ENABLED=true and SENTRY_DSN=<your DSN>, then:
php artisan tinker
app(App\Services\SentryReporter::class)->captureMessage('test from tinker', 'info', ['flow' => 'test']);
# Check your Sentry project dashboard
```

### Frontend

The `@sentry/vue` package is loaded lazily (dynamic import) only when `VITE_SENTRY_DSN` is set. If the DSN is absent or the SDK fails to load, the app continues normally.

| Env var | Description | Default |
|---|---|---|
| `VITE_SENTRY_DSN` | Browser DSN from Sentry project settings | (empty — disabled) |
| `VITE_SENTRY_ENVIRONMENT` | Environment tag | `production` |
| `VITE_SENTRY_RELEASE` | Release tag (link to source maps) | (empty) |
| `VITE_SENTRY_TRACES_SAMPLE_RATE` | Performance tracing sample rate (0–1) | `0.1` |

`sendDefaultPii: false` — no cookies, headers, or IP addresses are attached to frontend events.

User context (`setSentryUser`) should be called from the auth store after login to attach `user_id` and `role` to subsequent events. Example:
```ts
import { setSentryUser } from '@/services/sentry'
// after login:
setSentryUser({ id: user.id, role: user.role })
// after logout:
setSentryUser(null)
```

---

## 5. Health Endpoints

All endpoints are public (no authentication required) and safe to poll from uptime monitors.

### `GET /api/v1/health` — liveness

Returns 200 if the database is reachable.

```json
{
  "status": "ok",
  "app": { "name": "IzdajIznajmi", "version": "1.4.2", "env": "production" },
  "checks": {
    "db": { "ok": true }
  }
}
```

Failure: HTTP 500, `"status": "error"`.

### `GET /api/v1/health/ready` — readiness

Returns 200 if **all** of the following pass: database, cache, queue, storage disks.

```json
{
  "status": "ok",
  "app": { "..." },
  "checks": {
    "db": { "ok": true },
    "cache": { "ok": true, "driver": "redis" },
    "queue": {
      "ok": true,
      "driver": "database",
      "failed_jobs": { "ok": true, "count": 0 },
      "alerts": { "enabled": true, "threshold": 5, "triggered": false }
    },
    "storage": {
      "ok": true,
      "disks": {
        "private": { "ok": true, "disk": "private", "uses": "KYC documents and chat attachments" },
        "public": { "ok": true, "disk": "public", "uses": "avatars and listing images" }
      }
    }
  }
}
```

Failure: HTTP 500. Any failing check has `"ok": false` plus an `"error"` field.

### `GET /api/v1/health/queue` — queue deep-check

Returns queue connectivity status, failed jobs count, and alert state.

```json
{
  "status": "ok",
  "app": { "..." },
  "checks": {
    "queue": {
      "ok": true,
      "driver": "database",
      "connection": "pgsql",
      "table": "jobs",
      "failed_jobs": { "ok": true, "count": 2 },
      "alerts": { "enabled": true, "threshold": 5, "triggered": false }
    }
  }
}
```

**Alert behaviour**: When `QUEUE_FAILED_JOBS_ALERT_ENABLED=true` and `count > QUEUE_FAILED_JOBS_ALERT_THRESHOLD`, a `queue_failed_jobs_alert` structured log error is emitted and a Sentry message is captured. The alert is suppressed for `QUEUE_FAILED_JOBS_ALERT_COOLDOWN_SECONDS` (default 300 s) to avoid alert storms.

### Storage check

The `storage` check (included in `/health/ready`) writes a probe file to both the `private` and `public` filesystem disks and verifies it can be read back. This confirms:
- The private disk (KYC documents, chat attachments) is writable.
- The public disk (avatars, listing images) is writable.

---

## 6. Backup Monitoring

### Command

```
php artisan backup:verify [--backup-dir=<path>]
```

**Schedule**: Daily at 06:00 (after the backup cron which runs at ~03:00).

**What it checks**:
1. `BACKUP_DIR` directory exists.
2. At least one `*.sql.gz` file is present.
3. The newest file's mtime is within `BACKUP_STALENESS_ALERT_HOURS` (default 26 h).
4. If a `*.sha256` sidecar exists alongside the backup, its SHA-256 hash is verified.

**On success**: Logs `backup.verified` at info level.

**On failure**: Logs at error level and sends a Sentry `error`/`critical` message. The command exits with a non-zero code so it can be caught by cron monitoring.

### Environment variables

| Variable | Default | Description |
|---|---|---|
| `BACKUP_DIR` | `/var/backups/izdaji/postgres` | Directory containing `*.sql.gz` files |
| `BACKUP_STALENESS_ALERT_HOURS` | `26` | Max allowed age of the newest backup before alerting |

### Manual run

```bash
php artisan backup:verify
# With override:
php artisan backup:verify --backup-dir=/mnt/backups/postgres
```

---

## 7. Runbook — What to do when each alert fires

### `auth.login_failed` / `auth.brute_force_lockout`

1. **Check the pattern**: are failures targeted at one account or spread across many?
   ```bash
   grep '"auth.login_failed"' storage/logs/structured-*.log | jq .context.ip
   ```
2. If same IP → consider blocking at WAF / nginx level.
3. If same account → notify the user to change password and review trusted devices.
4. Lockout clears automatically after `LOGIN_LOCKOUT_MINUTES` (default 15 min).

### `security.ip_failed_logins_threshold`

An IP exceeded 20 failed logins in 10 minutes. This is a credential-stuffing or password-spray indicator.

1. Identify the IP hash in the log entry.
2. Correlate with nginx/WAF logs to find the actual IP.
3. Block at the infrastructure level if confirmed malicious.
4. Consider lowering `FRAUD_SIGNAL_FAILED_LOGIN_IP_THRESHOLD` or enabling CAPTCHA on the login endpoint.

### `auth.mfa_failed`

1. Check if the `user_id` is known or if it looks like a targeted attack.
2. If the fraud signal `failed_mfa` has also fired (check `fraud_signals` table), the user's score has increased.
3. If score threshold is met, `is_suspicious=true` is already set and admins were notified.
4. Admin can clear suspicion via `POST /api/v1/admin/users/{user}/fraud/clear`.

### `kyc_multi_user_ip` fraud signal

Multiple users submitted KYC from the same IP.

1. Admin receives `TYPE_ADMIN_NOTICE`. Navigate to `/admin/kyc`.
2. Review the submissions. If the same IP is suspicious (e.g. a VPN/proxy farm), reject the submissions.
3. If legitimate (shared office IP), clear the fraud signal via `POST /api/v1/admin/users/{user}/fraud/clear`.

### `rapid_uploads` fraud signal

User hit the chat attachment rate limit (10 uploads / 10 min).

1. Rarely malicious on its own — usually a confused user. Check if the fraud score is also elevated.
2. If combined with other signals, escalate: revoke sessions via admin panel.

### `auth.impersonation_started` / `auth.impersonation_stopped`

Informational. Review during security audits to ensure no unexpected impersonation activity.

### `backup.stale` / `backup.verify_failed` / `backup.checksum_failed`

**Critical — data loss risk.**

1. SSH to the backup host.
2. Check `BACKUP_DIR` for the latest file: `ls -lh $BACKUP_DIR/*.sql.gz | tail -5`
3. Check backup cron/timer: `systemctl status pg-backup.timer` (or check cron log).
4. If the backup job failed, investigate the error and re-run `ops/backup_pg.sh` manually.
5. If checksum mismatch: do NOT use the corrupted file. Find an older backup and investigate storage integrity.
6. Alert on-call engineer immediately.

See also: [backup runbook](../../07-operations/backups.md).

### `queue_failed_jobs_alert`

1. List failed jobs: `php artisan queue:failed`
2. Inspect the top entries: `php artisan queue:failed` → note UUIDs.
3. Retry if safe: `php artisan queue:retry all`
4. If retry loops, investigate the job class and fix the underlying issue.
5. Delete non-retryable jobs: `php artisan queue:forget <uuid>`

### Storage disk failure (`/health/ready` → `storage.ok = false`)

1. Check disk space: `df -h`
2. Check permissions on the storage directory: `ls -la storage/`
3. If S3/object storage: check credentials and bucket policy.
4. Restart the queue worker after fixing: `php artisan queue:restart`

---

*Last updated: 2026-03-02 — Security Phase 6 hotfixes (fraud.signal_recorded, LOG_STRUCTURED_LEVEL, admin log viewer)*
