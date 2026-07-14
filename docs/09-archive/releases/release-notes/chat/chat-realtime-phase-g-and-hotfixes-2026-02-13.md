# Chat Realtime Release Notes - 2026-02-13

Phase G implementation plus follow-up hotfixes completed through 2026-02-20.

## Scope
This release covers only chat/realtime behavior:
- attachments
- typing/presence signals
- notification realtime updates
- post-release hotfixes

## 1) Phase G implementation

### Backend
- Added attachment pipeline:
  - `chat_attachments` table (image/document metadata)
  - private storage path `storage/app/private/chat/{conversation_id}`
  - authorized access only for conversation participants via `/api/v1/chat/attachments/*`
  - thumbnail processing for image attachments via queued jobs
- Message send endpoint:
  - `POST /api/v1/conversations/{conversation}/messages` supports multipart (`body` + `attachments[]`)
  - validation limits:
    - max 5 files
    - max 10MB per file
    - MIME allowlist: `jpg`, `jpeg`, `png`, `webp`, `pdf`
  - rule: body is required when no attachment is sent
- Added signal endpoints:
  - typing: `POST/GET /api/v1/conversations/{id}/typing`
  - presence: `POST /api/v1/presence/ping`, `GET /api/v1/users/{id}/presence`
- Anti-abuse guardrails:
  - `chat_messages` limiter: 30/min per user/thread
  - `chat_attachments` limiter: 10 uploads/10min per user/thread
  - kept existing anti-spam rule: seeker can send up to 3 messages before landlord replies

### Frontend
- Chat composer supports:
  - image/pdf attachment upload
  - preview + remove before send
  - upload progress
  - message rendering for image grids and PDF chips/links
- Added typing/presence UI:
  - typing indicator below chat header
  - online badge in chat header for the other participant

## 2) Hotfix: chat messages required page refresh

### Problem
- Messages on `/chat/{id}` were not arriving without manual refresh.
- Browser console showed repeated `WebSocket connection failed` errors.

### Resolution
- Switched chat thread realtime refresh to polling in `frontend/src/pages/Chat.vue`.
- Removed dependency on Echo/WebSocket for chat thread rendering.
- Added periodic message refresh (~3s while chat page is open).
- Kept existing typing/presence polling behavior.

## 3) Hotfix: notification bell updated only after refresh

### Problem
- Notification badge and dropdown did not update until full page reload.

### Resolution
- `frontend/src/components/notifications/NotificationBell.vue`:
  - unread count polling every ~15s
  - refresh on `focus` and `visibilitychange`
  - fresh notification list fetch when dropdown opens
- `frontend/src/stores/notifications.ts`:
  - stopped overwriting `unreadCount` with partial paginated payloads

## 4) Hotfix: duplicate notifications

### Problem
- A single chat message created two `message.received` notifications.

### Root cause
- Event listener was registered twice via combination of:
  - explicit `$listen` mapping
  - automatic event discovery

### Resolution
- Explicitly disabled event discovery:
  - `backend/bootstrap/app.php`: `withEvents(discover: false)`
  - `backend/app/Providers/EventServiceProvider.php`: `shouldDiscoverEvents(): false`
- Added regression test:
  - `test_single_message_creates_single_message_notification`
  - `backend/tests/Feature/NotificationsApiTest.php`

## 5) Operational instructions for deploy/support
- After any event/listener wiring change run:
  - `php artisan event:clear`
  - `php artisan optimize:clear`
- For duplicate notification diagnosis:
  - run `php artisan event:list`
  - verify `MessageCreated` has no duplicate listener entries
- Primary runbook for this domain:
  - `docs/ops/CHAT-REALTIME-SUPPORT.md`

## 6) Owner checklist for future chat changes
Before release:
1. Verify two-browser chat realtime behavior.
2. Verify attachment upload and private download authorization.
3. Verify one-notification-per-message behavior.
4. Verify polling pauses/resumes on tab hidden/focus.
