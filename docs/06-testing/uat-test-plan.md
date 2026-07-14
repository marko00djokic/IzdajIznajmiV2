# UAT Test Plan - IzdajIznajmi V2

> Status: `active`; tekst ekrana može varirati sa i18n jezikom
> Poslednja ciljana provera setupa: 2026-07-15
> Source of truth: aktuelni UI, seed podaci i acceptance odluka ownera

This document guides clients, stakeholders, and product owners through business-level validation of the V2 prototype in the real UI.

## A) Introduction
- UAT is performed by clicking through the application (desktop or mobile).
- Backend is available, but UAT should be executed from UI screens without technical API steps.
- Goal: confirm that core user journeys are understandable, complete, and stable.

## B) Before You Start
- Open the application link provided by the team.
- For local testing, a developer should start the app and share the URL (usually `http://localhost:5173`).
- If you see a blank page or error, refresh and retry once before reporting.

Test users (password: `password`):
- Admin: `admin@gmail.com`
- Landlord: `stanodavac1@gmail.com`, `stanodavac2@gmail.com`
- Tenant: `trazilac1@gmail.com`, `trazilac2@gmail.com`, `trazilac3@gmail.com`

## C) Quick Demo (5-10 min)
1. Login as tenant `trazilac1@gmail.com` -> Home opens.
2. On Home, review "Most Popular" and "Recommended" sections -> open one card.
3. On listing detail, click "Send Inquiry", enter message/dates -> modal closes, application status is `submitted`.
4. Open "My Booking" -> "Requests" tab -> new application is visible.
5. Open "Messages", choose a conversation, send a message -> message bubble appears.
6. Logout (Profile -> Logout) -> app returns to guest state.

## D) Detailed UAT Scenarios

### 1) Guest browsing (without login)
- User: Guest
- Steps:
  1. Open app and check bottom navigation tabs: "Home", "My Booking", "Message", "Profile".
  2. On Home, scroll "Most Popular" (horizontal cards) and "Recommended for you" (vertical cards).
  3. Click search icon in header or open "Search" tab.
  4. Enter keyword, open filter panel, choose category/price/rating, save filters.
  5. Open "Map" and review map + highlighted card.
  6. Open listing detail and review: images, "Common Facilities", "Description", "Location", "Reviews".
  7. Open "Facilities" (See all) and "Reviews" (View all).
- Expected:
  - Listing cards load with images and price.
  - Filters visibly update the list.
  - Detail page sections are visible (placeholders are acceptable where expected).
- PASS/FAIL:
  - FAIL if key links do not open, lists do not load, or detail page does not open.
- Note:
  - Map may be a static placeholder image in some environments.
  - Online status in chat can be placeholder in mock-only flows.

### 2) Tenant flow - favorites and application
- User: Tenant (`trazilac1@gmail.com` recommended)
- Steps:
  1. Login and confirm authenticated Home view.
  2. On Home, click heart icon on a listing card.
  3. Open "Favorites" ("My Favorite" tab) and verify saved listing appears.
  4. Open a listing detail page.
  5. Click "Send Inquiry", enter dates/guests/message, submit.
  6. Open "My Booking" -> "Requests" and confirm application status badge (`submitted/accepted/rejected/withdrawn`).
  7. Open "Messages", enter a thread, send message and attach image or PDF.
  8. While the other side types, verify "is typing..." indicator.
  9. Verify online badge when the other participant is active.
  10. Logout from Profile.
- Expected:
  - Favorites toggle immediately.
  - Application appears in Requests with correct status badge.
  - Message and attachment are visible in chat bubble.
  - Typing indicator and online badge behave correctly.
- PASS/FAIL:
  - FAIL if application is missing or chat cannot show newly sent messages.

### 3) Landlord flow - listings and applications
- User: Landlord (`stanodavac1@gmail.com` recommended)
- Steps:
  1. Login and confirm landlord role context.
  2. Profile -> "My Listings" opens listing list with status badge.
  3. Click "+ New Listing", fill required fields, save.
  4. Open an existing listing in edit mode, change title or price, save.
  5. Open "My Booking" -> "Requests", accept or reject a submitted application.
  6. Open "Messages" and verify conversation list and thread visibility.
  7. Logout.
- Expected:
  - New listing card appears with title and image.
  - Edited values are reflected after save.
  - Request status badge updates to accepted/rejected.
- PASS/FAIL:
  - FAIL if listing cannot be created/edited or application status does not update.
- Note:
  - Some environments may use placeholder media upload flows.

### 4) Error and empty states
- Steps:
  1. Open "Messages" as user with no conversations -> should show empty state message.
  2. Open "Favorites" with no favorites -> should show empty state message.
  3. Open "Search" with restrictive filters -> should show "No results" message.
  4. As guest, try opening "My Booking" -> should redirect and show "Access denied" message.
  5. If generic error appears, refresh and retry once; if persistent, capture URL + timestamp.
- Expected:
  - Empty states are clear and user-friendly.
  - Unauthorized routes remain blocked.
  - Retry behavior works for transient issues.
- PASS/FAIL:
  - FAIL if empty states are missing or protected areas are accessible without auth.

## E) Final Acceptance Checklist
- Login/Logout works for tenant and landlord.
- Home/Search/Map load listing content and open listing details.
- Favorites toggle works and favorites list updates.
- "Send Inquiry" creates an application visible under "My Booking" -> "Requests".
- Landlord can create and edit listings.
- Landlord can accept/reject submitted applications.
- Messages list and chat support sending text and attachments.
- Typing indicator and online badge work in chat.
- Empty/error states are displayed instead of blank screens.

## F) Reporting Format for UAT Feedback
When reporting defects or blockers, include:
- Environment URL
- User role and account used
- Exact timestamp and timezone
- Short repro steps
- Expected result vs actual result
- Screenshot or screen recording (if available)
