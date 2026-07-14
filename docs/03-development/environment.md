# Environment promenljive

Javni nazivi i svrha se dokumentuju ovde; stvarne vrednosti i tajne nikad.
Kanonski inventar/defaults su `.env` primeri, Compose fajlovi i `backend/config`.

| Grupa | Ključni nazivi | Svrha |
| --- | --- | --- |
| App URL | `APP_URL`, `FRONTEND_URL`, `FRONTEND_URLS` | URL generisanje i CORS |
| Sanctum/session | `SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN`, `SESSION_SECURE_COOKIE`, `SESSION_SAME_SITE` | cookie auth |
| Database/queue | `DB_*`, `QUEUE_CONNECTION` | Eloquent i async jobs |
| Frontend mode | `VITE_API_BASE_URL`, `VITE_USE_MOCK_API`, `VITE_SEARCH_V2` | adapter i search UI |
| Search | `SEARCH_DRIVER`, `MEILISEARCH_HOST`, `MEILISEARCH_KEY`, `MEILISEARCH_INDEX` | Search V2 |
| Reverb | `REVERB_*`, `VITE_REVERB_*`, `REVERB_ALLOWED_ORIGINS` | WebSocket server/client |
| Push | `VAPID_*`, `VITE_ENABLE_WEB_PUSH`, `VITE_VAPID_PUBLIC_KEY` | web push |
| Slike | `IMAGE_OPTIMIZE`, `IMAGE_MAX_WIDTH`, `IMAGE_WEBP_QUALITY` | image queue obrada |
| Security | `REQUIRE_MFA_FOR_ADMINS`, `SECURITY_*`, `SENTRY_*` | MFA, headers, monitoring |
| Stripe | Stripe/service config env nazivi iz `config/services.php` i `config/transactions.php` | depozit/webhook |

Primeri:

- `backend/.env.example` — lokalni backend;
- `backend/.env.example.staging` i `.env.example.production` — deployment
  checklist osnova;
- `frontend/.env.example` — Vite build-time vrednosti;
- `.env.production.compose.example` — production-like Compose.

Promena `VITE_*` zahteva rebuild/restart frontend build-a. Posle promene Laravel
config vrednosti očisti/cache-uj config prema okruženju; ne pretpostavljaj da je
stari cache osvežen.
