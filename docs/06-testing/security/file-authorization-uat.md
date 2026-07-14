# UAT Scenarios — Phase 4: File Authorization & IDOR Prevention

> Status: `active manual security regression`
> Poslednja ciljana provera setupa: 2026-07-15
> Source of truth: KYC/chat policy kod i automatizovani testovi

This document is intended for testers and product owners.
No technical knowledge required — everything is done through the UI or the browser address bar.

---

## Setup

- Open the application: `http://localhost:5173`
- Use **two different browsers** (e.g. Chrome and Firefox) or two Chrome profiles to simulate two users simultaneously
- Reset the database before testing: ask a developer to run `php artisan migrate:fresh --seed`

### Test users (password: `password`)

| Who | Email | Role |
|---|---|---|
| Admin | `admin@gmail.com` | administrator |
| Lana | `stanodavac1@gmail.com` | landlord; tester prvo uploaduje KYC dokumente |
| Tomas | `trazilac2@gmail.com` | seeker bez pristupa Laninim dokumentima |
| Tena | `trazilac1@gmail.com` | seeker sa seedovanim razgovorom sa prvim landlordom |

Clean seed ne kreira KYC dokumente. Pre scenarija 1 prijavi se kao Lana i
pošalji testne KYC fajlove kroz verification ekran; ne koristi stvarne lične
dokumente.

---

## Scenario 1 — No one can open another user's KYC document

**Goal:** Tomas must not be able to download Lana's identity documents even if he knows the link.

### Steps

**Browser A — Log in as Lana:**

1. Open `http://localhost:5173` and log in as `stanodavac1@gmail.com`
2. Go to the profile verification page (Profile → Identity Verification)
3. Use the test submission created during setup — ask a developer for the numeric ID of its first document (from the `kyc_documents` table) or find it in the URL while browsing the page
4. Open in the browser: `http://localhost:8000/api/v1/kyc/documents/{ID}`

   **Expected:** The document is downloaded or displayed in the browser ✅

**Browser B — Log in as Tomas:**

5. Log in as `trazilac2@gmail.com`
6. In Browser B, type the same URL with Lana's document ID: `http://localhost:8000/api/v1/kyc/documents/{ID}`

   **Expected:** An error `403 Forbidden` is shown — the file is not displayed ✅

   **Fail if:** Tomas can see Lana's document ❌

---

## Scenario 2 — Guest (not logged in) cannot open a KYC document

**Goal:** Any attempt to access a document without being logged in must be rejected.

### Steps

1. Open an incognito/private window (Ctrl+Shift+N in Chrome)
2. Type the direct URL: `http://localhost:8000/api/v1/kyc/documents/1`

   **Expected:** Error `401 Unauthenticated` or redirect to the login page ✅

   **Fail if:** The file is displayed ❌

---

## Scenario 3 — Direct URL to the storage folder is blocked

**Goal:** Even if someone guesses the exact file path on the server, Nginx must block it.

### Steps

1. In any browser (logged in or not), type:
   `http://localhost:8000/storage/app/private/kyc/1/1/id_front.jpg`
2. Also try: `http://localhost:8000/storage/app/kyc/1/1/selfie.jpg`

   **Expected:** `404 Not Found` — an error page, no image ✅

   **Fail if:** An image or PDF is displayed ❌

3. Verify that public images still work:
   `http://localhost:8000/storage/listings/some-image.jpg`

   **Expected:** Image loads normally ✅

---

## Scenario 4 — No one can open a file from someone else's chat conversation

**Goal:** Tomas must not be able to download a file that Tena sent to Lana in their private chat.

### Steps

**Browser A — Lana sends a file to Tena:**

1. Log in as `stanodavac1@gmail.com`
2. Open the chat with Tena
3. Send a message with an attachment (image or PDF)
4. Once the message is sent, right-click the attachment → "Copy link address", or find the ID in Developer Tools (Network tab)
   — you are looking for a URL in the form `/api/v1/chat/attachments/{ID}`

**Browser B — Tomas tries the same link:**

5. Log in as `trazilac2@gmail.com`
6. Type the attachment URL you copied: `http://localhost:8000/api/v1/chat/attachments/{ID}`

   **Expected:** Error `403 Forbidden` ✅

   **Fail if:** The file is downloaded ❌

7. Also try the thumbnail URL: `http://localhost:8000/api/v1/chat/attachments/{ID}/thumb`

   **Expected:** Same `403` error ✅

---

## Scenario 5 — A conversation participant can see the attachment

**Continuing from Scenario 4, same file:**

1. Stay logged in as `trazilac1@gmail.com` (the browser that is not Tomas)
2. Open the chat with Lana
3. Find the message with the attachment and click to download it

   **Expected:** File downloads normally ✅

---

## Scenario 6 — A landlord cannot approve a KYC submission

**Goal:** Only admins have access to the admin KYC panel.

### Steps

1. Log in as `stanodavac1@gmail.com`
2. In the browser, open Developer Tools (F12) → Console tab and run:
   ```js
   fetch('/api/v1/admin/kyc/submissions/1/approve', {
     method: 'PATCH',
     headers: { 'Accept': 'application/json' }
   }).then(r => r.json()).then(console.log)
   ```

   **Expected:** `403 Forbidden` ✅

   **Fail if:** The submission status changes to `approved` ❌

---

## Scenario 7 — Admin approves a KYC submission; user's profile updates

**Goal:** An admin with confirmed MFA can approve a submission and this is immediately reflected on the user's profile.

### Steps

**Admin login:**

1. Log in as `admin@gmail.com`
2. The system will prompt for an MFA code — enter the code from your authenticator app
3. After successful login, go to Admin panel → KYC Submissions (or directly: `http://localhost:5173/admin/kyc`)

**Approval:**

4. Find Lana's submission with status `pending`
5. Click "Approve"

   **Expected:** Status changes to `approved`, a success message is shown ✅

**Verify on Lana's profile:**

6. Log in as `stanodavac1@gmail.com` (second browser)
7. Open Profile → check the verification status

   **Expected:** Status is `Verified`, the verification badge is green ✅

   **Fail if:** Status remains `pending` ❌

---

## Scenario 8 — devCode is not visible in production mode

**Goal:** The email verification code that assists developers must not be exposed in production.

> This scenario requires a brief configuration change — ask a developer to set `APP_ENV=production` in `.env` and restart the server, then restore `APP_ENV=local` after the test.

### Steps

1. Create a new test account with a fresh email (or use an account that is not yet verified)
2. Log in and trigger the verification email send
3. Open DevTools (F12) → Network tab → find the response for `/api/v1/me/verification/email/request`
4. Inspect the JSON response body

   **Expected (production):** The JSON does **not** contain a `devCode` field ✅

   **Fail if:** `"devCode": 123456` is visible in the response ❌

5. Repeat the same step with `APP_ENV=local`

   **Expected (local):** `devCode` is present in the response — this is intentional for development ✅

---

## Test results

| ID | Scenario | Result | Notes |
|---|---|---|---|
| S1 | Tomas cannot open Lana's KYC document | ☐ PASS / ☐ FAIL | |
| S2 | Guest cannot open a KYC document | ☐ PASS / ☐ FAIL | |
| S3 | Direct storage URL blocked by Nginx | ☐ PASS / ☐ FAIL / ☐ N/A (no Nginx) | |
| S4 | Tomas cannot open an attachment from someone else's chat | ☐ PASS / ☐ FAIL | |
| S5 | Conversation participant can access the attachment normally | ☐ PASS / ☐ FAIL | |
| S6 | Landlord gets 403 on the admin KYC endpoint | ☐ PASS / ☐ FAIL | |
| S7 | Admin approves KYC; profile updates to Verified | ☐ PASS / ☐ FAIL | |
| S8 | devCode absent in the production response | ☐ PASS / ☐ FAIL | |

**Phase 4 passes if:** all scenarios S1–S7 are PASS (S3 may be N/A if Nginx is not used in the local setup).
