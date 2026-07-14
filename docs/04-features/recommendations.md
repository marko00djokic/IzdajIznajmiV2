# Recommendations & badges

> Status: `implemented`
> Poslednja ciljana provera: 2026-07-15
> Source of truth: recommendation services/controllers, observers, commands i testovi

## Data signals & privacy
- Listing events are stored in `listing_events` with `event_type` = `view|favorite|apply`, `user_id` (nullable for guests), `listing_id`, optional `meta`, and `created_at`.
- View events are recorded only for authenticated seekers and deduped per user/listing per 12 hours.
- Search filter snapshots are stored in `search_filter_snapshots` for authenticated seekers when `recordSearch=true` is sent on search endpoints. These are used only to improve the seeker's own recommendations.
- Seeker behavior is never exposed to landlords.

## Similar listings (content based)
Scoring uses explainable, weighted signals:
- Same city (strong boost)
- Price proximity (within 10-20%)
- Rooms same or +/-1
- Area proximity (within 10-20%)
- Amenities overlap (Jaccard similarity)
- Verified landlord (small boost)

The response includes `why` reasons (e.g., `Same city`, `Similar price`).

## Personalized recommendations (seeker)
Preference profile signals:
- Top cities (from views, saved searches, recent search snapshots)
- Typical price range (median + saved search bounds)
- Typical rooms/area
- Top amenities

Candidates are scored and blended from:
- Similar to recently viewed listings
- Matches saved searches
- Recent search snapshots
- Fresh listings in preferred city

Results are cached per user for ~5-10 minutes to reduce load.

## Top landlord badge
Criteria for `top_landlord`:
- `ratings_count >= 5`
- `avg_rating >= 4.5` (30d if available, else all-time)
- `median_response_time_minutes <= 360` (6 hours)
- `completed_transactions_count >= 3`

Response time is based on tenant-to-landlord replies in the last 30 days.

Badges are suppressed for `is_suspicious` landlords.
Admin override (in `users.badge_override_json`) can force show/hide badges.

## Recompute command & schedule
- Manual: `php artisan badges:recompute`
- Scheduled: daily at 03:00 (see `backend/bootstrap/app.php`)
