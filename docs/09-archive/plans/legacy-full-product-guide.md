# Full Product Guide (End-User Friendly)

This document explains all app features in simple language, grouped by user role.

## 1) What This App Is
IzdajIznajmi connects tenants (seekers) and landlords.

You can:
- find rental listings
- send applications
- schedule in-person viewings
- chat with the other side
- complete verification steps
- sign rental contracts and handle deposit payments

## 2) User Roles
The app has 4 main roles:
- Guest (not logged in)
- Seeker/Tenant
- Landlord
- Admin

## 3) Features Available to Everyone (where applicable)

### Account and login
- Register account
- Login/logout
- Session-based secure login

### Profile settings
- Update personal profile data
- Upload/change profile avatar
- Change password
- Manage account verification status
- View public profile information where applicable

### Notifications
- In-app notifications (inside the app)
- Mark notifications as read
- Notification preferences by type
- Daily/weekly digest options
- Browser push notifications (supported browsers/devices)
- Manage push by device (enable/disable specific device endpoint)

### Security
- Optional MFA (Authenticator app / TOTP)
- Recovery codes for MFA
- Trusted devices
- Session/device history and session revocation

### Language/UI
- Multi-language text support (EN/SR)
- Responsive web UI for desktop/mobile

## 4) Guest Features (No Login)
- Open Home and browse public listing cards
- Use Search and filter listings
- Use search suggestions (city/query/amenity hints)
- Open listing details (images, facilities, description, location, reviews)
- Open Map view and inspect listing positions
- Open full facilities list and full reviews list pages
- Navigate app pages that do not require authentication

Guest restrictions:
- Cannot apply to listings
- Cannot request viewing slots
- Cannot access chat
- Cannot manage favorites/saved searches
- Cannot access landlord/admin features

## 5) Seeker/Tenant Features

### 5.1 Browse and discovery
- Browse active listings
- Search by city/location text
- Filter by:
  - price range
  - category
  - rooms/area/guests
  - rating
  - instant booking
  - facilities/amenities
- Use map-focused discovery mode
- Use search autosuggest to speed up filtering
- Open full listing detail pages
- Listing prices are presented as monthly prices in EUR (`€`).

### 5.2 Recommendations
- See personalized recommendations based on your activity
- Open similar listings from a listing detail
- Understand simple “why this listing” signals (for example: same city, similar price)

### 5.3 Favorites
- Add/remove listings to favorites
- Open dedicated favorites list

### 5.4 Saved searches and alerts
- Save search filters for later reuse
- Receive notification when new listings match your saved filters
- Open deep-link from notification back to filtered search

### 5.5 Applications
- Apply to active listings
- Add an optional message for landlord
- Select reservation window (`startDate` + `endDate`)
- Reservation window must be at least one month
- Re-application to the same listing is allowed when:
  - The previous reservation period has expired (`endDate < today`), or
  - The new `startDate` is strictly after all active (`submitted`/`accepted`) applications' `endDate`
- Track application status:
  - submitted
  - accepted
  - rejected
  - withdrawn
- View your own applications list
- See reservation metadata in application details:
  - created/updated timestamps
  - withdrawn timestamp (when withdrawn)
  - monthly price and calculated total price
  - participant names (seeker/landlord)

### 5.6 Messaging (chat)
- Open conversation list
- Enter a listing-related conversation
- Send text messages
- Send attachments (image/PDF)
- See upload progress for attachments
- See typing indicator
- See online presence badge

### 5.7 Ratings and trust
- Rate landlord/listing after required conditions are met
- Report inappropriate ratings/content
- Report problematic listing-related content to moderation queue

### 5.8 Verification and KYC
- Request email verification code
- Confirm verification code
- Submit KYC documents (where required)
- Check KYC status:
  - pending
  - approved
  - rejected
  - withdrawn

### 5.9 Transaction participation
When a landlord starts a rental transaction, tenant can:
- review generated contract
- e-sign contract
- pay deposit via Stripe checkout
- use cash-deposit confirmation flow where offered
- track transaction status updates
- report transaction issues

### 5.10 Viewing appointments
- Request available viewing slot on a listing
- Add a note when requesting a slot
- Track viewing request status:
  - requested
  - confirmed
  - rejected
  - cancelled
- Cancel viewing request when needed
- Download `.ics` calendar file for confirmed viewing

## 6) Landlord Features

### 6.1 Listing management
- Open “My Listings”
- Create listing
- Edit listing fields (title, price, address, category, description, beds/baths, etc.)
- Upload listing images
- Manage listing facilities
- Set cover image and image order
- Set/adjust map pin location
- Reset location to automatic geocoding

### 6.2 Listing lifecycle and status
- Save draft
- Publish/activate listing
- Use instant-book option when creating/updating listing
- Pause listing
- Archive and restore listing
- Mark listing as rented
- Automatic expiration for old active listings

### 6.3 Incoming demand management
- View incoming applications
- Accept/reject/mark withdrawn based on policy
- View seeker applications linked to listings
- Keep communication in listing-scoped threads

### 6.4 Viewing slot management
- Create viewing slots per listing
- Set slot window, capacity, and active/paused state
- Pause/reactivate slot availability
- Remove slot when there are no active requests
- Confirm/reject/cancel viewing requests

### 6.5 Messaging
- Full chat features with seekers:
  - text
  - attachments
  - typing indicator
  - online status

### 6.6 Verification and trust
- Submit KYC verification documents
- Track verification state
- Become verified landlord after approval
- Eligible for “top landlord” badge based on metrics (or admin override)

### 6.7 Transaction flow ownership
Landlord can:
- start transaction for listing + seeker
- generate rental contract PDF
- sign contract
- confirm move-in
- complete transaction lifecycle
- report transaction issues

## 7) Admin Features

### 7.1 Admin dashboard
- View KPI cards and trend summaries
- Monitor platform activity at high level

### 7.2 Moderation
- Review reports (ratings/messages/listings/transactions)
- Resolve/dismiss reports
- Remove or moderate problematic content where policy allows

### 7.3 KYC administration
- View pending KYC submissions
- Approve/reject KYC with notes
- Access KYC files through authorized private endpoints
- Keep audit trail of sensitive document access

### 7.4 User and security controls
- Browse/filter users
- View user security summary
- View/revoke user sessions/devices
- Revoke all sessions when needed
- Mark suspicious users and clear suspicion after review

### 7.5 Badge and trust controls
- Review landlord trust metrics
- Override badge state (example: top landlord)

### 7.6 Transaction administration
- Access admin transaction actions (for example payout/completion paths enabled in admin API)

### 7.7 Admin impersonation
- Temporarily impersonate user role for troubleshooting support issues
- Exit impersonation mode safely from UI

## 8) Notifications Explained (Simple)
The app can notify users in 3 ways:
- In-app notification center
- Notification badge/count updates
- Browser push notification (if enabled and supported)

Typical notification topics:
- new application
- application status change
- new viewing request
- viewing request confirmed/cancelled
- new chat message
- rating-related events
- KYC status updates
- transaction status updates
- moderation updates
- daily/weekly digest summaries
- saved-search match alerts

## 9) Verification and Trust Model
- Email verification is active for account trust
- Phone verification flow is currently disabled
- KYC documents are private and never publicly exposed
- Only allowed users (owner/admin) can access protected verification files

## 10) Safety and Anti-Abuse Rules (User-visible impact)
- Chat message rate limits prevent spam
- Attachment upload limits apply (count, type, size)
- Seeker message anti-spam rule limits repeated one-sided messaging until landlord replies
- Rapid repeated applications can trigger fraud protection and temporary blocks
- Authorization checks prevent access to other users’ protected data
- Duplicate notification prevention is implemented

## 11) Typical End-to-End Journeys

### Tenant journey
1. Register/login
2. Search listings and filter
3. Save favorites or saved searches
4. Apply to a listing
5. Optionally request in-person viewing slot
6. Chat with landlord
7. Complete verification steps if required
8. Sign contract and pay deposit

### Landlord journey
1. Login
2. Create/publish listing
3. Configure viewing slots
4. Receive applications and viewing requests
5. Chat with seeker
6. Approve and start transaction
7. Generate/sign contract and confirm move-in

### Admin journey
1. Monitor KPIs
2. Moderate reports
3. Process KYC queue
4. Manage high-risk/security cases

## 12) Important Notes for Users
- Some features depend on role permissions.
- Some features (especially push notifications) depend on browser/device support.
- Production behavior may differ from local/demo mode in mock data and integrations.

## 13) Quick Glossary
- Listing: rental offer created by landlord.
- Inquiry/Request: tenant request sent to landlord.
- Application: formal apply action to a listing.
- KYC: identity/address verification process.
- Digest: grouped notifications (daily/weekly).
- Push notification: browser-level notification sent to your device.
