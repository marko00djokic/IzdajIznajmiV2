# API Contract (Frontend-Oriented)

Base URL: `/api/v1`

Tranzicioni neversionisani alias trenutno izlaže isti ugovor pod `/api/*`.
Novi klijenti koriste isključivo `/api/v1`.

## Auth (Sanctum cookie/session)
- `GET /sanctum/csrf-cookie`
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`

## Health and webhooks
- `GET /api/v1/health`
- `GET /api/v1/health/ready`
- `GET /api/v1/health/queue`
- `POST /api/v1/webhooks/stripe`

## Public discovery
- `GET /api/v1/listings`
  - Typical filters: `category`, `priceMin`, `priceMax`, `rooms`, `areaMin`, `areaMax`, `guests`, `instantBook`, `location`, `facilities[]`, `rating`, `status`, `page`, `perPage`.
  - Geo filters: `centerLat`, `centerLng`, `radiusKm`, `mapMode=true`.
- `GET /api/v1/listings/{listing}`
- `GET /api/v1/listings/{listing}/similar`
- `GET /api/v1/listings/{listing}/ratings`
- `GET /api/v1/facilities`
- `GET /api/v1/search/listings`
- `GET /api/v1/search/suggest`
- `GET /api/v1/geocode`
- `GET /api/v1/geocode/suggest`
- `GET /api/v1/users/{user}`
- `GET /api/v1/users/{user}/ratings`

## Security (authenticated)
- MFA:
  - `POST /api/v1/security/mfa/setup`
  - `POST /api/v1/security/mfa/confirm`
  - `POST /api/v1/security/mfa/verify`
  - `POST /api/v1/security/mfa/disable`
  - `POST /api/v1/security/mfa/recovery-codes`
- Sessions/devices:
  - `GET /api/v1/security/sessions`
  - `POST /api/v1/security/sessions/{session}/revoke`
  - `POST /api/v1/security/sessions/revoke-others`

## Listings (landlord/admin)
- `GET /api/v1/landlord/listings`
- `POST /api/v1/landlord/listings` (multipart, images + facilities)
- `PUT /api/v1/landlord/listings/{listing}` (multipart)
- Lifecycle:
  - `PATCH /api/v1/landlord/listings/{listing}/publish` -> `active`
  - `PATCH /api/v1/landlord/listings/{listing}/unpublish` -> `paused`
  - `PATCH /api/v1/landlord/listings/{listing}/archive`
  - `PATCH /api/v1/landlord/listings/{listing}/restore`
  - `PATCH /api/v1/landlord/listings/{listing}/mark-rented`
  - `PATCH /api/v1/landlord/listings/{listing}/mark-available`
- Location pin override:
  - `PATCH /api/v1/listings/{listing}/location`
  - `POST /api/v1/listings/{listing}/location/reset`

## Applications (listing inquiry flow)
- `POST /api/v1/listings/{listing}/apply`
  - Request body:
    - `message` (optional, string, max 2000)
    - `startDate` (required, date, `>= today`)
    - `endDate` (required, date, `> startDate`, minimum reservation window: 1 month)
  - Reservation pricing model is monthly (`pricePerMonth`) with `EUR` currency.
  - Re-application rules: a seeker may apply to the same listing again only if:
    - All previous `submitted`/`accepted` applications for that listing have `endDate < startDate` of the new application (i.e. the new period starts strictly after all active periods end), **or**
    - All previous active applications have expired (`endDate < today`).
  - Returns `422` if a `submitted` or `accepted` application exists with `endDate >= newStartDate`.
- `GET /api/v1/seeker/applications`
- `GET /api/v1/landlord/applications` (`listing_id` optional)
- `PATCH /api/v1/applications/{application}`
  - Statuses: `submitted | accepted | rejected | withdrawn`
  - When status changes to `withdrawn`, backend stores `withdrawnAt`.

Application response payload (key fields):
- `id`, `status`, `message`
- `startDate`, `endDate`
- `createdAt`, `updatedAt`, `withdrawnAt`
- `currency` (`EUR`), `calculatedPrice`
- `listing { id, title, city, pricePerMonth, coverImage, status }`
- `participants { seekerId, landlordId, seekerName, landlordName }`

## Viewing appointments (separate from applications)
### Slots
- `GET /api/v1/listings/{listing}/viewing-slots`
- `POST /api/v1/listings/{listing}/viewing-slots`
- `PATCH /api/v1/viewing-slots/{viewingSlot}`
- `DELETE /api/v1/viewing-slots/{viewingSlot}`

### Requests
- `POST /api/v1/viewing-slots/{viewingSlot}/request`
- `GET /api/v1/seeker/viewing-requests`
- `GET /api/v1/landlord/viewing-requests?listing_id=`
- `PATCH /api/v1/viewing-requests/{viewingRequest}/confirm`
- `PATCH /api/v1/viewing-requests/{viewingRequest}/reject`
- `PATCH /api/v1/viewing-requests/{viewingRequest}/cancel`
- `GET /api/v1/viewing-requests/{viewingRequest}/ics`

## Conversations and messaging
- `GET /api/v1/conversations`
- `GET /api/v1/conversations/{conversation}`
- `GET /api/v1/conversations/{conversation}/messages`
  - Supports `since_id`/`after` incremental fetch.
  - Supports `ETag` + `If-None-Match`.
- `POST /api/v1/conversations/{conversation}/messages` (multipart: `body?`, `attachments[]?`)
- Listing/application thread helpers:
  - `GET|POST /api/v1/listings/{listing}/conversation`
  - `GET /api/v1/listings/{listing}/messages`
  - `POST /api/v1/listings/{listing}/messages`
  - `POST /api/v1/applications/{application}/conversation`
- Read receipts and realtime signals:
  - `POST /api/v1/conversations/{conversation}/read`
  - `POST /api/v1/conversations/{conversation}/typing`
  - `GET /api/v1/conversations/{conversation}/typing`
  - `POST /api/v1/presence/ping`
  - `GET /api/v1/users/{user}/presence`
  - `GET /api/v1/presence/users?ids[]=...`
- Attachments:
  - `GET /api/v1/chat/attachments/{attachment}`
  - `GET /api/v1/chat/attachments/{attachment}/thumb`

## Ratings and reporting
- Ratings:
  - `POST /api/v1/listings/{listing}/ratings`
  - `GET /api/v1/me/ratings`
  - `POST /api/v1/ratings/{rating}/replies`
- Reports:
  - `POST /api/v1/ratings/{rating}/report`
  - `POST /api/v1/listing-ratings/{listingRating}/report`
  - `POST /api/v1/messages/{message}/report`
  - `POST /api/v1/listings/{listing}/report`
  - `POST /api/v1/transactions/{transaction}/report`

## Notifications
- `GET /api/v1/notifications`
- `PATCH /api/v1/notifications/{notification}/read`
- `PATCH /api/v1/notifications/read-all`
- `GET /api/v1/notifications/unread-count`
- Preferences:
  - `GET /api/v1/notification-preferences`
  - `PUT /api/v1/notification-preferences`

## Web Push
- `GET /api/v1/push/subscriptions`
- `POST /api/v1/push/subscribe`
- `POST /api/v1/push/unsubscribe`

## Saved search and recommendations
- `GET /api/v1/saved-searches`
- `POST /api/v1/saved-searches`
- `PUT /api/v1/saved-searches/{savedSearch}`
- `DELETE /api/v1/saved-searches/{savedSearch}`
- `GET /api/v1/recommendations`

## Transactions
- `GET /api/v1/transactions`
- `POST /api/v1/transactions`
- `GET /api/v1/transactions/{transaction}`
- Contracts:
  - `POST /api/v1/transactions/{transaction}/contracts`
  - `GET /api/v1/transactions/{transaction}/contracts/latest`
  - `POST /api/v1/contracts/{contract}/sign`
  - `GET /api/v1/contracts/{contract}/pdf`
- Payments/status:
  - `POST /api/v1/transactions/{transaction}/payments/deposit/session`
  - `POST /api/v1/transactions/{transaction}/payments/deposit/cash`
  - `POST /api/v1/transactions/{transaction}/move-in/confirm`
  - `POST /api/v1/transactions/{transaction}/complete`
- Shared user history:
  - `GET /api/v1/users/{user}/transactions/shared`

## My account
- `PATCH /api/v1/me/profile`
- `POST /api/v1/me/avatar`
- `PATCH /api/v1/me/password`
- Email verification:
  - `POST /api/v1/me/verification/email/request`
  - `POST /api/v1/me/verification/email/confirm`

## KYC
- `POST /api/v1/kyc/submissions`
- `GET /api/v1/kyc/submissions/me`
- `POST /api/v1/kyc/submissions/{submission}/withdraw`
- `GET /api/v1/kyc/documents/{document}`

## Admin APIs
### KYC
- `GET /api/v1/admin/kyc/submissions`
- `GET /api/v1/admin/kyc/submissions/{submission}`
- `PATCH /api/v1/admin/kyc/submissions/{submission}/approve`
- `PATCH /api/v1/admin/kyc/submissions/{submission}/reject`
- `DELETE /api/v1/admin/kyc/submissions/{submission}/redact`

### Ratings and users
- `GET /api/v1/admin/ratings`
- `GET /api/v1/admin/ratings/{rating}`
- `DELETE /api/v1/admin/ratings/{rating}`
- `GET /api/v1/admin/users`
- `GET /api/v1/admin/users/{user}/security`
- `GET /api/v1/admin/users/{user}/sessions`
- `POST /api/v1/admin/users/{user}/sessions/revoke-all`
- `PATCH /api/v1/admin/users/{user}/flag-suspicious`
- `POST /api/v1/admin/users/{user}/fraud/clear`
- `PATCH /api/v1/admin/users/{user}/badges`

### Moderation and KPI
- `GET /api/v1/admin/moderation/queue`
- `GET /api/v1/admin/moderation/reports/{report}`
- `PATCH /api/v1/admin/moderation/reports/{report}`
- `GET /api/v1/admin/kpi/summary`
- `GET /api/v1/admin/kpi/conversion`
- `GET /api/v1/admin/kpi/trends`

### Admin transaction actions
- `GET /api/v1/admin/transactions`
- `GET /api/v1/admin/transactions/{transaction}`
- `PATCH /api/v1/admin/transactions/{transaction}/mark-disputed`
- `PATCH /api/v1/admin/transactions/{transaction}/cancel`
- `POST /api/v1/admin/transactions/{transaction}/payout`

### Impersonation
- `POST /api/v1/admin/impersonate/{user}`
- `POST /api/v1/admin/impersonate/stop`

## Core response entities (short form)
- `Listing`
- `Application`
- `ViewingSlot`
- `ViewingRequest`
- `Conversation`
- `Message`
- `Notification`
- `SavedSearch`
- `RentalTransaction`
- `Contract`
- `Payment`
- `KycSubmission`
