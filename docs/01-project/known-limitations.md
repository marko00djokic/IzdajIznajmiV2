# Poznata ograničenja

> Status: aktivan dokument (`partial` stavke)
> Poslednja ciljana provera: 2026-07-15
> Source of truth: navedeni kod, config i testovi

| Oblast | Ograničenje | Source of truth / sledeći korak |
| --- | --- | --- |
| Produkcija | Postoje dev i production-like Compose stack sa testnim podacima; nema potvrđene poslovne produkcije. | `docker-compose*.yml`, [roadmap](../08-roadmap/README.md) |
| API verzije | `/api/v1/*` i neversionisani `/api/*` trenutno rade paralelno. | `backend/routes/api.php`; ukloniti alias tek uz migraciju klijenata |
| Prijave | `Application` je aktivan tok; `BookingRequest` model/controller/seeder nemaju rute. | `backend/routes/api.php`, `ApplicationController.php` |
| Favoriti | Čuvaju se u browser `localStorage`; real API `getFavorites()` vraća praznu listu. | `frontend/src/stores/listings.ts`, `realApi.ts` |
| Rezervacije | Legacy `getBookings()` u real adapteru vraća praznu listu; aktivan UI koristi applications/viewings tabove. | `frontend/src/services/realApi.ts`, `Bookings.vue` |
| Mapa | `/map` je vizuelni showcase; stvarna Leaflet/OSM mapa je u search toku. | `frontend/src/pages/Map.vue`, `components/search/MapExplorer.vue` |
| Realtime | Reverb je integrisan, ali chat/notification tokovi zadržavaju polling i backoff fallback. | `frontend/src/pages/Chat.vue`, `NotificationBell.vue` |
| Spoljne usluge | Stripe, email, push, Sentry, Meilisearch i tunnel zahtevaju env/config i nisu garantovani u svakom režimu. | `.env` primeri i `backend/config/` |
| UI reference | Slike iz januara 2026. su istorijski dizajn, ne pixel-perfect izvor trenutnog UI-ja. | `docs/04-features/ui-reference/` |
| Licenca | Repozitorijum još nema definisanu licencu. | root `README.md` |

Nove praznine dodaj ovde i u relevantan feature dokument. Kada se stavka reši,
ukloni je ili zabeleži promenu u release/ADR dokumentu ako ima trajnu vrednost.
