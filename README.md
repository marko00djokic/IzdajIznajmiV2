# IzdajIznajmiV2

[![Backend CI](https://github.com/kameleon1808/IzdajIznajmiV2/actions/workflows/backend.yml/badge.svg)](https://github.com/kameleon1808/IzdajIznajmiV2/actions/workflows/backend.yml)

Marketplace za kratkoročno i srednjoročno izdavanje smeštaja, izgrađen kao
Vue 3 SPA sa Laravel 12 API-jem. Projekat demonstrira kompletan tok od
otkrivanja oglasa i prijave za smeštaj do komunikacije, ugovora, verifikacije i
administrativne kontrole.

Status projekta: funkcionalan MVP u fazi stabilizacije i pripreme za stvarnu
produkciju. Repozitorijum sadrži razvojni i production-like Docker stack;
trenutno javno hostovanje koristi testne podatke i ne predstavlja potvrđenu
poslovnu produkciju.

## Šta projekat demonstrira

- pretragu oglasa, geolokaciju, Leaflet/OSM mapu i Meilisearch opciju;
- uloge `seeker`, `landlord` i `admin` sa Laravel policy autorizacijom;
- prijave za smeštaj, termine obilaska i listing-scoped komunikaciju;
- ugovore, potpise, depozitni tok i Stripe integracionu tačku;
- KYC dokumente, MFA, sesije, audit, fraud signale i moderaciju;
- asinhronu obradu slika, queue/scheduler procese, Reverb i web push;
- mock i real API režime za brzo UI testiranje i punu integraciju;
- Docker, CI, deploy/rollback, backup i load-test operativne alate.

## Tehnologije

| Sloj | Tehnologije |
| --- | --- |
| Frontend | Vue 3, TypeScript, Vite, Pinia, Vue Router, Tailwind CSS |
| Backend | Laravel 12, PHP 8.2+, Sanctum, Reverb, Spatie Permission |
| Podaci | PostgreSQL u Dockeru; SQLite podrška za jednostavan native/test rad |
| Search/media | Meilisearch, Intervention Image, queue jobs |
| Testovi | PHPUnit 11, Vitest, Playwright, k6 |
| Operacije | Docker Compose, Nginx, Cloudflare Tunnel, shell runbook skripte |

## Arhitektura ukratko

```text
Vue SPA ── Sanctum/cookie + /api/v1 ── Laravel API ── PostgreSQL
   │                                      │
   ├── mock ili real service adapter      ├── queue / scheduler / Reverb
   └── Leaflet + optional Meilisearch     └── private/public storage
```

Repo je monorepo:

```text
frontend/   Vue aplikacija, unit i browser testovi
backend/    Laravel API, migracije, politike i testovi
docs/       projektna i tehnička dokumentacija
ops/        deploy, rollback, backup, Nginx i load-test alati
```

Detaljna mapa sistema je u
[pregledu arhitekture](docs/02-architecture/overview.md).

## Brzi početak — Docker

Preduslovi su Docker Engine i Compose plugin.

```bash
docker compose up -d --build
docker compose exec backend php artisan migrate --seed
docker compose exec backend php artisan storage:link
```

- aplikacija: `http://localhost:5173`
- API: `http://localhost:8000/api/v1`
- Meilisearch: `http://localhost:7700`
- Reverb: `ws://localhost:8080`

Stack već pokreće backend, frontend, Postgres, queue, scheduler, Reverb i
Meilisearch. Kompletan setup, prvi pristup i troubleshooting su u
[quick start vodiču](docs/03-development/quick-start.md).

## Demo nalozi

Seeder kreira lozinku `password` za sve demo naloge. Stabilne početne adrese
su:

- administrator: `admin@gmail.com`
- izdavalac: `stanodavac1@gmail.com`
- tražilac smeštaja: `trazilac1@gmail.com`

Seeder dodatno pravi numerisane naloge `stanodavac2..10@gmail.com` i
`trazilac2..10@gmail.com`.

## Razvoj i testiranje

```bash
# Backend
cd backend
php artisan test

# Frontend
cd frontend
npm run build
npm run test
npm run test:e2e
```

Frontend koristi `VITE_USE_MOCK_API=true` za lokalni mock ili `false` za
Laravel API. Real režim koristi Sanctum cookie sesije i zahteva usklađene CORS,
`SANCTUM_STATEFUL_DOMAINS` i URL vrednosti.

Potpuna command matrica je u
[strategiji testiranja](docs/06-testing/README.md), a env promenljive su
objašnjene u [environment vodiču](docs/03-development/environment.md).

## Dokumentacija

- [Onboarding i mapa dokumentacije](docs/README.md)
- [Proizvod, uloge i korisnički tokovi](docs/01-project/README.md)
- [Arhitektura i code map](docs/02-architecture/README.md)
- [Lokalni razvoj](docs/03-development/README.md)
- [Feature dokumentacija](docs/04-features/README.md)
- [API ugovor i primeri](docs/05-api/README.md)
- [Testiranje i UAT](docs/06-testing/README.md)
- [Operacije i deploy](docs/07-operations/README.md)
- [Roadmap i parity](docs/08-roadmap/README.md)

## Produkcioni status

`docker-compose.production.yml`, Nginx gateway i opcioni Cloudflare Tunnel
omogućavaju production-like hostovanje sa lokalnog računara. To okruženje je
korisno za demonstraciju i hardening, ali trenutno koristi testne podatke.
Pre stvarne produkcije treba završiti stavke iz
[roadmapa](docs/08-roadmap/README.md), posebno upravljanje tajnama, pouzdan
hosting, backup drill i observability.

## Kontakt

Projekat je portfolio demonstracija dostupna za saradnju i dalji razvoj.

Licenca: još nije definisana.
