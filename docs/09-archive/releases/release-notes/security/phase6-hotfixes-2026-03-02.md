# Security Phase 6 — Post-Audit Hotfixes

Date: 2026-03-02
Branch: `Security-Phase-6-Monitoring-Detection`

Fixes identified during E2E verification of the Phase 6 test plan (T-08, T-12) and during seeder testing.

---

## Fix 1 — Structured log silently dropped INFO entries in production stack

### Symptom

`kyc.document_accessed_by_admin` and other `INFO`-level structured log entries were absent from
`storage/logs/structured-*.log` despite the code path being confirmed to execute (audit log showed
the corresponding database entry).

### Root cause

`docker-compose.production.yml` sets:
```yaml
LOG_LEVEL: ${LOG_LEVEL:-warning}
```

Docker Compose environment blocks override the `.env` file. The structured log channel was configured as:
```php
'level' => env('LOG_LEVEL', 'info'),
```

With `LOG_LEVEL=warning`, Monolog's minimum level for the channel was `warning`, silently discarding
all `info`-level entries (fraud signals, KYC access, impersonation events, session revocations, etc.).

### Fix

Introduced a dedicated `LOG_STRUCTURED_LEVEL` environment variable, decoupled from `LOG_LEVEL`:

**`config/logging.php`**:
```php
'structured' => [
    'level' => env('LOG_STRUCTURED_LEVEL', 'info'),
    // ...
],
```

**`docker-compose.production.yml`**:
```yaml
LOG_LEVEL: ${LOG_LEVEL:-warning}
LOG_STRUCTURED_LEVEL: ${LOG_STRUCTURED_LEVEL:-info}
```

**`.env` / `.env.example`**:
```
LOG_LEVEL=debug
LOG_STRUCTURED_LEVEL=info
```

This ensures security events are always captured at `info` level regardless of the application log
verbosity setting.

---

## Fix 2 — Seeder crash: encrypted PII overflows varchar(255)

### Symptom

```
SQLSTATE[22001]: String data, right truncated: 7 ERROR: value too long for type character varying(255)
```

Occurred when running `php artisan db:seed` after the PII encryption migration.

### Root cause

Laravel's AES-256-CBC `encrypt()` produces a base64-encoded JSON envelope (IV + ciphertext + MAC),
typically 200–400+ characters. The `phone` and `residential_address` columns remained `varchar(255)`
after the PII encryption migration (`2026_05_10_000000_encrypt_pii_fields_on_users_table.php`), which
re-encrypted existing values but did not widen the column types.

### Fix

Added column-type changes to the migration's `up()` block **before** the backfill step:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('phone_hash', 64)->nullable()->after('phone');
    $table->text('phone')->nullable()->change();               // was string(255)
    $table->text('residential_address')->nullable()->change(); // was string(255)
});
```

The `down()` block reverts both columns back to `string`.

**Action required after deploying this fix**: Run `php artisan migrate:fresh --seed` (development) or
`php artisan migrate` (production — column type change is applied non-destructively).

---

## Fix 3 — fraud.signal_recorded missing from structured log

### Symptom

After triggering a `kyc_multi_user_ip` fraud signal, the structured log contained no
`fraud.signal_recorded` entry even though the signal was correctly persisted in the `fraud_signals`
table and the admin panel showed the updated fraud score.

### Root cause

`FraudSignalService::recordSignal()` wrote the signal to the database and sent admin notifications,
but had no call to `Log::channel('structured')`. The event type `fraud.signal_recorded` was planned
in the test spec but was never implemented in code.

### Fix

Added a structured log call inside `recordSignal()` immediately after `FraudSignal::create()`:

```php
Log::channel('structured')->info('fraud.signal_recorded', array_merge([
    'action'         => 'fraud.signal_recorded',
    'security_event' => true,
    'user_id'        => $user->id,
    'signal'         => $key,
    'weight'         => $weight,
], $meta));
```

The `$meta` array (e.g. `ip_hash`, `distinct_users`) is spread as top-level context fields so they
are queryable without nesting. All future signals automatically get this log entry because every
signal type flows through `recordSignal()`.

The `fraud.signal_recorded` event type has been added to the event reference table in
`docs/security/MONITORING-DETECTION.md`.

---

## Feature — Admin Structured Log Viewer

A read-only UI for the structured security log, accessible at `/admin/logs`.

See full documentation in `docs/security/ADMIN-LOG-VIEWER.md`.

**Summary**:
- Backend: `GET /api/v1/admin/logs` (admin-only, MFA-gated) — reads the daily JSON log file, applies
  server-side filters (date, action, level, security_event, user_id, limit).
- Frontend: `AdminLogs.vue` — filter bar, table with expandable rows, client-side pagination (50 rows/page).
- Nav link added to all admin panel pages.

---

*Files changed*:

| File | Change |
|---|---|
| `backend/config/logging.php` | `LOG_STRUCTURED_LEVEL` env var for structured channel level |
| `backend/.env` / `.env.example` | Added `LOG_STRUCTURED_LEVEL=info` |
| `docker-compose.production.yml` | Added `LOG_STRUCTURED_LEVEL: ${LOG_STRUCTURED_LEVEL:-info}` |
| `backend/database/migrations/2026_05_10_000000_encrypt_pii_fields_on_users_table.php` | Widen `phone` and `residential_address` to `text` |
| `backend/app/Services/FraudSignalService.php` | Added `Log::channel('structured')` call in `recordSignal()` |
| `backend/app/Http/Controllers/Admin/StructuredLogController.php` | New controller |
| `backend/routes/api.php` | `GET /admin/logs` route |
| `frontend/src/services/realApi.ts` | `StructuredLogEntry` interface + `getAdminLogs()` |
| `frontend/src/services/mockApi.ts` | `getAdminLogs` stub |
| `frontend/src/services/index.ts` | `getAdminLogs` export |
| `frontend/src/stores/language.ts` | `admin.nav.logs` + `admin.logs.*` i18n keys (EN + BS) |
| `frontend/src/pages/AdminLogs.vue` | New page |
| `frontend/src/router/index.ts` | `/admin/logs` route |
| `frontend/src/pages/Admin*.vue` (×6) | Logs nav link added |
| `docs/security/ADMIN-LOG-VIEWER.md` | New — feature documentation |
| `docs/security/MONITORING-DETECTION.md` | Updated event table + LOG_STRUCTURED_LEVEL note |
