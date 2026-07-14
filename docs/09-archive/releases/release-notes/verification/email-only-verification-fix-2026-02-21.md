# Verification Release Notes - 2026-02-21 (Email-only)

This document covers verification-domain changes delivered in the "Verification-Fix" phase, including a production SMTP configuration incident and remediation.

## 1) Goal and scope

### Goals
- Keep email verification as active verification channel.
- Temporarily remove phone verification from backend and frontend flows.
- Keep seeker/landlord KYC flow unchanged.
- Remove production confusion around mail configuration.

### Out of scope
- Phone verification providers (SMS/WhatsApp and similar).
- DB schema semantic changes for `phone_verified` (column remains for compatibility).

## 2) Backend changes

### 2.1 Verification endpoints
Removed phone endpoints:
- `POST /api/v1/me/verification/phone/request`
- `POST /api/v1/me/verification/phone/confirm`

Kept active email endpoints:
- `POST /api/v1/me/verification/email/request`
- `POST /api/v1/me/verification/email/confirm`

File:
- `backend/routes/api.php`

### 2.2 UserVerificationController refactor
- Controller now supports email channel only.
- Verification code remains hashed in `user_verification_codes`.
- If delivery fails, verification code row is removed and API returns `503`.
- `devCode` remains available only for `local/testing`.

File:
- `backend/app/Http/Controllers/UserVerificationController.php`

### 2.3 Mail delivery layer
Added:
- `EmailVerificationCodeSender` service (Mail facade delivery)
- `VerificationCodeMail` mailable
- `emails/verification-code` view
- `VerificationDeliveryException`
- `config/verification.php` with `VERIFICATION_CODE_TTL_MINUTES`

Files:
- `backend/app/Services/Verification/EmailVerificationCodeSender.php`
- `backend/app/Mail/VerificationCodeMail.php`
- `backend/resources/views/emails/verification-code.blade.php`
- `backend/app/Services/Verification/Exceptions/VerificationDeliveryException.php`
- `backend/config/verification.php`

### 2.4 Phone verification domain removal
- Removed `CHANNEL_PHONE` constant from verification code model.
- Removed phone verification payload from API resources.
- Public profile verifications now expose only `email` and `address`.

Files:
- `backend/app/Models/UserVerificationCode.php`
- `backend/app/Http/Resources/UserResource.php`
- `backend/app/Http/Resources/PublicUserResource.php`

### 2.5 Rating requirements
- Rating no longer requires phone verification.
- New requirement: `email_verified && address_verified`.
- Validation message updated to: "Verify your email and address to rate".

Files:
- `backend/app/Services/RatingService.php`
- `backend/app/Services/ListingRatingService.php`

### 2.6 Profile update behavior
- Changing phone number no longer resets `phone_verified` because phone verification flow is disabled.

File:
- `backend/app/Http/Controllers/UserAccountController.php`

## 3) Frontend changes

### 3.1 Verification UI
- Removed phone verification block from `/profile/verification`.
- Page now handles email verification only.

File:
- `frontend/src/pages/KycVerification.vue`

### 3.2 API service layer
Removed implementations and exports:
- `requestPhoneVerification`
- `confirmPhoneVerification`

Files:
- `frontend/src/services/index.ts`
- `frontend/src/services/realApi.ts`
- `frontend/src/services/mockApi.ts`

### 3.3 Profile and type updates
- Public profile no longer renders phone verification badge.
- `PublicProfile.verifications` no longer includes `phone`.
- Auth store no longer tracks `phoneVerified`.

Files:
- `frontend/src/pages/PublicProfile.vue`
- `frontend/src/types/index.ts`
- `frontend/src/stores/auth.ts`

### 3.4 i18n cleanup
- Removed phone verification translation keys.
- `verification.codeHint` is now email-only.

File:
- `frontend/src/stores/language.ts`

## 4) Tests and QA updates
Backend test updates:
- `VerificationApiTest` updated to email-only flow.
- `UserAccountApiTest` adapted to new phone update behavior.
- `RatingsApiTest` fixtures no longer depend on `phone_verified=true`.

Files:
- `backend/tests/Feature/VerificationApiTest.php`
- `backend/tests/Feature/UserAccountApiTest.php`
- `backend/tests/Feature/RatingsApiTest.php`

Documentation update:
- Removed phone verification scenario from test plan.

File:
- `docs/test-plan-sr.md`

## 5) Production incident: mail config not loaded

### 5.1 Symptom
Email verification endpoint returned:
- `503 Email delivery is not configured.`

Runtime check showed:
- `config('mail.default') === 'log'`
- `smtp.host === 127.0.0.1`
- `smtp.port === 2525`

### 5.2 Root cause
In production compose, `APP_ENV=production` caused Laravel to load:
- `backend/.env.production` (if present)

instead of `backend/.env`.

`backend/.env.production` still had old mail settings (`MAIL_MAILER=log`), while `backend/.env` had correct SMTP values.

### 5.3 Resolution
1. Align `backend/.env.production` with real SMTP values.
2. Recreate backend processes:
  - backend
  - queue
  - scheduler
  - reverb
3. Clear runtime cache:
  - `php artisan optimize:clear`
4. Verify:
  - `config('mail.default')` must be `smtp`.

## 6) Mail operations checklist
Required keys for email verification:
- `MAIL_MAILER=smtp`
- `MAIL_SCHEME=smtps` (for port 465)
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`

Quick runtime check from backend container:
```bash
php artisan tinker --execute="dump(config('mail.default')); dump(config('mail.mailers.smtp.host')); dump(config('mail.mailers.smtp.port'));"
```

If value is not `smtp`:
```bash
php artisan optimize:clear
```
then recreate backend stack services.

## 7) Future development note
If phone verification is reintroduced:
- restore API routes and service functions
- restore UI elements and i18n keys
- gate behavior behind a dedicated feature flag (for example `VERIFICATION_PHONE_ENABLED`) so enable/disable does not require broad cleanup
