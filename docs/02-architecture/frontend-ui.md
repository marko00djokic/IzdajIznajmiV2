# UI Tokens and Route Map

## Color Tokens
- `primary`: `#2F80ED` (CTA, active chips, icons)
- `primary.dark`: `#1F63C9` (pressed state)
- `primary.light`: `#E8F1FF` (light backgrounds)
- `surface`: `#F7F9FC` (page background)
- `muted`: `#6B7280` (secondary text)
- `line`: `#E5E7EB` (subtle borders)
- `card-shadow`: `rgba(47, 128, 237, 0.08)`

## Spacing and Radius
- Spacing scale: `4 / 8 / 12 / 16 / 20 / 24 px`
- Card/input radius: `18-20 px`
- Hero/map radius: `24-28 px`
- Shadows:
  - `shadow-soft`: `0 8px 24px rgba(0,0,0,0.06)`
  - `shadow-card`: `0 10px 30px rgba(47,128,237,0.08)`
- Bottom safe-area utility: `safe-bottom`

## Route Map

### Public/common
- `/` Home
- `/search` Search + filter sheet
- `/map` Map view
- `/listing/:id` Listing detail
- `/listing/:id/facilities` Facilities list
- `/listing/:id/reviews` Reviews list
- `/users/:id` Public profile
- `/login` Login
- `/register` Register

### Seeker and landlord user space
- `/favorites` Favorites
- `/saved-searches` Saved searches
- `/bookings` Booking/applications/viewings hub (includes reservation details modal)
- `/applications` Applications tab shortcut
- `/landlord/applications` Landlord applications shortcut
- `/viewings` Viewings tab shortcut
- `/messages` Conversation list
- `/chat` Chat deep-link resolver
- `/chat/:id` Chat thread
- `/messages/:id` Legacy redirect to `/chat/:id`
- `/transactions` Transaction list
- `/transactions/:id` Transaction detail

### Profile/settings
- `/profile`
- `/profile/verification`
- `/settings/profile`
- `/settings/personal`
- `/settings/security`
- `/settings/notifications`
- `/settings/language`
- `/settings/legal`
- `/notifications`

### Landlord listings
- `/landlord/listings`
- `/landlord/listings/new`
- `/landlord/listings/:id/edit`

### Admin
- `/admin`
- `/admin/moderation`
- `/admin/moderation/reports/:id`
- `/admin/ratings`
- `/admin/kyc`
- `/admin/transactions`
- `/admin/transactions/:id`
- `/admin/users`
- `/admin/users/:id`

## Route Guard Rules
- Routes with `meta.roles` require auth and matching role.
- Unauthorized users are redirected to `/login` with `returnUrl`.
- Authenticated users without required role are redirected to `/` and shown "Access denied" toast.

## Core Frontend Data Shapes (simplified)
- `Listing`: `{ id, title, city, country, address?, lat?, lng?, pricePerMonth, rating, reviewsCount, coverImage, images?, description?, beds, baths, category, instantBook?, facilities?, ownerId?, status? }`
- `Booking`: `{ id, listingId, listingTitle, status ('booked'|'history'), startDate?, endDate?, datesRange, pricePerMonth, calculatedPrice?, currency? }`
- `Application`: `{ id, listing, participants { seekerId, landlordId, seekerName?, landlordName? }, message?, status ('submitted'|'accepted'|'rejected'|'withdrawn'), startDate?, endDate?, createdAt?, updatedAt?, withdrawnAt?, currency?, calculatedPrice? }`
- `ViewingSlot`: `{ id, listingId, startsAt, endsAt, capacity, isActive, pattern?, daysOfWeek?, timeFrom?, timeTo? }`
- `ViewingRequest`: `{ id, status ('requested'|'confirmed'|'rejected'|'cancelled'), cancelledBy?, slot?, listing?, participants, createdAt }`
- `Conversation`: `{ id, listingId?, counterpart, unreadCount, lastMessage, updatedAt }`
- `Message`: `{ id, conversationId, from, text?, attachments?, createdAt }`
- `Notification`: `{ id, type, title, body, readAt?, createdAt, deepLink? }`
- `RentalTransaction`: `{ id, status, listing, participants, depositAmount?, rentAmount?, currency, contract?, payments[] }`
