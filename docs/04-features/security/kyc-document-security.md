# KYC Document Security

> Status: `implemented`; AV zaštita zavisi od okruženja
> Poslednja strukturna provera: 2026-07-15
> Source of truth: KYC config/Requests/policy/jobs, storage i testovi

This document describes the security controls applied to KYC (Know Your Customer) document uploads, storage, access, and lifecycle.

---

## 1. File Validation

### 1.1 Extension and MIME allowlist (layer 1)

`StoreKycSubmissionRequest` applies Laravel's `mimes:` validator to every uploaded file. Laravel uses PHP's `finfo` extension internally to detect the actual file MIME type from its bytes, then maps that to an expected extension — rejecting mismatches before they ever reach the controller.

Allowed types: `jpg`, `jpeg`, `png`, `webp`, `pdf`
Max size: 10 MB per file (configurable via `KYC_MAX_FILE_SIZE_KB`, default `10240`)

### 1.2 Magic byte validation (layer 2)

A custom `after()` hook in `StoreKycSubmissionRequest` reads each uploaded file's actual bytes using `finfo_open(FILEINFO_MIME_TYPE)` (with fallback to `mime_content_type()`) and checks the detected MIME against a server-side allowlist:

```
image/jpeg
image/png
image/webp
application/pdf
```

This runs independently of the client-supplied `Content-Type` header. If the detected MIME is not in the allowlist, validation fails and the file is rejected before storage.

The allowlist is configurable in `config/kyc.php` under `allowed_magic_mimes`.

### 1.3 Stored MIME type

The MIME type written to `kyc_documents.mime_type` is the **server-detected** value from `finfo`, not the client-supplied header. This value is used when streaming files back to clients in the `Content-Type` header.

---

## 2. Storage

### 2.1 Disk and path

All KYC documents are stored on the **`private`** disk:

```
storage/app/private/kyc/{user_id}/{submission_id}/{doc_type}_{uuid}.{ext}
```

- The `private` disk root (`storage/app/private`) is **not symlinked** to `public/storage` and is not web-accessible.
- Files are only reachable through the authenticated download endpoint.
- The disk is configured in `config/filesystems.php`.

### 2.2 Encryption at rest

**Current approach:** filesystem-level; files are stored as plain bytes on disk. Server-level disk encryption (e.g. LUKS, AWS EBS encryption, filesystem-level AES) should be ensured at the infrastructure level.

**Application-level encryption:** not currently applied. If required, Laravel's encrypted stream (`Storage::disk('private')->putEncrypted(...)`) or a KMS-backed approach should be added. This is documented as a future hardening step.

> **Infrastructure requirement:** ensure the server volume hosting `storage/app/private` is encrypted at rest at the OS/cloud level.

---

## 3. Retention Policy

### 3.1 How retention works

When a submission reaches a terminal status (**approved** or **rejected**), a `purge_after` timestamp is automatically set on `kyc_submissions`:

```
purge_after = NOW() + KYC_DOCUMENT_RETENTION_DAYS
```

The default is **90 days**. Configure via the environment:

```dotenv
KYC_DOCUMENT_RETENTION_DAYS=90
```

**Withdrawn** submissions have their documents deleted immediately (no retention needed).
**Quarantined** submissions are handled separately by admins (see §6).

### 3.2 Purge command

```bash
php artisan kyc:purge-expired
```

- Finds all `kyc_submissions` where `purge_after <= NOW()` and documents still exist.
- Deletes the files from storage.
- Removes the `kyc_documents` DB rows.
- Submission records and audit logs are **retained** (no submission rows are deleted).
- Errors are logged; the command exits with `FAILURE` if any file could not be deleted.

**Dry-run mode** (no writes):
```bash
php artisan kyc:purge-expired --dry-run
```

### 3.3 Schedule

The purge command runs daily at **04:00 UTC** via the Laravel scheduler:

```php
$schedule->command('kyc:purge-expired')->dailyAt('04:00');
```

Ensure the scheduler is running: `php artisan schedule:run` (cron every minute) or a long-running `schedule:work` process.

---

## 4. Document Access

### 4.1 Endpoint

```
GET /api/kyc/documents/{document}
```

- Requires an authenticated Sanctum session.
- **Owners** (submitter) can download their own documents.
- **Admins** can download any document.
- All other requests receive `403 Forbidden`.
- File existence is verified server-side before streaming; returns `404` if missing.
- No static URL or presigned URL is exposed. Files are streamed byte-by-byte through the controller.

### 4.2 Response security headers

Every document response includes:

```
Content-Type: <server-detected MIME>
X-Content-Type-Options: nosniff
Cache-Control: private, no-store, max-age=0
Content-Disposition: inline|attachment; filename="<sanitized-name>"
```

Filenames are sanitized: `basename()` + quote-character stripping.

### 4.3 MFA requirement for admin access

The admin KYC document download endpoint is covered by the `mfa` middleware (`EnsureMfaVerified`), which requires an active `mfa_verified_at` session value for users with MFA enabled.

Admin-only management routes (approve, reject, redact, list) additionally require the `admin_mfa` middleware (`RequireMfaForAdmin`), which enforces that the admin account has MFA enabled and confirmed.

---

## 5. Audit Logging

Every document download is written to `audit_logs` via `AuditLogService`, regardless of whether the accessor is the owner or an admin.

| Accessor | Action recorded |
|----------|----------------|
| Owner | `kyc.document.owner_downloaded` |
| Admin | `kyc.document.admin_downloaded` |

Each entry includes:
- `actor_user_id` — who accessed the file
- `subject_type` = `App\Models\KycDocument`
- `subject_id` — document ID
- `metadata.submission_id`, `metadata.owner_id`, `metadata.doc_type`, `metadata.is_admin`
- `ip_address`, `user_agent`
- `created_at` timestamp

---

## 6. Malware (AV) Scanning

### 6.1 Scanner integration

ClamAV scanning is dispatched as a queued job (`ScanKycDocumentJob`) after each document is stored. The job is gated by the environment variable:

```dotenv
ENABLE_AV_SCAN=false   # set to true to enable
```

When disabled, documents remain with `av_status = pending` and no scan is performed. This is safe for development environments but **must be enabled in production** once ClamAV is available.

### 6.2 ClamAV setup

Install ClamAV on the application server:

```bash
# Debian/Ubuntu
apt-get install clamav clamav-daemon
freshclam       # update virus definitions
```

Then set in `.env`:

```dotenv
ENABLE_AV_SCAN=true
CLAMSCAN_BINARY=/usr/bin/clamscan   # adjust to actual path
CLAMSCAN_TIMEOUT=60                 # seconds per scan
```

Ensure the queue worker has access to the `storage/app/private` directory.

### 6.3 Scan results

| `av_status` | Meaning |
|------------|---------|
| `pending` | Not yet scanned (scan queued or disabled) |
| `clean` | Scan passed — no threat found |
| `infected` | Threat detected — file quarantined |
| `error` | Scan could not complete (binary missing, timeout, etc.) |

Documents with `av_status = error` should be monitored. The job retries up to 3 times with 30-second backoff. Persistent errors indicate a scanner configuration problem.

### 6.4 Quarantine procedure

When a document is flagged as infected:

1. The file is **moved** (not deleted) to `storage/app/private/kyc_quarantine/{document_id}_{filename}`.
2. The `kyc_documents.path` column is updated to the quarantine path.
3. The `kyc_documents.av_status` is set to `infected`.
4. The parent `kyc_submissions.status` is set to `quarantined`.
5. A warning is logged: `kyc.av_scan.infected` with `document_id`, `submission_id`, and `threat` name.

**Admin procedure for quarantined submissions:**

1. Navigate to the admin KYC queue — filter by status `quarantined`.
2. Review the submission metadata and the scanner threat name in `reviewer_note`.
3. Do **not** attempt to download the flagged file through the normal endpoint.
4. Delete the quarantine file from the server directly after forensic review, if needed.
5. Redact the submission via `DELETE /api/admin/kyc/submissions/{id}/redact` to clear it from the queue.

Quarantined files are **not** eligible for the automatic retention purge. They must be manually reviewed and removed.

---

## 7. Environment Variables Reference

| Variable | Default | Description |
|----------|---------|-------------|
| `KYC_MAX_FILE_SIZE_KB` | `10240` | Maximum file size per document in KB (10 MB) |
| `KYC_DOCUMENT_RETENTION_DAYS` | `90` | Days to retain documents after terminal status |
| `ENABLE_AV_SCAN` | `false` | Enable ClamAV malware scanning |
| `CLAMSCAN_BINARY` | `clamscan` | Path to clamscan executable |
| `CLAMSCAN_TIMEOUT` | `60` | Per-scan timeout in seconds |

---

## 8. Known Gaps and Future Hardening

| Gap | Recommendation |
|-----|---------------|
| No application-level encryption at rest | Implement Laravel encrypted streams or KMS integration |
| ClamAV disabled by default | Enable `ENABLE_AV_SCAN=true` and install ClamAV in production before going live |
| No S3/remote disk support in AV scanner | `ScanKycDocumentJob::resolveAbsolutePath()` returns `null` for non-local disks — add temp download support if switching to S3 |
| `purge_after` not set on `quarantined` submissions | Admins must manually redact quarantined submissions to trigger document deletion |
| Owner MFA not enforced at endpoint level | Consider adding `mfa` middleware to the `kyc/documents/{document}` route for users with MFA enabled (already enforced for admins) |
