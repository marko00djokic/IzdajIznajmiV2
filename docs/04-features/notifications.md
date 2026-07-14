# Obaveštenja i web push

> Status: `implemented`; web push je env/browser-zavisan
> Poslednja ciljana provera: 2026-07-15
> Source of truth: notification kontroleri/services/jobs, push config i testovi

In-app notifications podržavaju listu, unread count, pojedinačno i grupno
označavanje pročitanog. Preference određuju channel/frequency/push ponašanje;
scheduler šalje daily/weekly digeste. Web push koristi VAPID i service worker,
a slanje ide kroz queue.

Frontend notification bell polling radi na 15 sekundi samo dok je dokument
vidljiv. Reverb događaji mogu ubrzati prikaz, ali polling ostaje fallback.

| Sloj | Putanje |
| --- | --- |
| Frontend | NotificationBell, Notifications, SettingsNotifications, push service, `public/sw.js` |
| Backend | Notification/Preference/Push kontroleri, NotificationService, Dispatch/Push jobs |
| Podaci | notifications, notification_preferences, push_subscriptions |
| Testovi | NotificationsApi, NotificationDigest, PushSubscriptions testovi |

Za produkciju su potrebni `VAPID_*`, odgovarajuće `VITE_*` build vrednosti,
HTTPS i aktivan queue. Istorijski rollout je u
[release beleškama](../09-archive/releases/release-notes/notifications/web-push-pwa-rollout-2026-02-20.md).
