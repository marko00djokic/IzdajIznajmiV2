# Data Protection & Privacy — Security Reference

> Status: `implemented reference`; pravnu usklađenost mora potvrditi stručnjak
> Poslednja strukturna provera: 2026-07-15
> Source of truth: migracije, Resources, PiiSanitizer, retention/account kod i testovi

> Implemented in: `Security-Phase-5-Data-Protection-&-Privacy`
> Last updated: 2026-03-02

---

## Table of Contents

1. [PII Fields Inventory](#1-pii-fields-inventory)
2. [Log Sanitization](#2-log-sanitization)
3. [API Response PII Rules](#3-api-response-pii-rules)
4. [GDPR — Account Deletion](#4-gdpr--account-deletion)
5. [Data Retention Policy](#5-data-retention-policy)
6. [Support Runbook — Manual GDPR Deletion](#6-support-runbook--manual-gdpr-deletion)

---

## 1. PII Fields Inventory

### `users` table

| Field | Sensitivity | Storage Method | Notes |
|---|---|---|---|
| `email` | High | Plaintext + DB unique index | Used for login and delivery; masked by PiiSanitizer in logs |
| `email_verified_at` | Low | Plaintext | Timestamp only |
| `password` | Critical | Bcrypt hash (`password` cast) | Never logged or serialized |
| `full_name` | Medium | Plaintext | Semi-public (shown to counterparts in transactions) |
| `phone` | High | **AES-256-CBC encrypted** (`encrypted` cast) | Plaintext never stored; uniqueness enforced via `phone_hash` |
| `phone_hash` | Internal | HMAC-SHA256 of `mb_strtolower(trim(phone))` keyed by `APP_KEY` | DB-level unique index; hidden from all serialization |
| `residential_address` | High | **AES-256-CBC encrypted** (`encrypted` cast) | — |
| `address_book` | High | **AES-256-CBC encrypted** (`encrypted:array` cast) | JSON array, decrypted transparently by Eloquent |
| `date_of_birth` | High | Plaintext (date column) | Used for age verification |
| `gender` | Medium | Plaintext (enum) | — |
| `employment_status` | Medium | Plaintext (enum) | — |
| `avatar_path` | Low | Plaintext (storage path on public disk) | File itself is publicly accessible via URL |
| `mfa_totp_secret` | Critical | Plaintext | Hidden from serialization via `$hidden`; never logged |
| `verification_notes` | Medium | Plaintext | Admin-entered free text |
| `badge_override_json` | Low | JSON | No PII |

**Encryption implementation:** Laravel's built-in `encrypted` and `encrypted:array` casts use AES-256-CBC with the application's `APP_KEY`. Encryption/decryption is transparent at the Eloquent model layer. Raw DB queries cannot read or filter encrypted values.

**Phone uniqueness:** Because AES-256-CBC produces non-deterministic ciphertext, the DB `UNIQUE` index was moved from `phone` to `phone_hash`. The hash is computed via `User::hashPhone()`:

```php
hash_hmac('sha256', mb_strtolower(trim($phone)), config('app.key'))
```

### `kyc_documents` table

| Field | Sensitivity | Storage Method |
|---|---|---|
| `original_name` | Low | Plaintext (filename) |
| `path` | Medium | Plaintext (private disk path) |
| `mime_type` | Low | Plaintext |
| `size_bytes` | Low | Plaintext |

KYC document **files** are stored on the private disk under `kyc/{user_id}/{submission_id}/`. Access is authenticated and audited. See [KYC document security](kyc-document-security.md) for full details.

### `mfa_recovery_codes` table

| Field | Sensitivity | Storage Method |
|---|---|---|
| `code` | Critical | SHA-256 hashed (one-way) |

---

## 2. Log Sanitization

### PiiSanitizer Monolog Processor

`App\Logging\PiiSanitizer` is a Monolog `ProcessorInterface` applied to **all log channels** (single, daily, slack, papertrail, stderr, syslog, errorlog, structured).

**Masked fields** (value replaced with `***`, email addresses show `us***@domain.com`):

| Key | Reason |
|---|---|
| `email` | Direct PII |
| `phone` | Direct PII |
| `full_name` | Direct PII |
| `residential_address` | Direct PII |
| `address` | Direct PII |
| `document_id` | KYC reference |
| `password` | Credential |
| `token` | Credential |
| `secret` | Credential |
| `recovery_code` | MFA credential |

**Stripped fields** (key + value removed entirely):

| Key | Reason |
|---|---|
| `address_book` | Array of addresses |
| `ssn` | Government ID |
| `tax_id` | Government ID |

The processor recurses up to 5 levels into nested arrays so context passed as nested objects is also sanitized.

### Known PII in Logs (pre-Phase-5)

| Location | Field | Fix Applied |
|---|---|---|
| `EmailVerificationCodeSender::send()` | `email` logged on send failure | Removed from log context (user_id is sufficient) |

### Sentry / Error Tracker

Sentry integration (`App\Services\SentryReporter`) should have `scrubFields` set in `config/sentry.php` (or equivalent SDK config):

```php
'scrub_fields' => ['email', 'phone', 'full_name', 'residential_address', 'password', 'token', 'secret'],
```

The `PiiSanitizer` processor also prevents PII from appearing in the structured log channel that feeds error context, providing defence-in-depth.

---

## 3. API Response PII Rules

### `PublicUserResource` — unauthenticated / other-user context

**Exposed:** `id`, `role`, `fullName`, `avatarUrl`, `joinedAt`, `badges`, `verifications` (boolean flags), `ratingStats`, `recentRatings`.

**Not exposed:** `email`, `phone`, `residential_address`, `address_book`, `date_of_birth`, `gender`, `employment_status`, `is_suspicious`, `mfa_enabled`, `verification_notes`.

### `UserResource` — authenticated user (own data only)

**Exposed:** All profile fields including `email`, `phone`, `residentialAddress`, `addressBook`, `isSuspicious`, `mfaEnabled`, `verificationNotes`.

This resource is only returned to the authenticated user themselves (`GET /api/v1/auth/me`, profile updates). It is never used to expose another user's data.

### `ConversationResource`

Exposes participant IDs (`tenantId`, `landlordId`) and a display name derived from `user->name` only. No email, phone, or address data.

### `MessageResource`

Exposes message body and `senderId` (integer). No PII beyond message content written by the user.

### `KycSubmissionResource` — admin context only

Exposes `user.email` when the `user` relation is loaded. This resource is only returned via admin-only routes (`GET /admin/kyc/submissions`, `GET /admin/kyc/submissions/{id}`) which are protected by `['role:admin', 'admin_mfa', 'mfa']` middleware.

### `AdminReportResource`

Exposes reporter name (full_name or name). Used only in admin moderation routes.

---

## 4. GDPR — Account Deletion

### Endpoint

```
DELETE /api/v1/me
```

**Authentication:** Required (`auth:sanctum` + `session_activity`)
**Rate limit:** 3 requests per day per user/IP (`account_deletion` limiter)
**Body:**
```json
{ "password": "current-password" }
```

**Response:** `204 No Content`

### What happens on deletion

The account is **anonymized in-place** (the `users` row is kept to preserve referential integrity for transactions, ratings, contracts, and audit trails).

| Action | Details |
|---|---|
| Avatar deleted | `Storage::disk('public')->delete($user->avatar_path)` |
| Chat attachments deleted | Files (`path_original`, `path_thumb`) removed from private disk for all attachments where `uploader_id = user.id` |
| Sessions revoked | All entries in `sessions` and `user_sessions` tables deleted via `SecuritySessionService::revokeAllSessions()` |
| Push subscriptions deleted | All `push_subscriptions` rows for the user deleted |
| PII fields nulled / anonymized | See table below |
| MFA recovery codes deleted | All `mfa_recovery_codes` rows deleted |
| Audit log created | Action: `user.account.deleted`, subject: `User:{id}`, includes requesting IP |

**Anonymized fields on the `users` row:**

| Field | Value after deletion |
|---|---|
| `name` | `'Deleted User'` |
| `full_name` | `null` |
| `email` | `deleted_{id}@deleted.local` |
| `phone` | `null` |
| `phone_hash` | `null` |
| `date_of_birth` | `null` |
| `gender` | `null` |
| `residential_address` | `null` |
| `employment_status` | `null` |
| `address_book` | `null` |
| `avatar_path` | `null` |
| `verification_notes` | `null` |
| `badge_override_json` | `null` |
| `mfa_totp_secret` | `null` |
| `password` | Random bcrypt hash (login impossible) |
| `mfa_enabled` | `false` |

**Not deleted (referential integrity):**

- `users` row itself
- `ratings`, `listing_ratings` — rating content authored by or received by the user
- `transactions`, `contracts`, `payments` — financial records
- `audit_logs` — legal/compliance record
- `kyc_submissions` and `kyc_documents` — KYC records (separately governed, see KYC docs)

### Audit trail

Every deletion is recorded in `audit_logs`:
```json
{
  "action": "user.account.deleted",
  "subject_type": "App\\Models\\User",
  "subject_id": <user_id>,
  "actor_user_id": null,
  "metadata": { "actor_user_id": <user_id>, "ip": "<requesting IP>" }
}
```

---

## 5. Data Retention Policy

All purge commands support `--dry-run` to preview eligible records without deleting.

| Data Type | Retention Period | Env Var | Artisan Command | Schedule |
|---|---|---|---|---|
| Chat attachments (files + DB records) | 365 days | `DATA_RETENTION_CHAT_ATTACHMENTS_DAYS` | `attachments:purge-old` | Daily 05:00 |
| Audit logs (DB records) | 730 days (2 years) | `DATA_RETENTION_AUDIT_LOGS_DAYS` | `audit-logs:purge-old` | Daily 05:15 |
| Notifications (DB records) | 90 days | `DATA_RETENTION_NOTIFICATIONS_DAYS` | `notifications:purge-old` | Daily 05:30 |
| KYC documents (files only) | Per-submission `purge_after` | `KYC_DOCUMENT_RETENTION_DAYS` | `kyc:purge-expired` | Daily 04:00 |
| Trusted devices | `TRUSTED_DEVICE_TTL_DAYS` (default 30) | — | `trusted-devices:purge` | Daily 04:30 |

All retention periods are configured in `config/data_retention.php`. Defaults are conservative (legal minimum recommended values for Serbian/EU data protection law context).

---

## 6. Support Runbook — Manual GDPR Deletion

Use this procedure when a user submits a GDPR erasure request through a support channel and cannot use the self-service endpoint (e.g., account is locked).

### Step 1: Verify identity

Confirm the requester's identity via email (require a reply from the email address on their account). Log the request receipt timestamp.

### Step 2: Locate the user

```bash
php artisan tinker --no-interaction
# Inside tinker:
$user = App\Models\User::where('email', 'user@example.com')->first();
echo $user->id;
```

### Step 3: Run the deletion

```php
# Still inside tinker — or write a one-off script:
use App\Services\SecuritySessionService;
use App\Services\AuditLogService;
use App\Models\ChatAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

$user = App\Models\User::find(<ID>);

// 1. Delete avatar
if ($user->avatar_path) {
    Storage::disk('public')->delete($user->avatar_path);
}

// 2. Delete chat attachments
ChatAttachment::where('uploader_id', $user->id)->chunkById(100, function ($attachments) {
    foreach ($attachments as $a) {
        $disk = $a->disk ?? 'private';
        if ($a->path_original) Storage::disk($disk)->delete($a->path_original);
        if ($a->path_thumb)    Storage::disk($disk)->delete($a->path_thumb);
    }
    ChatAttachment::whereIn('id', $attachments->pluck('id'))->delete();
});

// 3. Revoke sessions
app(SecuritySessionService::class)->revokeAllSessions($user);

// 4. Delete push subscriptions
$user->pushSubscriptions()->delete();

// 5. Anonymize user row
$user->fill([
    'name'                => 'Deleted User',
    'full_name'           => null,
    'email'               => 'deleted_' . $user->id . '@deleted.local',
    'phone'               => null,
    'phone_hash'          => null,
    'date_of_birth'       => null,
    'gender'              => null,
    'residential_address' => null,
    'employment_status'   => null,
    'address_book'        => null,
    'avatar_path'         => null,
    'verification_notes'  => null,
    'badge_override_json' => null,
    'mfa_totp_secret'     => null,
    'password'            => Hash::make(bin2hex(random_bytes(32))),
]);
$user->mfa_enabled = false;
$user->save();

// 6. Remove MFA recovery codes
$user->mfaRecoveryCodes()->delete();

// 7. Audit log
app(AuditLogService::class)->record(null, 'user.account.deleted', App\Models\User::class, $user->id, [
    'actor_user_id' => null,
    'source'        => 'support_manual_gdpr',
    'ip'            => 'support-tool',
]);
```

### Step 4: Confirm and respond

After running the script:
1. Verify `$user->fresh()->email === 'deleted_<id>@deleted.local'`
2. Confirm no avatar file exists at the original path
3. Send the erasure confirmation to the requester within the required regulatory timeframe (72 hours under GDPR Article 12)
4. Record the ticket/case ID in the audit log metadata if possible

### KYC documents

KYC documents are managed separately. To redact documents for a deleted user:

```
POST /api/v1/admin/kyc/submissions/{submission_id}/redact
```

(Admin auth required.) This removes files from storage and marks the submission `withdrawn`.
