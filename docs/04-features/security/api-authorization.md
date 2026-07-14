# API Authorization & IDOR Prevention

> Status: `implemented`
> Poslednja strukturna provera: 2026-07-15
> Source of truth: rute, middleware, policies i security feature testovi

Security Phase 4 — applied to branch `Security-Phase-4-API-Authorization-IDOR-Prevention`.

---

## Authorization model

All protected resources follow a two-layer approach:

| Layer | Mechanism | Location |
|---|---|---|
| Route-level | `auth:sanctum` middleware | `routes/api.php` |
| Resource-level | Laravel Gate / Policy class | `app/Policies/` |

Inline `abort_unless()` checks have been replaced by named Policy classes registered
in `AppServiceProvider`. This makes authorization auditable and testable in isolation.

---

## Policy classes

### `ChatAttachmentPolicy`

File: [app/Policies/ChatAttachmentPolicy.php](../../../backend/app/Policies/ChatAttachmentPolicy.php)

| Ability | Rule |
|---|---|
| `view` | User must be a participant of the conversation that owns the attachment. Covers both the original file and the thumbnail. |

Enforced in [ChatAttachmentController](../../../backend/app/Http/Controllers/ChatAttachmentController.php)
via `$this->authorize('view', $attachment)` in both `show()` and `thumb()`.

### `KycDocumentPolicy`

File: [app/Policies/KycDocumentPolicy.php](../../../backend/app/Policies/KycDocumentPolicy.php)

| Ability | Rule |
|---|---|
| `view` | User is the document owner (`user_id` match) **or** has the `admin` role. |

Enforced in [KycDocumentController](../../../backend/app/Http/Controllers/KycDocumentController.php)
via `$user->can('view', $document)`.

> The controller also records an audit log entry after every successful access, using
> action `kyc.document.owner_downloaded` or `kyc.document.admin_downloaded`.

---

## Private file serving

All user-uploaded files (chat attachments, KYC documents) are stored on the **`private`
disk** (`storage/app/private/`) which is never exposed via the web server's document root.

Files are served exclusively through authenticated controller endpoints that perform
authorization before streaming the file:

```
GET /api/v1/chat/attachments/{attachment}       → ChatAttachmentController@show
GET /api/v1/chat/attachments/{attachment}/thumb → ChatAttachmentController@thumb
GET /api/v1/kyc/documents/{document}            → KycDocumentController@show
```

### Nginx hardening

`ops/nginx-site.conf` contains an explicit deny rule that prevents any attempt to
access private storage paths directly, even if Nginx's document root were misconfigured:

```nginx
# Block direct access to private storage — files must be served through PHP.
location ~* ^/storage/app/ {
    deny all;
    return 404;
}
```

The public symlink (`storage/app/public/ → public/storage/`) is still served normally
for genuinely public assets (e.g. property photos).

---

## Filename sanitization

Both controllers sanitize the `Content-Disposition` filename before sending it to the
client to prevent header injection:

```php
private function sanitizeFilename(?string $name): string
{
    $name = $name ?: 'attachment';
    $name = basename($name);                                  // strip directory traversal
    $name = Str::of($name)->replace(['"', "'"], '')->toString(); // strip quote chars
    return $name ?: 'attachment';
}
```

---

## IDOR coverage by resource

| Resource | IDOR vector | Protection |
|---|---|---|
| `ChatAttachment` | Fetch another conversation's file by ID | `ChatAttachmentPolicy::view` — participant check |
| `KycDocument` | Fetch another user's KYC document by ID | `KycDocumentPolicy::view` — owner or admin |
| `Conversation` | Read/send messages in another user's chat | Inline participant check in `ConversationController` |
| KYC submission approval | Approve/reject without admin role | `RoleMiddleware:admin` + `RequireMfaForAdmin` |

---

## devCode environment gating

`UserVerificationController` may return a `devCode` field in its email verification
response to assist local development. This field is **only** present when
`app()->environment(['local', 'testing'])` is true.

The absence of `devCode` in production is covered by:

```
Tests\Feature\VerificationApiTest::test_devcode_is_absent_outside_local_and_testing
```

---

## Admin MFA requirement

Admin routes (`/api/v1/admin/…`) are protected by a chain of middleware:

```
auth:sanctum → SessionActivity → EnsureMfaVerified → RoleMiddleware:admin → RequireMfaForAdmin
```

| Middleware | Effect |
|---|---|
| `EnsureMfaVerified` | If the user has MFA enabled, the session must contain `mfa_verified_at`. |
| `RoleMiddleware:admin` | User must carry the Spatie `admin` role. |
| `RequireMfaForAdmin` | When `REQUIRE_MFA_FOR_ADMINS=true`, admins must have `mfa_enabled` and `mfa_confirmed_at` set. |

### Writing tests for admin endpoints

A test admin user must satisfy **all three** layers:

```php
$admin = User::factory()->create([
    'role'           => 'admin',
    'mfa_enabled'    => true,
    'mfa_confirmed_at' => now(),
]);
$admin->assignRole('admin'); // Spatie role — required by RoleMiddleware

$this->withSession(['mfa_verified_at' => now()->toIso8601String()]) // EnsureMfaVerified
    ->actingAs($admin)
    ->patchJson('/api/v1/admin/...')
    ->assertOk();
```

> **Session isolation**: Do not make requests as a different user within the same test
> method before an admin request. `SecuritySessionService::touchSession()` creates a
> session cookie on every response, which can leak the previous user into the next
> request's session-based auth resolution. Use separate test methods for the
> "forbidden" and "allowed" cases.

---

## Feature test coverage

| Test class | Method | What it covers |
|---|---|---|
| `KycApiTest` | `test_document_download_forbidden_to_non_owner_or_admin` | KYC document IDOR — third-party user gets 403 |
| `KycApiTest` | `test_admin_document_access_is_audited` | Admin access is audit-logged |
| `KycApiTest` | `test_non_admin_cannot_approve_kyc_submission` | Non-admin gets 403 on approve endpoint |
| `KycApiTest` | `test_admin_can_approve_kyc_submission` | Admin with MFA session can approve |
| `VerificationApiTest` | `test_devcode_is_absent_outside_local_and_testing` | devCode not leaked in production env |

---

## Checklist for new resources

When adding a new model that users can fetch by ID through an API endpoint:

- [ ] Create a Policy class in `app/Policies/` with a `view()` (and `update()`, `delete()` as needed) method.
- [ ] Register the policy in `AppServiceProvider::boot()` with `Gate::policy(Model::class, Policy::class)`.
- [ ] Replace any inline `abort_unless` ownership checks in the controller with `$this->authorize('ability', $model)`.
- [ ] Store files on the `private` disk; serve through a controller, never via a public URL.
- [ ] Add a `sanitizeFilename()` call before setting `Content-Disposition`.
- [ ] Write a feature test asserting that a user who does **not** own the resource receives 403.
- [ ] Add a Nginx `deny all` rule if the storage path could be guessable.
