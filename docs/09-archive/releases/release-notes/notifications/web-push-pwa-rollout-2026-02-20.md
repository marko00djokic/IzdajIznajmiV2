# Web Push and PWA Rollout Notes - 2026-02-20

This document captures the full rollout of browser Web Push (desktop + Android), service worker integration, and production hardening updates.

## 1) Goal and scope

### Goals
- Add browser push notifications for existing in-app notification events.
- Implement per-device opt-in/opt-out flow.
- Deliver push through backend queue using VAPID keys.
- Avoid regression in existing notification behavior.

### Out of scope
- Native iOS app push/APNS flow.
- Quiet-hours data model and scheduling logic.

## 2) Backend architecture updates

### 2.1 Data model
Added migrations:
- `backend/database/migrations/2026_02_20_000500_create_push_subscriptions_table.php`
- `backend/database/migrations/2026_02_20_000501_add_push_enabled_to_notification_preferences_table.php`

`push_subscriptions` fields:
- `user_id`
- `endpoint` (unique)
- `p256dh`
- `auth`
- `user_agent`
- `device_label`
- `is_enabled`

`notification_preferences` changes:
- Added `push_enabled` (bool, default `false`)

### 2.2 API endpoints
Added under `/api/v1`:
- `POST /push/subscribe`
- `POST /push/unsubscribe`
- `GET /push/subscriptions`

Implementation files:
- `backend/app/Http/Controllers/PushSubscriptionController.php`
- `backend/routes/api.php`
- rate limiter `push_subscriptions` in `backend/app/Providers/AppServiceProvider.php`

Behavior:
- `subscribe` upserts endpoint and sets `notification_preferences.push_enabled=true`.
- `unsubscribe` disables specific endpoint (`is_enabled=false`).
- If user has no active endpoints left, `push_enabled` is disabled.

### 2.3 Dispatch and queue delivery
Notification pipeline extension:
- Existing `backend/app/Jobs/DispatchNotificationJob.php` still creates in-app notification.
- If push criteria are met (`push_enabled=true` and active subscription exists), it enqueues:
  - `backend/app/Jobs/SendWebPushNotificationJob.php`

`SendWebPushNotificationJob` behavior:
- Uses `minishlink/web-push` with VAPID.
- Builds payload: `title`, `body`, `icon`, `badge`, `url`, `data`.
- Skips digest notification types.
- Auto-disables endpoints on `404/410` push responses.
- Logs `push_vapid_not_configured` and exits gracefully if VAPID keys are missing.

### 2.4 Config and env
Added:
- `backend/config/push.php`
- backend env keys:
  - `VAPID_PUBLIC_KEY`
  - `VAPID_PRIVATE_KEY`
  - `VAPID_SUBJECT`
  - `PUSH_NOTIFICATION_ICON`
  - `PUSH_NOTIFICATION_BADGE`

## 3) Frontend architecture updates

### 3.1 Service worker
New file:
- `frontend/public/sw.js`

Implemented:
- `push` event handler -> browser notification display
- `notificationclick` handler -> deep-link to SPA (`/notifications` fallback)

### 3.2 Push service and subscription flow
New file:
- `frontend/src/services/push.ts`

Capabilities:
- register service worker (`/sw.js`)
- subscribe/unsubscribe device endpoint
- map backend device entries to UI
- validate permission states and flow transitions

### 3.3 UI integration
Updated:
- `frontend/src/pages/SettingsNotifications.vue`
- `frontend/src/main.ts`
- `frontend/src/stores/notifications.ts`
- `frontend/src/stores/language.ts`

UI additions:
- permission state display
- enable/disable push controls
- active device list
- per-device disable action

### 3.4 Frontend env
- `VITE_ENABLE_WEB_PUSH`
- `VITE_VAPID_PUBLIC_KEY`

## 4) Production hardening (Docker/Nginx)

### 4.1 Explicit VAPID env mapping in production compose
Updated:
- `docker-compose.production.yml`

Added env mapping for `backend` and `queue`:
- `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT`, `PUSH_NOTIFICATION_ICON`, `PUSH_NOTIFICATION_BADGE`

Added env mapping for `frontend`:
- `VITE_ENABLE_WEB_PUSH`, `VITE_VAPID_PUBLIC_KEY`

Why this was required:
- Without explicit mapping, `config('push.vapid')` remained null in queue runtime.
- Result was silent push non-delivery.

### 4.2 Nginx DNS re-resolve fix
Updated:
- `ops/nginx-docker-production.conf`

Added:
- `resolver 127.0.0.11 ...`
- `proxy_pass` via upstream variables

Why this was required:
- After backend/frontend container restart, Nginx kept stale container IPs and returned `502 Bad Gateway`.

## 5) Tests and verification

### Backend automated tests
Added/updated:
- `backend/tests/Feature/PushSubscriptionsApiTest.php`
- `backend/tests/Feature/NotificationsApiTest.php`

Coverage includes:
- subscribe/unsubscribe/list APIs
- enqueue decision for `SendWebPushNotificationJob`

### Frontend verification
- `npm run test` passes
- `npm run build` passes

## 6) Operations runbook (for future maintainers)
Use alias:
```bash
DC="docker compose -p izdaji_prod --env-file .env.production.compose -f docker-compose.production.yml"
```

### 6.1 Deploy/refresh after env or config changes
```bash
$DC up -d --build --force-recreate backend queue scheduler frontend
$DC exec backend php artisan optimize:clear
$DC exec backend php artisan config:cache
$DC restart backend queue scheduler gateway
```

### 6.2 Quick push diagnostics
1. Confirm VAPID values are visible in queue runtime:
```bash
$DC exec queue php artisan tinker --execute="dump(config('push.vapid'));"
```

2. Confirm push job execution:
```bash
$DC logs --tail=200 queue
```

3. Confirm push warnings in logs:
```bash
$DC exec backend sh -lc "grep -R 'push_' storage/logs -n | tail -n 50"
```

4. Confirm subscription writes via API:
```bash
$DC logs --tail=200 gateway | grep '/api/v1/push/'
```

### 6.3 When user does not receive push
Check in this order:
- browser permission is `granted`
- subscription exists and `isEnabled=true`
- `notification_preferences.push_enabled=true`
- `config('push.vapid')` is not null
- no `push_vapid_not_configured` log entries
- Brave setting `Use Google services for push messaging` is enabled
- app is loaded over `https://` (`http://` is unsupported for push)
- on iPhone/iPad: app is installed to Home Screen (PWA), not opened in normal browser tab

### 6.4 Browser/device compatibility notes
- Android: validated across Chromium family (`Chrome`, `Brave`, `Edge`, `Samsung Internet`) when push services are not blocked.
- iPhone/iPad (iOS/iPadOS 16.4+): supported only for installed PWA from Home Screen.
- If an old subscription exists with a previous VAPID key, frontend now auto re-subscribes with the current key.

### 6.5 Tunnel returns 502
- Check gateway logs:
```bash
$DC logs --tail=200 gateway
```
- If logs show stale upstream IP `connection refused`, restart gateway:
```bash
$DC restart gateway
```

## 7) Key maintenance files
Backend:
- `backend/app/Jobs/DispatchNotificationJob.php`
- `backend/app/Jobs/SendWebPushNotificationJob.php`
- `backend/app/Http/Controllers/PushSubscriptionController.php`
- `backend/config/push.php`
- `backend/database/migrations/2026_02_20_000500_create_push_subscriptions_table.php`
- `backend/database/migrations/2026_02_20_000501_add_push_enabled_to_notification_preferences_table.php`

Frontend:
- `frontend/public/sw.js`
- `frontend/src/services/push.ts`
- `frontend/src/pages/SettingsNotifications.vue`

Infra/Ops:
- `docker-compose.production.yml`
- `ops/nginx-docker-production.conf`

## 8) Local env handling note
`frontend/.env` is local runtime-only and should not be committed.
It is ignored via root `.gitignore` as `frontend/.env`.

## 9) Stabilization update - 2026-02-23

### 9.1 Frontend push compatibility hardening
Updated:
- `frontend/src/services/push.ts`
- `frontend/src/pages/SettingsNotifications.vue`
- `frontend/src/stores/language.ts`

Implemented:
- granular push unavailability reasons (`disabled_by_config`, `insecure_context`, `ios_home_screen_required`, `missing_vapid_key`, browser API unsupported)
- explicit iOS/iPadOS handling: push available only for installed PWA
- stabilized service-worker registration using `navigator.serviceWorker.ready`
- automatic re-subscribe for stale subscriptions using old VAPID key
- clearer handling for `Registration failed - push service error`
- expanded browser labels (Brave, Samsung Internet, Opera, Edge, Firefox, Chrome, Safari)
- Settings -> Notifications now displays contextual hints instead of a single generic disabled message

### 9.2 Unnecessary `/auth/me` 401 noise removal
Updated:
- `frontend/src/stores/auth.ts`

Implemented:
- `initialize()` now skips `GET /api/v1/auth/me` when local auth state is already guest and no active auth context exists.
- Result: public routes no longer produce expected-but-noisy `401` entries.

### 9.3 Verification
- `cd frontend && npx vue-tsc -b` passes.
- `cd frontend && npm run build -- --outDir dist-check` passes.
