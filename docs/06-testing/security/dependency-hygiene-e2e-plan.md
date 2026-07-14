# E2E Test Plan — Security Phase 7: Dependency & Code Hygiene

> Status: `active manual regression plan`
> Poslednja strukturna provera: 2026-07-15
> Source of truth: CI workflow-i, lock fajlovi i audit komande

Verification test plan for Phase 7. This phase adds no new UI screens — tests verify that **CI security gates work correctly**, that **secrets are protected**, and that **local audit tools produce the expected results**.

**Prerequisites:**
- Access to the GitHub repository (Actions tab)
- Local: `composer` available in `backend/`, `npm` available in `frontend/`
- Local: Docker or direct backend access for Artisan commands

---

## T-01 — Composer audit passes in CI on clean code

**Goal**: Verify that the `composer audit` step exists in backend CI and passes.

| # | Action | Expected result |
|---|---|---|
| 1 | On GitHub: open **Actions → Backend CI**, latest run on `main` | Workflow run visible |
| 2 | Open step **Security audit (Composer)** | Step was executed (not skipped) |
| 3 | Check step output | Either `"No security vulnerability advisories found"`, or a list of advisories with exit code ≠ 0 |

---

## T-02 — Composer audit blocks the pipeline on a critical vulnerability

**Goal**: Verify that the pipeline fails when a production dependency has an advisory.

**Note**: Tested locally — do not commit a broken `composer.json`.

| # | Action | Expected result |
|---|---|---|
| 1 | Locally, in `backend/`: run `composer audit --no-dev --format=plain` | If no advisories: exit 0, message "No security vulnerability advisories found" |
| 2 | (Simulation) Temporarily add a known vulnerable package (e.g. an older version), run `composer audit` again | Exit code ≠ 0, advisory printed with CVE number and affected version |
| 3 | Revert `composer.json` to the original | — |

---

## T-03 — npm audit passes in CI on clean code

**Goal**: Verify that the `npm audit` step exists in frontend CI and passes.

| # | Action | Expected result |
|---|---|---|
| 1 | On GitHub: open **Actions → Frontend CI**, latest run on `main` | Workflow run visible |
| 2 | Open step **Security audit (npm)** | Step was executed |
| 3 | Check step output | Either `"found 0 vulnerabilities"`, or a list of `high`/`critical` issues that block the pipeline |

---

## T-04 — npm audit locally

**Goal**: Verify the local frontend vulnerability scanning tool.

| # | Action | Expected result |
|---|---|---|
| 1 | In `frontend/`: run `npm audit --omit=dev` | Output: vulnerability count by severity (low/moderate/high/critical) |
| 2 | Run `npm audit --audit-level=high --omit=dev` | Exit 0 if no high/critical; exit ≠ 0 if any exist |
| 3 | (Optional) Run `npm audit --audit-level=high` (includes devDependencies) | May differ — devOnly vulnerabilities are not blocking for production |

---

## T-05 — Gitleaks workflow detects secrets in a PR

**Goal**: Verify that the Gitleaks workflow exists and scans every push/PR.

| # | Action | Expected result |
|---|---|---|
| 1 | On GitHub: open **Actions → Security Scanning**, latest run | Two jobs shown: `Gitleaks — secrets detection` and `Verify .env files are not tracked` |
| 2 | Open job `Gitleaks — secrets detection`, check the log | `"X commits scanned"`, `"leaks found: 0"` (or `"leaks found: N"` with details if any exist) |
| 3 | (Simulation — on a feature branch, NEVER on main) Commit a file with a fake secret like `password=abc123secret`, push the branch, open a PR | Gitleaks job fails, GitHub Actions shows `❌ leaks found` |
| 4 | Remove the file, force-push the branch | Gitleaks passes again |

---

## T-06 — Env guard blocks committed .env files

**Goal**: Verify that the CI job `env-files-not-committed` detects tracked .env files.

| # | Action | Expected result |
|---|---|---|
| 1 | On GitHub: open job `Verify .env files are not tracked` in the **Security Scanning** workflow | Step `Fail if any real .env file is tracked by git` was executed |
| 2 | Check step output | `"No real .env files are tracked. ✓"`, exit 0 |
| 3 | Locally: verify that env files are gitignored | `git check-ignore -v backend/.env backend/.env.production frontend/.env` — each file should appear in the `.gitignore` output |
| 4 | (Simulation) Run `git ls-files backend/.env` | Empty output — file is not tracked |

---

## T-07 — Dependabot configuration is active

**Goal**: Verify that the Dependabot configuration exists and GitHub recognises the ecosystems.

| # | Action | Expected result |
|---|---|---|
| 1 | On GitHub: open **Insights → Dependency graph → Dependabot** | Ecosystems shown: `composer` (`/backend`), `npm` (`/frontend`), `github-actions` (`/`) |
| 2 | Verify that the status is not `"Dependabot alerts disabled"` | Alerts enabled — Dependabot is tracking vulnerabilities |
| 3 | Locally: check the contents of `.github/dependabot.yml` | Three `package-ecosystem` sections: `composer`, `npm`, `github-actions`; schedule `weekly`, timezone `Europe/Belgrade` |
| 4 | (After the first Monday since merge) Check the **Pull requests** tab | Dependabot has opened PRs for available updates |

---

## T-08 — Pre-release security scan (local runbook)

**Goal**: Verify that the full local security scan completes without errors.

| # | Action | Expected result |
|---|---|---|
| 1 | `cd backend && composer audit --no-dev` | Exit 0, no advisories |
| 2 | `cd frontend && npm audit --omit=dev --audit-level=high` | Exit 0, no high/critical |
| 3 | Verify no tracked .env files: `git ls-files \| grep -E '^(backend/\.env\|frontend/\.env\|\.env\.production)'` | Empty output |
| 4 | `cd backend && php artisan test` | All tests pass |
| 5 | `cd frontend && npm run test && npm run build` | Tests and build pass |
