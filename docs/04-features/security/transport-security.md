# Transport & Infrastructure Security

> Status: `implemented reference`; TLS edge zavisi od deployment topologije
> Poslednja strukturna provera: 2026-07-15
> Source of truth: middleware/config, Nginx fajlovi i header testovi

## Overview

This document covers TLS configuration, security response headers, Content Security Policy, rate limits on authentication endpoints, and the environment variables that control each feature.

Two deployment configurations exist:
- **`ops/nginx-site.conf`** — bare-metal / VPS deployment (Nginx handles TLS directly)
- **`ops/nginx-docker-production.conf`** — Docker Compose deployment (TLS terminated upstream by a load balancer or reverse proxy)

Laravel's `SecurityHeadersMiddleware` emits headers on every response, complementing what Nginx adds at the edge.

---

## TLS Configuration (bare-metal)

**File:** `ops/nginx-site.conf`

```nginx
ssl_protocols TLSv1.2 TLSv1.3;
ssl_prefer_server_ciphers on;
ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384;
ssl_session_cache shared:SSL:10m;
ssl_session_timeout 1d;
ssl_session_tickets off;
ssl_stapling on;
ssl_stapling_verify on;
```

- **Protocols:** TLS 1.2 and TLS 1.3 only. SSLv3, TLS 1.0, and TLS 1.1 are disabled.
- **Cipher suites:** ECDHE-first with AES-GCM and ChaCha20-Poly1305. No RC4, no 3DES, no export ciphers.
- **Session tickets disabled** to preserve forward secrecy across server restarts.
- **OCSP stapling** enabled to reduce handshake latency.

For Docker deployments, TLS is terminated by the upstream load balancer. The `X-Forwarded-Proto: https` header must be set by the LB and is forwarded to Laravel via `proxy_set_header X-Forwarded-Proto $scheme`.

---

## Security Headers

Headers are applied at two levels:

### Nginx level (both configs)

| Header | Value |
|--------|-------|
| `X-Content-Type-Options` | `nosniff` |
| `X-Frame-Options` | `SAMEORIGIN` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=(self), payment=(), usb=(), interest-cohort=()` |

Additionally, the bare-metal HTTPS config (`nginx-site.conf`) adds:

| Header | Value |
|--------|-------|
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains; preload` |
| `Content-Security-Policy` | See CSP section below |

### Laravel level (`SecurityHeadersMiddleware`)

The middleware runs on every request via the global middleware stack (`bootstrap/app.php`). It emits the same baseline headers as Nginx and additionally handles:
- **HSTS** — only when the request arrived over HTTPS (via `isSecure()` or `X-Forwarded-Proto: https`)
- **CSP** — enforce or report-only mode, with per-request nonce support

All header values are controlled by environment variables (see [Environment Variables](#environment-variables)).

---

## Content Security Policy

### Production policy (`SECURITY_CSP_POLICY`)

```
default-src 'self';
script-src 'self' 'nonce-{nonce}';
style-src 'self' 'unsafe-inline';
img-src 'self' data: blob: https:;
font-src 'self' data:;
connect-src 'self' wss://api.izdaji.example https://api.izdaji.example;
base-uri 'self';
object-src 'none';
frame-ancestors 'self';
form-action 'self';
upgrade-insecure-requests
```

### How the nonce works

`SecurityHeadersMiddleware` generates a cryptographically random nonce on every request:

```php
$nonce = base64_encode(random_bytes(16));
$request->attributes->set('csp_nonce', $nonce);
```

Any `{nonce}` placeholder in `SECURITY_CSP_POLICY` is replaced with the actual nonce value before the header is sent. This allows inline scripts to be whitelisted per-request without relaxing the policy globally.

To read the nonce in a controller or view:
```php
$nonce = $request->attributes->get('csp_nonce');
// or in a Blade template:
// {{ request()->attributes->get('csp_nonce') }}
```

### Moving from report-only to enforce mode

| `SECURITY_CSP_REPORT_ONLY` | Header sent |
|---|---|
| `true` | `Content-Security-Policy-Report-Only` |
| `false` | `Content-Security-Policy` |

Before switching to enforce mode in production:
1. Deploy with `SECURITY_CSP_REPORT_ONLY=true` and `SECURITY_CSP_ENABLED=true`.
2. Monitor browser console and any CSP report endpoint for violations.
3. Fix violations in the policy or frontend code.
4. Set `SECURITY_CSP_REPORT_ONLY=false` to enforce.

To update the policy, change `SECURITY_CSP_POLICY` in the server environment — no code deploy required.

---

## HSTS (HTTP Strict Transport Security)

| Setting | Production value |
|---------|-----------------|
| `max-age` | `31536000` (1 year) |
| `includeSubDomains` | yes |
| `preload` | yes |

`preload` requires the domain to be submitted to the [HSTS preload list](https://hstspreload.org/). Only enable it when the entire domain is served over HTTPS.

HSTS from the Laravel middleware is only emitted when:
- `SECURITY_HSTS_ENABLED=true`
- The request arrived over HTTPS (direct or via `X-Forwarded-Proto: https`)
- `SECURITY_HSTS_PROD_ONLY=true` and `APP_ENV=production`

In the bare-metal Nginx config, HSTS is emitted directly from the `ssl` server block, so the Laravel middleware HSTS can optionally be left disabled to avoid duplication. In the Docker setup (no TLS at Nginx), rely on the Laravel middleware.

---

## Rate Limits on Auth Endpoints

All three sensitive auth endpoints are rate limited in `AppServiceProvider::boot()`.

| Endpoint | Throttle key | Limit | Key basis |
|----------|-------------|-------|-----------|
| `POST /api/v1/auth/login` | `auth` | 30 / minute | IP |
| `POST /api/v1/auth/register` | `auth` | 30 / minute | IP |
| `POST /api/v1/security/mfa/verify` | `mfa_verify` | 5 / minute | user ID + IP |
| `POST /api/v1/security/mfa/setup` | `mfa_sensitive` | 5 / minute | user ID + IP |
| `POST /api/v1/security/mfa/confirm` | `mfa_sensitive` | 5 / minute | user ID + IP |
| `POST /api/v1/security/mfa/disable` | `mfa_sensitive` | 5 / minute | user ID + IP |

Exceeding the `mfa_verify` limit also records a `failed_mfa` fraud signal against the user.

Rate limiters are defined in [AppServiceProvider.php](../../../backend/app/Providers/AppServiceProvider.php).

---

## Environment Variables

### Security headers

| Variable | Default (local) | Production | Description |
|----------|----------------|------------|-------------|
| `SECURITY_HEADERS_ENABLED` | `true` | `true` | Master switch for all Laravel-emitted headers |
| `SECURITY_X_CONTENT_TYPE_OPTIONS` | `nosniff` | `nosniff` | Value for `X-Content-Type-Options` |
| `SECURITY_X_FRAME_OPTIONS` | `SAMEORIGIN` | `SAMEORIGIN` | Value for `X-Frame-Options` |
| `SECURITY_REFERRER_POLICY` | `strict-origin-when-cross-origin` | `strict-origin-when-cross-origin` | Value for `Referrer-Policy` |
| `SECURITY_PERMISSIONS_POLICY` | `camera=(), ...` | `camera=(), ...` | Value for `Permissions-Policy` |

### HSTS

| Variable | Default (local) | Production | Description |
|----------|----------------|------------|-------------|
| `SECURITY_HSTS_ENABLED` | `false` | `true` | Enable HSTS header emission |
| `SECURITY_HSTS_MAX_AGE` | `31536000` | `31536000` | `max-age` in seconds |
| `SECURITY_HSTS_INCLUDE_SUBDOMAINS` | `true` | `true` | Add `includeSubDomains` directive |
| `SECURITY_HSTS_PRELOAD` | `false` | `true` | Add `preload` directive |
| `SECURITY_HSTS_PROD_ONLY` | `true` | `true` | Only emit in `APP_ENV=production` |

### CSP

| Variable | Default (local) | Production | Description |
|----------|----------------|------------|-------------|
| `SECURITY_CSP_ENABLED` | `false` | `true` | Enable CSP header |
| `SECURITY_CSP_REPORT_ONLY` | `true` | `false` | `true` = report-only, `false` = enforce |
| `SECURITY_CSP_POLICY` | `default-src 'self'; ...` | Full policy with `nonce-{nonce}` | Full CSP policy string. Use `{nonce}` as placeholder for per-request nonce. |

---

## Verifying Headers in Production

Use `curl` to verify headers without following redirects:

```bash
# Check HTTPS redirect (bare-metal)
curl -I http://app.izdaji.example

# Check TLS and all security headers
curl -I https://app.izdaji.example

# Check API response headers
curl -I https://api.izdaji.example/up

# Verify TLS version and cipher (requires openssl)
openssl s_client -connect app.izdaji.example:443 -tls1_1 2>&1 | grep -E "Protocol|Cipher"
# Expected: "no peer certificate available" or handshake failure — TLS 1.1 must be rejected

openssl s_client -connect app.izdaji.example:443 -tls1_2 2>&1 | grep -E "Protocol|Cipher"
# Expected: successful connection with TLS 1.2

# Verify HSTS
curl -sI https://app.izdaji.example | grep -i strict-transport

# Verify CSP
curl -sI https://api.izdaji.example/up | grep -i content-security-policy

# Verify Permissions-Policy
curl -sI https://app.izdaji.example | grep -i permissions-policy
```

Expected header values in production:

```
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=(self), payment=(), usb=(), interest-cohort=()
Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-<base64>'; ...
```

---

## Deploy Checklist

Before every production deploy, verify:

- [ ] `APP_DEBUG=false`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `SESSION_HTTP_ONLY=true`
- [ ] `SESSION_SAME_SITE=lax`
- [ ] `SESSION_ENCRYPT=true`
- [ ] `SECURITY_HEADERS_ENABLED=true`
- [ ] `SECURITY_HSTS_ENABLED=true`
- [ ] `SECURITY_HSTS_PRELOAD=true` (only after confirming domain is on preload list or ready to submit)
- [ ] `SECURITY_CSP_ENABLED=true`
- [ ] `SECURITY_CSP_REPORT_ONLY=false` (after policy validation period)
- [ ] `SECURITY_CSP_POLICY` contains the correct `connect-src` origins (update if API domain changes)
- [ ] TLS certificate is valid and not expiring within 30 days
- [ ] `ssl_protocols` in Nginx does **not** include TLSv1 or TLSv1.1
- [ ] Run `curl -I https://app.izdaji.example` and confirm all expected headers are present
- [ ] Run `openssl s_client -connect app.izdaji.example:443 -tls1_1` and confirm connection is rejected
