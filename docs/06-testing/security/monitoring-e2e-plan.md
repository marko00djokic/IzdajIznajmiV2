# E2E Test Plan — Security Phase 6: Monitoring & Detection

> Status: `active manual regression plan`
> Poslednja strukturna provera: 2026-07-15
> Source of truth: security logging/health kod, config i automatizovani testovi

Verification test plan for everything implemented in Phase 6.
Phase 6 adds no new UI screens — tests verify that normal user flows **generate the expected structured logs, fraud signals, Sentry context, and health/backup responses**.

**Prerequisites:**
- Docker Compose stack running: `docker compose up -d`
- Test users (create before testing):
  - `seeker@test.com` / `Password1!` — role: seeker
  - `landlord@test.com` / `Password1!` — role: landlord
  - `admin@test.com` / `Password1!` — role: admin (MFA enabled)

> **Note**: `storage/` is a Docker named volume (`backend-storage`), not a bind mount.
> The `backend/storage/logs` folder on the host is **empty** — logs live inside the container.

**How to follow the structured log (run in a separate terminal):**
```bash
docker compose exec backend tail -f storage/logs/structured-$(date +%F).log
```

Each entry is a JSON object. Relevant fields: `action`, `user_id`, `ip`, `security_event`.

---

## T-01 — Log a failed login (auth.login_failed)

**Goal**: Verify that a failed login generates a structured log entry.

| # | Action | Expected result |
|---|---|---|
| 1 | Open `/login` | Login form |
| 2 | Enter `seeker@test.com` + wrong password, click **Sign in** | "Invalid credentials" error on the page |
| 3 | Check the structured log | Entry with `"action": "auth.login_failed"`, `"security_event": true`, `"attempt_count": 1`, `"ip": "<your IP>"` |

---

## T-02 — Log a brute-force lockout (auth.brute_force_lockout)

**Goal**: Verify that 10 consecutive failed attempts generate a lockout log entry.

**Note**: `LOGIN_MAX_ATTEMPTS=10` (can be temporarily reduced to 3 for testing).

| # | Action | Expected result |
|---|---|---|
| 1 | Repeat failed login for the same email 10× | After the 10th attempt: "Too many login attempts" error |
| 2 | Check the structured log | Entry with `"action": "auth.brute_force_lockout"`, `"security_event": true`, `"attempt_count": 10` |
| 3 | Attempt to log in immediately again (even with correct credentials) | Entry with `"action": "auth.login_blocked"`, `"security_event": true` |

---

## T-03 — Log a failed MFA attempt (auth.mfa_failed)

**Prerequisite**: `admin@test.com` has MFA enabled.

| # | Action | Expected result |
|---|---|---|
| 1 | Sign in as `admin@test.com` | MFA challenge screen shown |
| 2 | Enter a wrong 6-digit code, click **Confirm** | "Invalid MFA code" error |
| 3 | Check the structured log | Entry with `"action": "auth.mfa_failed"`, `"security_event": true`, `"used_recovery_code": false`, `"user_id": <admin ID>` |
| 4 | Repeat with a non-existent recovery code | Entry with `"used_recovery_code": true` |

---

## T-04 — Log account deletion (auth.account_deleted)

**Prerequisite**: Create a test user `delete_me@test.com` / `Password1!`.

| # | Action | Expected result |
|---|---|---|
| 1 | Sign in as `delete_me@test.com` | Successful login |
| 2 | Go to **Settings → Account** | Account deletion section |
| 3 | Click **Delete account**, enter password `Password1!`, confirm | Logged out and redirected to `/` |
| 4 | Check the structured log | Entry with `"action": "auth.account_deleted"`, `"security_event": true`, `"user_id": <ID>` |

---

## T-05 — Log a session revocation (auth.session_revoked)

| # | Action | Expected result |
|---|---|---|
| 1 | Sign in as `seeker@test.com` in **two different browsers/incognito windows** | Both signed in |
| 2 | In browser A: go to **Settings → Security → Active sessions** | Session list — 2+ sessions visible |
| 3 | Click **Sign out** next to the session from browser B | Session removed from the list |
| 4 | Check the structured log | Entry with `"action": "auth.session_revoked"`, `"security_event": true`, `"self_revocation": false` |

---

## T-06 — Log a bulk session revocation (auth.sessions_bulk_revoked)

| # | Action | Expected result |
|---|---|---|
| 1 | Sign in as `seeker@test.com` in 3 browsers/incognito windows | All three signed in |
| 2 | In one browser: go to **Settings → Security**, click **Sign out all other sessions** | The other 2 sessions are revoked |
| 3 | Check the remaining browsers | Redirected to `/login` |
| 4 | Check the structured log | Entry with `"action": "auth.sessions_bulk_revoked"`, `"revoked_count": 2` |

---

## T-07 — Log admin impersonation (auth.impersonation_started / stopped)

**Prerequisite**: Admin impersonation feature is available from the admin panel.

| # | Action | Expected result |
|---|---|---|
| 1 | Sign in as `admin@test.com` (with MFA) | Admin dashboard |
| 2 | Go to **Admin → Users**, find `seeker@test.com`, click **Impersonate** | Signed in as seeker, banner "Impersonating user X" |
| 3 | Check the structured log | Entry with `"action": "auth.impersonation_started"`, `"admin_id": <admin ID>`, `"target_user_id": <seeker ID>` |
| 4 | Click **Stop impersonation** | Returned to admin account |
| 5 | Check the structured log | Entry with `"action": "auth.impersonation_stopped"` |

---

## T-08 — Log admin KYC document access (kyc.document_accessed_by_admin)

**Prerequisite**: A seeker has uploaded a KYC document.

| # | Action | Expected result |
|---|---|---|
| 1 | Sign in as `admin@test.com` | Admin dashboard |
| 2 | Go to **Admin → KYC** | KYC submission list |
| 3 | Open `seeker@test.com`'s submission, click the document (image/PDF) | Document displayed |
| 4 | Check the structured log | Entry with `"action": "kyc.document_accessed_by_admin"`, `"security_event": true`, `"admin_id": <admin ID>`, `"owner_id": <seeker ID>` |

---

## T-09 — Health endpoint: storage disk check

**Goal**: Verify that `/health/ready` includes the storage disk status (added in Phase 6).

| # | Action | Expected result |
|---|---|---|
| 1 | Open in browser or via `curl`: `http://localhost:8000/api/v1/health/ready` | JSON response |
| 2 | Check the `checks.storage` field in the response | `{"ok": true, "disks": {"private": true, "public": true}}` |
| 3 | (Optional) Temporarily remove permissions on the storage directory, repeat the request | `{"ok": false, ...}`, HTTP status 503 |

**Example expected response:**
```json
{
  "status": "ok",
  "checks": {
    "database": true,
    "cache": true,
    "queue": { "ok": true, "failed_jobs": 0 },
    "storage": { "ok": true, "disks": { "private": true, "public": true } }
  }
}
```

---

## T-10 — Backup verification command

**Goal**: Verify the `php artisan backup:verify` command.

| # | Action | Expected result |
|---|---|---|
| 1 | Run without a backup directory: `php artisan backup:verify --backup-dir=/tmp/nonexistent` | Error: "Backup directory does not exist", exit code 1 |
| 2 | Create a test backup: `mkdir -p /tmp/test_backups && gzip -c /dev/null > /tmp/test_backups/test.sql.gz` | File created |
| 3 | Run: `php artisan backup:verify --backup-dir=/tmp/test_backups` | "Backup OK — test.sql.gz (0.0 hours old)" |
| 4 | Check the structured log | Entry with `"action": "backup.verified"`, `"backup_file": "test.sql.gz"` |
| 5 | Create a stale backup: `touch -d '2 days ago' /tmp/test_backups/old.sql.gz` and remove the new one | Error: "Latest backup is stale", exit code 1 |
| 6 | Check the structured log for the stale backup | Entry with `"action": "backup.stale"`, `"security_event": true` |

---

## T-11 — Sentry user context (backend middleware)

**Prerequisite**: Sentry is configured (`SENTRY_ENABLED=true`, valid `SENTRY_DSN`) — **test in staging environment only**.

| # | Action | Expected result |
|---|---|---|
| 1 | Sign in as `seeker@test.com` | Successful login |
| 2 | Trigger a Sentry error (e.g. access a non-existent resource that throws an exception) | Error appears in the Sentry dashboard |
| 3 | Open the event in the Sentry UI | Field `user.id` contains the seeker ID, `user.segment` contains "seeker" — **no email, no phone** |

---

## T-12 — Fraud signal: KYC multi-user IP

**Goal**: Verify that KYC submission from the same IP for 2+ users triggers a fraud signal.

**Note**: `FRAUD_SIGNAL_KYC_MULTI_USER_IP_THRESHOLD=2` (default value).

| # | Action | Expected result |
|---|---|---|
| 1 | Sign in as `seeker@test.com`, submit a KYC document | KYC submission created |
| 2 | Sign out, sign in as `landlord@test.com` (same IP), submit KYC | KYC submission created |
| 3 | Check the structured log | Entry with `"action": "fraud.signal_recorded"`, `"signal": "kyc_multi_user_ip"`, `"ip_hash": "..."` |
| 4 | Check admin notifications (Admin → Notifications) | New notification about the fraud signal for `landlord@test.com` |
| 5 | Check the fraud score for `landlord@test.com` in the admin panel | Score increased by 20 points |

---

## T-13 — Fraud signal: rapid attachment uploads

**Goal**: Verify that exceeding the attachment upload rate limit triggers a fraud signal.

**Note**: `CHAT_ATTACHMENTS_PER_10_MINUTES=10` — temporarily reduce to 2 for testing.

| # | Action | Expected result |
|---|---|---|
| 1 | Sign in as `seeker@test.com`, open a chat thread | Chat interface |
| 2 | Upload an attachment 3× in quick succession (if limit=2) | Third upload rejected with a rate limit error |
| 3 | Check the structured log | Entry with `"action": "fraud.signal_recorded"`, `"signal": "rapid_uploads"`, `"user_id": <seeker ID>` |
| 4 | Check the fraud score for `seeker@test.com` in the admin panel | Score increased by 5 points |
