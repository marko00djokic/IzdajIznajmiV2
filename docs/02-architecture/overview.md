# Pregled arhitekture

## Komponente

```text
Browser
  └─ Vue 3 SPA (`frontend/`)
       ├─ mock adapter ili Axios real adapter
       ├─ Pinia state + Vue Router guards
       └─ Leaflet / Echo / service worker
              │ Sanctum cookie + JSON/multipart + WebSocket/polling
Laravel 12 (`backend/`)
  ├─ routes → middleware → Request → Controller → Service/Policy → Resource
  ├─ Eloquent + PostgreSQL/SQLite
  ├─ queue jobs + scheduler + Reverb
  └─ storage, Meilisearch, email, push, Stripe, Sentry
```

## Granice i entry points

- Frontend bootstrap: `frontend/src/main.ts`, `App.vue`, `router/index.ts`.
- API adapter: `frontend/src/services/index.ts`; izbor mock/real je
  `VITE_USE_MOCK_API`.
- Kanonske rute: `backend/routes/api.php`, prefiks `/api/v1`.
- Backend bootstrap/middleware/schedule: `backend/bootstrap/app.php`.
- Rate limit i policy registracija: `backend/app/Providers/AppServiceProvider.php`.
- Schema: `backend/database/migrations/`; seed: `database/seeders/`.
- Runtime topologija: `docker-compose.yml` i
  `docker-compose.production.yml`.

## Ključni tok zahteva

Real SPA prvo inicijalizuje Sanctum CSRF cookie, zatim šalje credential cookie
zahteve. Laravel primenjuje session activity, MFA/role middleware, Form Request
validaciju i Policy/Gate autorizaciju. Resource klase oblikuju response. Poslovi
koji ne treba da blokiraju HTTP odlaze u database queue.

## Asinhroni i periodični rad

Queue pokriva slike, chat attachment-e, search indeks, geokodiranje,
obaveštenja, web push i KYC skeniranje. Scheduler je definisan u
`backend/bootstrap/app.php`; kompletnu matricu vidi u
[development services](../03-development/services.md).

## Okruženja

- `docker-compose.yml`: podrazumevani lokalni razvojni stack.
- `docker-compose.production.yml`: production-like self-hosted stack sa Nginx
  gateway-em i opcionim Cloudflare Tunnel profilom.
- Native workflow postoji za ciljanu dijagnostiku, ali nije podrazumevani.

Bezbednosna arhitektura je izdvojena u
[security indeks](../04-features/security/README.md).
