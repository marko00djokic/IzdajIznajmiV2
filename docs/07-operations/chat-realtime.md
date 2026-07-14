# Chat and Realtime Support Runbook

> Status: `active`
> Poslednja ciljana provera: 2026-07-15
> Source of truth: chat frontend/backend kod, Reverb config, Jobs i testovi

Operational guide for support and continued development of chat/realtime functionality.

## Scope
- Chat messages and thread rules
- Chat attachments (image/pdf in private storage)
- Typing indicator and online presence (polling)
- In-app notification badge/dropdown realtime refresh (polling)

## Architecture (current state)
- Browser UX currently relies on polling; backend also broadcasts message
  events through Reverb.
- Chat and notification UI continue to work without WebSocket connectivity.
- Backend remains the source of truth; frontend refreshes state periodically.
- Chat attachment originals are private and only accessible to conversation participants.

## Backend key points
- Chat messages:
  - `POST /api/v1/conversations/{conversation}/messages`
  - `GET /api/v1/conversations/{conversation}/messages`
  - Supports `since_id` or `after` for incremental fetch.
  - Supports `ETag` / `If-None-Match` (`304` when unchanged).
- Typing and presence:
  - `POST /api/v1/conversations/{conversation}/typing`
  - `GET /api/v1/conversations/{conversation}/typing`
  - `POST /api/v1/presence/ping`
  - `GET /api/v1/users/{user}/presence`
  - `GET /api/v1/presence/users?ids[]=...` (batch presence)
- Attachments:
  - `GET /api/v1/chat/attachments/{attachment}`
  - `GET /api/v1/chat/attachments/{attachment}/thumb`
- Rate limits:
  - `chat_messages`: 30/min per user/thread
  - `chat_attachments`: 10/10min per user/thread

## Frontend polling intervals
- Chat thread messages (`frontend/src/pages/Chat.vue`):
  - Starts around 3s and applies exponential backoff up to around 30s when idle.
  - Backoff resets on activity (new message or user interaction).
  - Polling pauses when tab is hidden and resumes on focus/visibilitychange.
- Typing:
  - Polls status every ~4s.
  - Sends `is_typing=true/false` on typing/start/stop/blur/send.
- Presence:
  - Sends ping every ~25s.
  - Checks peer presence every ~30s.
- Notifications (`frontend/src/components/notifications/NotificationBell.vue`):
  - Polls unread count every ~15s.
  - Uses `ETag` / `If-None-Match` (`304`) when unchanged.
  - Polling pauses when tab is hidden.
  - Refreshes on focus/visibilitychange.
  - Opening dropdown triggers fresh list fetch.

## Notification duplication prevention
- Event auto-discovery is intentionally disabled:
  - `backend/bootstrap/app.php` -> `withEvents(discover: false)`
  - `backend/app/Providers/EventServiceProvider.php` -> `shouldDiscoverEvents(): false`
- Reason: avoids duplicate listener registration (`Class` + `Class@handle`) and duplicate notifications.

## Operational diagnostics
Verify event registrations:
```bash
cd backend
php artisan event:clear
php artisan event:list
```
Expected:
- `App\Events\MessageCreated` should have a single app listener (`SendMessageNotification`), not duplicates.

After deploy/config changes:
```bash
php artisan optimize:clear
```

## Quick troubleshooting
1. Chat does not refresh without page reload:
- Confirm frontend is sending periodic `GET /api/v1/conversations/{id}/messages`.
- Confirm the polling fallback is active; separately inspect Reverb when the
  incident concerns WebSocket delivery.

2. Typing or online indicators do not work:
- Check `POST/GET typing` and `POST ping + GET presence` calls in browser network tab.
- Check backend cache keys and TTL (`typing:*`, `presence:*`).

3. Notification badge is delayed:
- Confirm periodic `GET /api/v1/notifications/unread-count` calls (~15s + focus refresh).
- Confirm `fetchNotifications` does not overwrite `unreadCount` from partial page payload.

4. Duplicate notifications:
- Run `php artisan event:list`.
- If both `SendMessageNotification` and `SendMessageNotification@handle` appear, event discovery is misconfigured or stale cache is present.
- Run `php artisan event:clear` and `php artisan optimize:clear`, then redeploy/restart backend processes.

## Manual realtime QA
1. Open the same chat for two users in separate tabs/browsers.
2. Send a message from user A and confirm user B receives it without refresh.
3. Type in user A and confirm typing indicator for user B.
4. Keep both users active and confirm online badge behavior.
5. Send another message and confirm notification badge increments without refresh.
6. Confirm exactly one notification per message (no duplicates).

## Relevant files
Backend:
- `backend/app/Http/Controllers/ConversationController.php`
- `backend/app/Http/Controllers/ChatSignalController.php`
- `backend/app/Listeners/SendMessageNotification.php`
- `backend/app/Providers/EventServiceProvider.php`
- `backend/bootstrap/app.php`

Frontend:
- `frontend/src/pages/Chat.vue`
- `frontend/src/components/notifications/NotificationBell.vue`
- `frontend/src/stores/notifications.ts`
- `frontend/src/stores/chat.ts`
