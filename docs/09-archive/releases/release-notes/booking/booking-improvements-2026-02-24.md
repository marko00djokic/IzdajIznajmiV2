# Booking Improvements — 2026-02-24

## Summary

A set of UX and validation improvements to the application (listing inquiry) flow, covering the booking form on `/listing/{id}` and the bookings management page at `/bookings?tab=requests`.

---

## Changes

### 1. Optional message field

**Previously:** The message textarea ("Podelite planove ili vreme") was required (min 5 characters) when submitting a booking request.

**Now:** The field is optional. Seekers may submit a booking request without a message.

Affected files:
- `backend/app/Http/Requests/StoreBookingRequestRequest.php` — rule changed from `required, min:5` to `nullable`
- `backend/database/migrations/2026_05_03_000000_make_message_nullable_on_booking_requests_table.php` — column made nullable
- `frontend/src/pages/ListingDetail.vue` — removed `message.trim().length >= 5` from `isFormValid`

---

### 2. Re-application after reservation period

**Previously:** A seeker was permanently blocked from applying to the same listing once any application existed, regardless of dates.

**Now:** Re-application is allowed under two conditions:
1. The previous reservation period has expired (`endDate < today`)
2. The new `startDate` is strictly after the `endDate` of all active (`submitted`/`accepted`) applications for the same listing

The date picker minimum on the booking form is automatically set to the day after the active period ends.

Affected files:
- `backend/app/Http/Controllers/ApplicationController.php` — replaced blanket `exists()` check with date-aware conflict query (`end_date >= newStartDate`)
- `backend/database/migrations/2026_05_04_000000_drop_unique_constraint_on_applications_table.php` — dropped `UNIQUE(listing_id, seeker_id)` DB constraint
- `frontend/src/pages/ListingDetail.vue` — `hasApplied` replaced with `activeRequestEndDate` + `minStartDate` computed; button always enabled for seekers; date picker min enforced

---

### 3. Withdraw confirmation dialog

**Previously:** Clicking "Povuci" on `/bookings?tab=requests` immediately withdrew the request with no confirmation.

**Now:** A styled confirmation modal (matching app design system) opens before the withdrawal is executed.

Affected files:
- `frontend/src/pages/Bookings.vue` — `confirmWithdraw`, `doWithdraw`, `cancelWithdraw` functions; `showWithdrawConfirm` / `pendingWithdrawId` state; `ModalSheet` confirmation UI

---

## Post-deploy verification

1. Apply to a listing → submit without a message → confirm `201`
2. Apply to the same listing with `startDate` before the existing `endDate` → confirm `422`
3. Apply to the same listing with `startDate` strictly after the existing `endDate` → confirm `201`
4. Navigate to `/bookings?tab=requests` → click "Povuci" → confirm modal appears → confirm withdrawal
5. Run `php artisan migrate` to apply the two new migrations
