# Authentication & Session Security

> Status: `implemented`
> Poslednja strukturna provera: 2026-07-15
> Source of truth: Auth/Security kontroleri, middleware, config i testovi

> **Docker napomena:** U produkcijskom/staging okruženju sve `php artisan` komande pokretati kao:
> ```bash
> DC="docker compose -p izdaji_prod --env-file .env.production.compose -f docker-compose.production.yml"
> $DC exec backend php artisan <komanda>
> ```
> `php artisan tinker --execute="..."` ne ispisuje return value automatski — koristiti `echo` ili `var_dump`.


## MFA Enforcement Rules

### Per-Role Policy

| Role    | MFA Required | Enforcement Point |
|---------|-------------|-------------------|
| admin   | **Yes** (configurable via `REQUIRE_MFA_FOR_ADMINS`) | `RequireMfaForAdmin` middleware + `EnsureMfaVerified` middleware on all `/admin/*` routes |
| landlord | No (voluntary) | User-initiated setup only |
| seeker   | No (voluntary) | User-initiated setup only |

### Admin MFA Gate (`REQUIRE_MFA_FOR_ADMINS`)

Set in `config/security.php` via `REQUIRE_MFA_FOR_ADMINS` env var.

- Default in dev: `false`
- **Required in production/staging: `true`**

When `REQUIRE_MFA_FOR_ADMINS=true`:
1. `RequireMfaForAdmin` (`admin_mfa` alias) — blocks any admin whose account does not have MFA **enabled and confirmed** (`mfa_enabled=true AND mfa_confirmed_at IS NOT NULL`). Returns `403`.
2. `EnsureMfaVerified` (`mfa` alias) — blocks any authenticated user (admin or not) who has MFA enabled but has not completed the MFA challenge in the **current session** (`mfa_verified_at` not in session). Returns `403`.

Both middlewares are applied to the `Route::middleware(['role:admin', 'admin_mfa', 'mfa'])` group covering all admin API routes.

### Voluntary MFA Flow (all users)

1. `POST /api/v1/security/mfa/setup` — generate TOTP secret + QR + recovery codes
2. `POST /api/v1/security/mfa/confirm` — confirm with first TOTP code; stamps `mfa_confirmed_at`
3. On next login: server returns `202 { mfa_required: true, challenge_id }` if device is not trusted
4. `POST /api/v1/security/mfa/verify` — submit `{ challenge_id, code | recovery_code, remember_device? }`
5. On success: session stamped with `mfa_verified_at`

---

## Session Lifecycle

### Creation
- Session created on `POST /auth/login` or `POST /auth/register`
- `session()->regenerate()` called to prevent session fixation
- `SecuritySessionService::recordSession()` stores metadata in `user_sessions` table: truncated IP, user agent, device fingerprint, device label

### Expiry
- Idle lifetime: `SESSION_LIFETIME` minutes (default 120; configurable per env)
- `SESSION_EXPIRE_ON_CLOSE=false` by default — sessions persist across browser closes
- Laravel session garbage collection runs at lottery odds (2/100 requests)

### Revocation Triggers

| Trigger | Sessions Revoked | Implementation |
|---------|-----------------|----------------|
| User logs out | Current session only | `AuthController::logout` → `session()->invalidate()` + `UserSession` record deleted |
| User changes password | **All other sessions** | `UserAccountController::updatePassword` → `SecuritySessionService::revokeOtherSessions()` |
| Admin sets `is_suspicious = true` via flag-suspicious route | **All sessions for flagged user** | `RatingAdminController::flagUser` → `SecuritySessionService::revokeAllSessions()` |
| Admin flags user via moderation report resolution (`flag_user_id`) | **All sessions for flagged user** | `ModerationController::update` → `SecuritySessionService::revokeAllSessions()` |
| Admin explicitly revokes all sessions | All sessions for target user | `POST /api/v1/admin/users/{user}/sessions/revoke-all` |
| User revokes a specific session | That session only | `POST /api/v1/security/sessions/{id}/revoke` |
| User revokes all other sessions | All sessions except current | `POST /api/v1/security/sessions/revoke-others` |

`revokeAllSessions` / `revokeOtherSessions` deletes from both the `sessions` table (Laravel native) and the `user_sessions` tracking table.

---

## Brute-Force Protection

### Login Lockout (per account)

Tracked via Cache (key: `login_lockout:{sha256(email)}`).

| Parameter | Env var | Default |
|-----------|---------|---------|
| Max failed attempts before lockout | `LOGIN_MAX_ATTEMPTS` | 10 |
| Lockout window | `LOGIN_LOCKOUT_MINUTES` | 15 |

**Behaviour:**
- On each failed `POST /auth/login`: counter incremented, TTL set to `LOGIN_LOCKOUT_MINUTES` from that failure
- When counter ≥ `LOGIN_MAX_ATTEMPTS`: next attempt returns `429` with `retry_after_minutes` (i.e. the **N+1th** request is blocked — first N failures still return `401`)
- On successful login: counter cleared with `Cache::forget`

**Manual lockout reset (za testove):**
```bash
# lokalno
php artisan tinker --execute="echo Cache::forget('login_lockout:'.hash('sha256','email@example.com')) ? 'cleared' : 'not found';"

# Docker
$DC exec backend php artisan tinker --execute="echo Cache::forget('login_lockout:'.hash('sha256','email@example.com')) ? 'cleared' : 'not found';"
```

### Login Rate Limit (per IP)

`throttle:auth` rate limiter applied at the route level:
- 30 requests/minute per IP (defined in `AppServiceProvider`)
- Covers both `POST /auth/login` and `POST /auth/register`

### MFA Verify Rate Limit

`throttle:mfa_verify`: 5 attempts/minute per `user_id + IP`.
- On exceeding: fraud signal `failed_mfa_rate_limited` recorded via `FraudSignalService`
- Admin notified via `admin.notice` notification

### MFA Failed Attempts (fraud signal)

`FraudSignalService::recordFailedMfaAttempt()`:
- Tracks failures in Cache: key `fraud:failed_mfa:{user_id}`
- After `FRAUD_SIGNAL_FAILED_MFA_THRESHOLD` (default 3) failures within `FRAUD_SIGNAL_FAILED_MFA_WINDOW` (default 10) minutes:
  - Signal `failed_mfa` recorded with weight `FRAUD_SIGNAL_FAILED_MFA_WEIGHT` (default 8)
  - Cooldown: `FRAUD_SIGNAL_FAILED_MFA_COOLDOWN` (default 30 minutes)

---

## Trusted Device Flow and TTL

### Registration
- When user completes MFA and sends `remember_device: true` in the verify payload:
  - `SecuritySessionService::rememberDevice()` called
  - Device fingerprint (SHA-256 of `X-Device-Id | User-Agent | IP/24`) stored in `trusted_devices`
  - `expires_at` = now + `TRUSTED_DEVICE_TTL_DAYS` (default 30 days)

### Trust Check
- On login: `SecuritySessionService::isTrustedDevice()` checks fingerprint exists AND `expires_at > now()`
- Expired records are treated as untrusted — MFA challenge issued

### Cleanup
- `php artisan trusted-devices:purge` deletes all `trusted_devices` rows where `expires_at <= now()`
- Scheduled **daily at 04:30** in `bootstrap/app.php`
- Docker: `$DC exec backend php artisan trusted-devices:purge`

---

## Cookie Security Settings

| Setting | Config key | Env var | Dev default | Production required |
|---------|-----------|---------|-------------|---------------------|
| HTTPS only | `session.secure` | `SESSION_SECURE_COOKIE` | `false` | **`true`** |
| JavaScript inaccessible | `session.http_only` | `SESSION_HTTP_ONLY` | `true` | `true` |
| SameSite policy | `session.same_site` | `SESSION_SAME_SITE` | `lax` | `lax` |
| Session encryption at rest | `session.encrypt` | `SESSION_ENCRYPT` | `false` | `true` |

All settings are in `config/session.php` and driven by environment variables — no code changes needed for production hardening; set the env vars correctly.

---

## How to Manually Revoke All Sessions for a User

### Via Admin API
```
POST /api/v1/admin/users/{user_id}/sessions/revoke-all
Authorization: (admin session cookie)
```
Requires: authenticated admin, MFA verified this session (`REQUIRE_MFA_FOR_ADMINS=true` enforced).

Returns: `{ "message": "Sessions revoked." }`

### Via Artisan (CLI)
```bash
# Interaktivni tinker (lokalno)
php artisan tinker
>>> $user = App\Models\User::find(42);
>>> echo app(App\Services\SecuritySessionService::class)->revokeAllSessions($user);

# --execute forma (lokalno ili Docker)
php artisan tinker --execute="
  \$u = App\Models\User::find(42);
  echo app(App\Services\SecuritySessionService::class)->revokeAllSessions(\$u) . ' session(s) revoked';
"

# Docker
$DC exec backend php artisan tinker --execute="
  \$u = App\Models\User::find(42);
  echo app(App\Services\SecuritySessionService::class)->revokeAllSessions(\$u) . ' session(s) revoked';
"
```

### Via direct DB (emergency)
```sql
-- Get all session IDs for the user
SELECT session_id FROM user_sessions WHERE user_id = 42;

-- Delete from Laravel sessions table
DELETE FROM sessions WHERE id IN (
    SELECT session_id FROM user_sessions WHERE user_id = 42
);

-- Delete tracking records
DELETE FROM user_sessions WHERE user_id = 42;
```

---

## CSRF Token Handling (Frontend)

The SPA Axios client (`frontend/src/services/apiClient.ts`) handles CSRF automatically:

- `withCredentials: true` — cookies are included on all requests
- `xsrfCookieName: 'XSRF-TOKEN'` / `xsrfHeaderName: 'X-XSRF-TOKEN'` — Axios reads the cookie and injects the header
- Manual request interceptor also reads `XSRF-TOKEN` via `readCookie()` as a fallback

### 419 Auto-Retry

When any request fails with HTTP 419 (CSRF token mismatch — e.g., after session expiry or backend restart):

1. `ensureCsrfCookie()` is called automatically (`GET /sanctum/csrf-cookie`)
2. The original request is retried once with the fresh token
3. The `_csrfRetry` flag prevents infinite retry loops

`ensureCsrfCookie()` is also called explicitly before login, register, logout, and admin impersonation flows.

### curl Testing Requirements

When testing Sanctum-protected endpoints with curl, include these headers on every request:

```bash
-H "Origin: https://izdajiznajmi.com"   # Sanctum stateful domain match
-H "Referer: https://izdajiznajmi.com/"  # fallback for Sanctum origin check
-H "Accept: application/json"            # prevents Laravel redirect responses
```

Without `Origin`, Sanctum skips adding session middleware → `$request->session()` throws a `RuntimeException` → 500.
Without `Accept: application/json`, Laravel returns an HTML redirect on validation failure instead of JSON 422.

**Full curl login flow:**

```bash
BASE="https://izdajiznajmi.com"
COOKIES="cookies.txt"
rm -f $COOKIES

# 1. Init session
curl -s -c $COOKIES -b $COOKIES \
  -H "Origin: $BASE" -H "Referer: $BASE/" \
  "$BASE/sanctum/csrf-cookie" > /dev/null

# 2. Extract XSRF-TOKEN (URL-decode)
XSRF=$(grep XSRF-TOKEN $COOKIES | awk '{print $NF}' | \
  python3 -c "import sys,urllib.parse; print(urllib.parse.unquote(sys.stdin.read().strip()))")

# 3. Login
curl -s -c $COOKIES -b $COOKIES \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -H "Origin: $BASE" -H "Referer: $BASE/" \
  -H "X-XSRF-TOKEN: $XSRF" \
  -X POST "$BASE/api/v1/auth/login" \
  -d '{"email":"user@example.com","password":"secret"}'

# 4. Refresh XSRF after login (server may rotate it)
XSRF=$(grep XSRF-TOKEN $COOKIES | awk '{print $NF}' | \
  python3 -c "import sys,urllib.parse; print(urllib.parse.unquote(sys.stdin.read().strip()))")
```

---

## Emergency MFA Reset (CLI)

When a user loses access to their authenticator app and cannot log in:

```bash
# Docker (production)
docker exec izdajiznajmiv2-backend-1 php artisan tinker --execute="
\$u = App\Models\User::where('email', 'user@example.com')->first();
\$u->mfa_enabled = false;
\$u->mfa_secret = null;
\$u->mfa_recovery_codes = null;
\$u->mfa_confirmed_at = null;
\$u->save();
echo 'MFA reset for: ' . \$u->email;
"
```

After reset the user can log in without MFA and re-enroll via `/settings/security`.

---

## Regression Test Cases

### MFA Enforcement — Admin

1. **Admin without MFA tries to access admin route**
   - `REQUIRE_MFA_FOR_ADMINS=true`
   - Admin user has `mfa_enabled=false`
   - `GET /api/v1/admin/users` → expect `403 { mfa_required: true }`

2. **Admin with MFA enabled but not verified this session**
   - Admin has `mfa_enabled=true`, `mfa_confirmed_at` set
   - Session does NOT have `mfa_verified_at`
   - `GET /api/v1/admin/users` → expect `403 { mfa_required: true }`

3. **Admin with MFA verified this session**
   - Session has `mfa_verified_at`
   - `GET /api/v1/admin/users` → expect `200`

4. **Gate disabled (`REQUIRE_MFA_FOR_ADMINS=false`)**
   - Admin without MFA enabled
   - `GET /api/v1/admin/users` → expect `200` (gate bypassed, role check still applies)

### Password Change — Session Revocation

5. **Password change revokes other sessions**
   - User has 3 active sessions (A, B, C); request made from session A
   - `PATCH /api/v1/me/password` → expect sessions B and C deleted from `sessions` + `user_sessions` tables
   - Session A still valid, response `200 { message: "Password updated" }`

### Suspicious Flag — Session Revocation

6. **Flag user as suspicious revokes all sessions**
   - User has 2 active sessions
   - Admin calls `PATCH /api/v1/admin/users/{user}/flag-suspicious` with `{ "is_suspicious": true }`
   - Expect both sessions deleted from `sessions` + `user_sessions`

7. **Clear suspicion does NOT revoke sessions**
   - Admin calls `PATCH /api/v1/admin/users/{user}/flag-suspicious` with `{ "is_suspicious": false }`
   - Existing sessions not touched

8. **Moderation report resolution with `flag_user_id` revokes all sessions**
   - User has 2 active sessions
   - Admin resolves a report via `PATCH /api/v1/admin/moderation/reports/{report}` with `{ "action": "resolve", "flag_user_id": <user_id> }`
   - Expect both sessions deleted from `sessions` + `user_sessions`

### Brute-Force Lockout

9. **Lockout after N failed attempts**
   - Make `LOGIN_MAX_ATTEMPTS` (e.g. 10) failed login attempts for the same email
   - Next attempt → expect `429 { message: "Too many failed login attempts...", retry_after_minutes: 15 }`
   - Wait for lockout window to expire → login succeeds with correct credentials

10. **Counter resets on success**
    - Make 5 failed attempts, then 1 successful login
    - Counter cleared; can fail again up to `LOGIN_MAX_ATTEMPTS` before next lockout

11. **Lockout is per email, not per IP**
    - Same email from two different IPs: both contribute to the same counter
    - Different emails from the same IP: independent counters

### Trusted Device TTL

12. **Expired trusted device triggers MFA challenge**
    - Manually set `expires_at` to a past timestamp in `trusted_devices`
    - Login → expect `202 { mfa_required: true }` despite previously trusted fingerprint

13. **Non-expired trusted device skips MFA**
    - `expires_at` is in the future
    - Login → expect `200` (no MFA challenge)

14. **`trusted-devices:purge` removes only expired records**
    - Create 2 devices: one expired, one valid
    - Run `php artisan trusted-devices:purge`
    - Expired record deleted, valid record remains
