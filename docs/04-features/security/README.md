# Security indeks

> Status: `implemented`, kontinuirani hardening
> Poslednja strukturna provera: 2026-07-15
> Source of truth: middleware, policies, config, services i security testovi

Ovaj indeks sažima aktivne kontrole. Tematski dokumenti daju detalje:

- [Auth i session security](auth-session-security.md)
- [API autorizacija i IDOR](api-authorization.md)
- [KYC document security](kyc-document-security.md)
- [Data privacy](data-privacy.md)
- [Transport i infrastruktura](transport-security.md)
- [Monitoring i detekcija](monitoring-detection.md)
- [Dependency/code hygiene](dependency-code-hygiene.md)
- [Admin log viewer](admin-log-viewer.md)

## MFA (TOTP)

### Setup & Confirm
1. `POST /api/v1/security/mfa/setup`
   - Returns `otpauth_url`, `qr_svg`, `secret`, and one-time `recovery_codes`.
2. `POST /api/v1/security/mfa/confirm`
   - Payload: `{ code }`
   - Enables MFA and stamps `mfa_confirmed_at`.

### Login Challenge Flow
- `POST /api/v1/auth/login`
  - If MFA is enabled and device is not trusted, returns `202` with `{ mfa_required: true, challenge_id }`.
- `POST /api/v1/security/mfa/verify`
  - Payload: `{ challenge_id, code | recovery_code, remember_device? }`
  - On success completes login and marks the session as verified.

### Disable MFA
- `POST /api/v1/security/mfa/disable`
  - Requires `password` + `code` or `recovery_code`.

### Recovery Codes
- One-time use, stored hashed.
- `POST /api/v1/security/mfa/recovery-codes` to regenerate (requires a valid TOTP or recovery code).

### Trusted Devices
- When `remember_device` is true, the device fingerprint is stored and expires by `TRUSTED_DEVICE_TTL_DAYS`.

## Session & Device History

### User Endpoints
- `GET /api/v1/security/sessions`
- `POST /api/v1/security/sessions/{id}/revoke`
- `POST /api/v1/security/sessions/revoke-others`

### Admin Endpoints
- `GET /api/v1/admin/users/{user}/security`
- `GET /api/v1/admin/users/{user}/sessions`
- `POST /api/v1/admin/users/{user}/sessions/revoke-all`

## Anti-Fraud Signals

Signals are stored in `fraud_signals` and summed over the last 30 days (configurable).

Current signal keys:
- `failed_mfa`
- `rapid_messages`
- `rapid_applications`
- `duplicate_address_attempt`
- `session_anomaly`

When the score meets/exceeds `FRAUD_SCORE_THRESHOLD`, the user is marked `is_suspicious` and admins receive an `admin.notice` notification.

## Admin Controls

- MFA enforcement for admins is controlled by `REQUIRE_MFA_FOR_ADMINS`.
- Admin UI can review fraud scores, signals, MFA status, and sessions; actions include session revoke and clearing suspicion.

## Privacy Notes

- Device fingerprints are stored as hashed values.
- IPs are stored only in truncated form for new session metadata (IPv4 `/24`, IPv6 `/64`).
- Recovery codes are stored hashed and are one-time use.

## Rate Limits

- `security/mfa/verify` is throttled at 5/min per user+IP.
- MFA setup/confirm/disable/recovery are throttled via `mfa_sensitive`.

## Header and Cookie Hardening Checklist

- Enable baseline response headers (`SECURITY_HEADERS_ENABLED=true`):
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: SAMEORIGIN` (or stricter via CSP `frame-ancestors`)
  - `Referrer-Policy: strict-origin-when-cross-origin`
- HSTS:
  - enable only on HTTPS production (`SECURITY_HSTS_ENABLED=true`)
  - keep `SECURITY_HSTS_PROD_ONLY=true`
- CSP:
  - start with report-only (`SECURITY_CSP_REPORT_ONLY=true`)
  - move to enforce mode after policy validation
- Cookies:
  - `SESSION_SECURE_COOKIE=true` in production
  - `SESSION_SAME_SITE=lax` by default
  - `SESSION_HTTP_ONLY=true`
