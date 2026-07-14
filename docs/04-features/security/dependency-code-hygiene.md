# Dependency & Code Hygiene — Security Reference

> Status: `partial`; verzioni snapshot iz 2026-03-02 može zastareti
> Poslednja strukturna provera: 2026-07-15
> Source of truth: lock fajlovi, CI workflow-i i aktuelni audit output

_Security Phase 7 — last updated 2026-03-02_

---

## 1. Dependency Version Snapshot (2026-03-02)

### Backend (PHP / Composer)

| Package | Constraint | Purpose |
|---|---|---|
| `laravel/framework` | `^12.0` | Core framework |
| `laravel/sanctum` | `^4.2` | SPA authentication (stateful) |
| `laravel/reverb` | `^1.0` | WebSocket server |
| `spatie/laravel-permission` | `^6.24` | RBAC / roles+permissions |
| `minishlink/web-push` | `^9.0` | Push notifications (VAPID) |
| `stripe/stripe-php` | `^19.3` | Payments |
| `spomky-labs/otphp` | `^11.3` | TOTP / MFA |
| `endroid/qr-code` | `^5.0` | QR code generation |
| `intervention/image` | `^3.11` | Image processing/optimisation |
| `barryvdh/laravel-dompdf` | `^3.1` | PDF generation (contracts) |
| `meilisearch/meilisearch-php` | `^0.27.0` | Search index client |
| **Runtime** | `PHP ^8.2` (CI: 8.3) | |

### Frontend (JS / npm)

| Package | Constraint | Purpose |
|---|---|---|
| `vue` | `^3.5.24` | UI framework |
| `vue-router` | `^4.6.4` | Client-side routing |
| `pinia` | `^3.0.4` | State management |
| `@vueuse/core` | `^14.1.0` | Vue composable utilities |
| `axios` | `^1.13.2` | HTTP client |
| `@sentry/vue` | `^8.0.0` | Error monitoring |
| `leaflet` | `^1.9.4` | Maps |
| `vite` | `^7.2.4` | Build tool |
| `typescript` | `~5.9.3` | Type checking |
| `vitest` | `^4.0.18` | Unit testing |
| `@playwright/test` | `^1.49.1` | E2E testing |
| **Runtime** | Node `>=20.0.0` | |

---

## 2. Vulnerability Scanning

### Running Locally

```bash
# Backend — Composer audit
cd backend
composer audit                    # show all advisories
composer audit --no-dev           # production-only packages (use in CI)
composer audit --format=json      # machine-readable output

# Frontend — npm audit
cd frontend
npm audit                         # all severities
npm audit --audit-level=high      # fail only on high/critical
npm audit --omit=dev              # production packages only
npm audit fix                     # auto-fix where possible (review diff!)
npm audit fix --force             # ⚠ may introduce breaking changes — always review
```

### Interpreting Results

**Composer audit:**
- Exits `0` = no advisories
- Exits `1` = advisories found (any severity)
- Review each advisory at https://github.com/advisories — check if the vulnerable code path is reachable in this app before panic-upgrading

**npm audit:**
- `low` / `moderate` — review, schedule fix in next sprint
- `high` / `critical` — fix before merging to `main`
- If a high advisory is in a devDependency only and is not reachable at runtime, it is acceptable to document the exception

### CI Security Gates

All three CI workflows run on every push and pull request to `main`:

| Workflow | File | What it checks |
|---|---|---|
| `backend.yml` | `.github/workflows/backend.yml` | `composer audit --no-dev` — fails on any advisory |
| `frontend.yml` | `.github/workflows/frontend.yml` | `npm audit --audit-level=high --omit=dev` — fails on high/critical |
| `security.yml` | `.github/workflows/security.yml` | Gitleaks (secrets detection) + tracked `.env` files guard |

To temporarily suppress a false positive in `composer audit`:

```json
// composer.json — add to "extra"
"composer-audit": {
    "ignore-cve": ["CVE-XXXX-XXXXX"]
}
```

Document every suppression with a comment explaining why it is safe.

---

## 3. Dependency Update Policy

### Cadence

| Update type | Frequency | Owner | Notes |
|---|---|---|---|
| Security patches (any package) | **Immediately** — within 24 h of advisory | On-call engineer | Skip normal review cycle, but run tests |
| Minor / patch (non-security) | **Weekly** via Dependabot PRs | Team rotation | Review + merge if CI passes |
| Major version upgrades | **Quarterly** planned sprint | Lead developer | Full regression test required |

### Process for Security Patches

1. `composer audit` or `npm audit` output identifies a vulnerability
2. Check if the vulnerable code path is used in this project
3. If yes: create a hotfix branch, update the package, run `php artisan test` / `npm run test`, merge to `main` immediately
4. If no (code path unreachable): document the exception in a comment in `composer.json` or `package.json`, schedule a normal update

### Process for Dependabot PRs

Dependabot is configured in `.github/dependabot.yml` and opens weekly PRs on Monday mornings.

1. Review the PR diff — look for breaking changes in the changelog
2. Verify all CI checks pass on the PR
3. For grouped PRs (e.g. `vue-ecosystem`): spin up the app locally and smoke-test the main user flows
4. Merge once CI is green and no breaking changes are found
5. For major version bumps: Dependabot opens a separate PR — treat these as planned upgrades, not routine merges

### Testing Requirements After Updates

- **Backend packages**: `php artisan test` must pass; manually test affected feature if changed package is security-critical (Sanctum, Spatie Permission, OTPHP)
- **Frontend packages**: `npm run test` + `npm run build` must pass; smoke-test auth flow and listing search if Vue/Pinia/Router updated
- **Stripe / web-push**: always manually verify payment flow / push subscription after upgrade
- **GitHub Actions**: verify the workflow runs correctly on the next push after merging

---

## 4. Secrets Management

### Environment Variable Inventory

The table below lists all required env vars by purpose. **No real values are recorded here.**

| Variable | Purpose | Where to obtain |
|---|---|---|
| `APP_KEY` | Laravel encryption key | `php artisan key:generate` |
| `DB_PASSWORD` | PostgreSQL password | Ops/infra team vault |
| `REDIS_PASSWORD` | Redis auth | Ops/infra team vault |
| `MAIL_USERNAME` / `MAIL_PASSWORD` | SMTP credentials | Email provider control panel |
| `STRIPE_PUBLIC_KEY` / `STRIPE_SECRET_KEY` / `STRIPE_WEBHOOK_SECRET` | Stripe integration | Stripe dashboard |
| `VAPID_PUBLIC_KEY` / `VAPID_PRIVATE_KEY` | Web push (VAPID) | `php artisan webpush:vapid` |
| `MEILISEARCH_KEY` | Meilisearch master key | Set at Meilisearch server startup |
| `REVERB_APP_ID` / `REVERB_APP_KEY` / `REVERB_APP_SECRET` | WebSocket authentication | Generate random strings |
| `SENTRY_DSN` | Sentry error reporting | Sentry project settings |
| `BACKUP_DIR` | Path to PostgreSQL backup files | Ops/infra — set to backup destination |
| `FRAUD_SIGNAL_*` | Fraud detection weights/thresholds | `config/security.php` defaults, tune per environment |

See `.env.example` (development defaults) and `.env.example.production` (production template with `CHANGE_ME` placeholders) for the full list with comments.

### .gitignore Rules

The following files must **never** be committed:

```
backend/.env
backend/.env.production
backend/.env.staging
frontend/.env
frontend/.env.local
frontend/.env.production
.env.production.compose
```

These entries are in `backend/.gitignore` (for backend files) and `.gitignore` (root, for compose/frontend files). The CI `security.yml` workflow verifies this on every push.

### Allowed .env Files in Git

The following **template** files ARE committed (they contain only placeholders):

- `backend/.env.example` — development template
- `backend/.env.example.production` — production template (all secrets = `CHANGE_ME` or empty)
- `backend/.env.example.staging` — staging template
- `.env.production.compose.example` — Docker Compose production template

**Rule:** if a file ends in `.example` or `.example.*` it may be committed. All others must be gitignored.

### Pre-commit Protection

The CI `env-files-not-committed` job (in `security.yml`) enforces this on every PR. For local protection, add to your git hooks:

```bash
# .git/hooks/pre-commit  (chmod +x)
#!/bin/bash
FORBIDDEN=(
  "backend/.env"
  "backend/.env.production"
  "backend/.env.staging"
  "frontend/.env"
  "frontend/.env.local"
  "frontend/.env.production"
  ".env.production.compose"
)

for file in "${FORBIDDEN[@]}"; do
  if git diff --cached --name-only | grep -qx "$file"; then
    echo "ERROR: Attempting to commit secret file: $file"
    exit 1
  fi
done
```

To install: copy the above to `.git/hooks/pre-commit` and run `chmod +x .git/hooks/pre-commit`.

---

## 5. Code Pattern Audit

### Shell Execution — `proc_open` in ScanKycDocumentJob

File: `backend/app/Jobs/ScanKycDocumentJob.php:80-118`

The job spawns `clamscan` via `proc_open` to scan KYC uploads for malware. Both the binary path and file path are passed through `escapeshellarg()` before being interpolated into the command string. The binary is sourced from application config (not user input). This is the correct pattern — **no action required**.

Review checklist for any future shell execution:
- [ ] All user-controlled values passed through `escapeshellarg()` or `escapeshellcmd()`
- [ ] Binary paths sourced from config/env, never from user input
- [ ] Output captured and not echoed back to users without sanitisation
- [ ] Timeout enforced (avoids hung processes)

### Raw SQL — `whereRaw` / `selectRaw`

Files: `backend/app/Services/ListingSearchService.php`, `backend/app/Services/Search/SqlSearchDriver.php`

All raw SQL expressions use `?` placeholder binding (the second argument to `whereRaw`/`selectRaw`). Column names used in expressions are hard-coded string literals built from a fixed list — never from user input. **No SQL injection risk.**

Review checklist for any future raw SQL:
- [ ] Values always passed as bindings (`?` or named), never string-interpolated
- [ ] Column/table names never derived from user input; if dynamic selection is needed, validate against a whitelist

### File Upload Handling

File uploads use Laravel's `Request::file()` and `UploadedFile::store()` / `storeAs()` methods throughout — never the PHP built-in `move_uploaded_file()`. **No action required.**

### `eval()`, `system()`, `exec()` Usage

A codebase-wide search found **no** usage of `eval()`, `system()`, or `exec()` in application code (`app/`). The only shell execution is the `proc_open` call in `ScanKycDocumentJob` (covered above).

---

## 6. Code Review Security Checklist (for PRs)

Copy this into the PR description when reviewing security-sensitive changes:

```
### Security Review Checklist

**Authentication & Authorisation**
- [ ] New routes have appropriate middleware (`auth:sanctum`, `role:admin`, `mfa`, etc.)
- [ ] Resource actions check ownership (no IDOR) — use policies or explicit `where('user_id', auth()->id())`
- [ ] Admin-only endpoints use `['role:admin', 'admin_mfa', 'mfa']` middleware stack

**Input & Output**
- [ ] User input is validated with Form Request or inline `$request->validate()`
- [ ] No raw SQL string interpolation — bindings only
- [ ] File uploads use Laravel's `UploadedFile` methods, not `move_uploaded_file()`
- [ ] Shell commands use `escapeshellarg()` / `escapeshellcmd()` on all user-derived values

**Secrets & Configuration**
- [ ] No secrets or real credentials in the PR diff
- [ ] New env vars added to `.env.example` and `.env.example.production` with placeholder values
- [ ] New env vars documented in DEPENDENCY-CODE-HYGIENE.md env inventory table

**Logging**
- [ ] No PII logged (email, phone, name, address, tokens) — use structured log with sanitised fields
- [ ] Security events (login failure, session revoke, etc.) emitted to `StructuredLogger`

**Dependencies**
- [ ] New package added? Run `composer audit` / `npm audit` locally and confirm no high/critical advisories
- [ ] Package version pinned appropriately (avoid `dev-master` or `*`)
```

---

## 7. Running a Full Security Scan Before a Release

```bash
# 1. Composer vulnerability audit
cd backend
composer audit --no-dev

# 2. npm vulnerability audit
cd ../frontend
npm audit --omit=dev

# 3. Gitleaks — scan entire repo history for leaked secrets
docker run -v "$(git rev-parse --show-toplevel):/repo" \
  ghcr.io/gitleaks/gitleaks:latest detect \
  --source=/repo --log-level=warn

# 4. Verify no real .env files are tracked
git ls-files | grep -E '^(backend/\.env|frontend/\.env|\.env\.production\.compose)$' \
  && echo "ERROR: secret file tracked" && exit 1 \
  || echo "No secret files tracked"

# 5. Run backend tests
cd ../backend
php artisan test

# 6. Run frontend tests + build
cd ../frontend
npm run test
npm run build
```

All six steps must complete without errors before tagging a release.
