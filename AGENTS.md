# IzdajIznajmiV2 — vodič za AI agente

## Svrha i izvor istine

`IzdajIznajmiV2` je marketplace za kratkoročno i srednjoročno izdavanje
smeštaja. Funkcionalan MVP je u fazi stabilizacije i pripreme za stvarnu
produkciju.

- Kod, migracije, konfiguracija i automatizovani testovi su izvor istine za
  implementirano ponašanje.
- `docs/` objašnjava sistem; ne sme da nadjača kod niti da duplira kanonski
  dokument.
- Za početnu navigaciju pročitaj samo ovaj fajl. Dodatne dokumente biraj iz
  tabele ispod.
- Centralna dokumentacija je na srpskom, latinicom; tehnički identifikatori
  ostaju na engleskom.

## Mapa repozitorijuma

| Putanja | Odgovornost |
| --- | --- |
| `frontend/` | Vue 3, Vite, TypeScript, Pinia, Router, Vitest i Playwright |
| `backend/` | Laravel 12 API, Sanctum, politike, poslovi, migracije i PHPUnit |
| `docs/` | Kanonska projektna, razvojna, feature, API, QA i ops dokumentacija |
| `ops/` | Deploy/rollback, Nginx, backup, supervisor i k6 skripte |
| `.ai/memory/` | Kratak status projekta za roadmap i nastavak ranijeg rada |
| `.github/workflows/` | CI i deploy workflow-i |
| `example/` | Samo read-only referenca; nije deo aplikacije niti se menja u projektnim zadacima |

Laravel nema aktivne root `app/` ili `database/` direktorijume; ceo backend je
u `backend/`.

## Routing po vrsti zadatka

| Zadatak | Prvo pročitaj | Glavni entry points | Provera |
| --- | --- | --- | --- |
| Product tok/uloge | `docs/01-project/` | frontend stranice, API rute | ciljane feature testove |
| Frontend feature/bug | `frontend/AGENTS.md`, feature doc | `frontend/src/pages`, `components`, `stores`, `services` | `npm run build`, `npm run test` |
| Backend/API feature/bug | `backend/AGENTS.md`, `docs/05-api/README.md` | `backend/routes/api.php`, kontroleri, modeli, politike | `php artisan test` ili ciljani test |
| API ugovor | `docs/05-api/contract.md` | `backend/routes/api.php`, Resources/Requests | `php artisan route:list --path=api` |
| Baza/modeli | `docs/02-architecture/data-model.md` | `backend/database/migrations`, modeli | migracije i feature testovi |
| Auth/security/KYC | `docs/04-features/security/`, `docs/04-features/kyc.md` | middleware, policies, security config | security/KYC testovi |
| Search/geolokacija | `docs/04-features/search.md` | listing store, SearchController, search config | search testovi |
| Chat/realtime | `docs/04-features/chat.md`, `docs/07-operations/chat-realtime.md` | chat store/page, ConversationController, events | ChatApi/Signals testovi |
| Docker/lokalni rad | `docs/03-development/quick-start.md` | compose fajlovi i env primeri | `docker compose config` |
| Deploy/incident | `docs/07-operations/` i `ops/AGENTS.md` | compose production, `ops/` | runbook verifikacija |
| Test/QA/UAT | `docs/06-testing/README.md` | `backend/tests`, `frontend/tests`, `frontend/e2e` | odgovarajuća command matrica |
| Roadmap/nastavak | `.ai/memory/project-status.md`, `docs/08-roadmap/` | povezani kod i issue kontekst | proveri status prema kodu |
| Dokumentacija | `docs/AGENTS.md` | `docs/README.md`, kanonski domenski doc | `php ops/check-docs-links.php` |

## Obavezni protokol pre izmene

1. Utvrdi traženi obim i pročitaj samo ciljane dokumente iz routing tabele.
2. Proveri `git status --short`; postojeće korisničke izmene čuvaj i ne
   prepisuj.
3. Pronađi postojeći tok, testove i source of truth pre kreiranja novog sloja.
4. Kod kontradikcije veruj kodu/testovima/config-u, a dokumentaciju ispravi u
   istom zadatku.
5. Napravi najmanju koherentnu izmenu i pokreni ciljane provere; puni suite
   biraj prema riziku.

## Serena i OpenSpec

Serena MCP je preporučeni alat za semantičku navigaciju kroz kod. Koristi ga
kada zadatak zahteva pronalaženje simbola i referenci, praćenje call-site-ova i
toka izvršavanja, razumevanje veze frontend/backend komponenti ili bezbedan
rename/refactor preko više fajlova. Posebno je koristan u nepoznatom delu
codebase-a i pre arhitektonske odluke. Za tačnu tekstualnu pretragu, listanje
fajlova ili malu dokumentacionu/config izmenu dovoljan je `rg` i direktno
čitanje fajla.

OpenSpec je zvanični CLI + Codex skill workflow (nije MCP server). Preporučuje
se pre implementacije nove ili nedovoljno precizne funkcionalnosti, breaking
izmene, većeg refaktora i promene koja zahvata više slojeva ili važan ugovor
(API, data model, auth/security, realtime/search ili deploy). Koristi
`$openspec-explore` kada prvo treba razjasniti problem i opcije, a
`$openspec-propose` kada zahtev treba pretvoriti u proposal, specifikacije,
design i taskove za pregled pre koda. Za mali lokalizovani bug, mehaničku
izmenu ili copy/docs-only korekciju ne pravi OpenSpec change osim ako korisnik
to izričito traži.

Kod većih promena prvo koristi Serenu da potvrdi postojeći tok, pa nalaze ugradi
u OpenSpec proposal. Setup i provera oba alata opisani su u
[`docs/03-development/ai-agent-tools.md`](docs/03-development/ai-agent-tools.md).

## Kritični gotchas

- Podrazumevani lokalni workflow je kompletan `docker-compose.yml` stack:
  Postgres, backend, queue, scheduler, Reverb, frontend i Meilisearch.
- Frontend bira servis pri build/start vremenu. `VITE_USE_MOCK_API=true` koristi
  mock podatke; `false` koristi Laravel i Sanctum cookie sesiju.
- Za real API podesi stateful domene/CORS, prvo pozovi
  `/sanctum/csrf-cookie` i šalji credential cookies.
- Kanonski API je `/api/v1`. Neversionisani `/api/*` postoji kao tranzicioni
  alias i ne treba ga uvoditi u nove klijente.
- Kanonski zahtev za smeštaj je `Application`. `BookingRequest` kod postoji,
  ali trenutno nema rute i predstavlja legacy ostatak.
- Queue je potreban za slike, obaveštenja, push i pojedine search/KYC poslove;
  scheduler pokreće expiry, digeste, saved-search matcher i retention poslove.
- Reverb radi u compose stacku, dok chat i notification UI i dalje koriste i
  polling kao fallback. Ne pretpostavljaj isključivo WebSocket ponašanje.
- Meilisearch je uključen u Docker workflow. `VITE_SEARCH_V2=true` bira V2 UI;
  map mode koristi legacy/geospatial listing pretragu.
- Favoriti su browser-local (`ii-favorites`); real API nema favorites endpoint.
- `/map` je vizuelni showcase, dok `/search` sadrži stvarnu Leaflet/OSM mapu.
- `docker-compose.production.yml` je production-like self-hosted okruženje sa
  testnim podacima, ne potvrđena javna produkcija.
- Tajne i stvarne `.env` vrednosti nikada ne upisuj u dokumentaciju.

## Komande

```bash
# Podrazumevani razvoj
docker compose up -d --build
docker compose exec backend php artisan migrate --seed

# Backend
cd backend
php artisan test
php artisan route:list --path=api
./vendor/bin/pint --test

# Frontend
cd frontend
npm run build
npm run test
npm run test:e2e

# Dokumentacija i compose validacija
php ops/check-docs-links.php
docker compose config --quiet
docker compose -f docker-compose.production.yml config --quiet
```

Ako instalirane zavisnosti nedostaju, prvo koristi `composer install` odnosno
`npm ci`. Ne menjaj lock fajlove bez potrebe zadatka.

## Stil i testovi

- Frontend: 2 razmaka, PascalCase komponente, camelCase store/service moduli,
  kebab-case rute i dokumenti.
- Backend: PSR-12, 4 razmaka, jasni Request/Resource/Policy slojevi i `/api/v1`
  ugovor.
- Ciljane testove pokreni uvek kada je moguće. Build/type-check je obavezan za
  TypeScript izmene; puni suite je obavezan za široke ili rizične izmene.
- Ne popravljaj nepovezane padove bez saglasnosti; jasno ih prijavi.

## Održavanje dokumentacije

- Prvo pronađi kanonski dokument u
  `docs/02-architecture/source-of-truth.md`; ažuriraj njega i iz drugih mesta
  samo linkuj.
- Ažuriraj dokumentaciju kada se menja ponašanje, ugovor, arhitektura, setup,
  operacija ili korisnički tok.
- Changelog/release belešku dodaj samo za promenu ponašanja proizvoda,
  arhitekture ili operacija.
- `.ai/memory/project-status.md` menjaj samo kada se promeni praćeni status ili
  roadmap stavka.
- Za značajnu, teško reverzibilnu odluku dodaj kratak ADR u
  `docs/02-architecture/decisions/`.
- Zastareo dokument odmah ispravi, označi `stale` ili premesti u
  `docs/09-archive/`; planirano nikad ne predstavljaj kao implementirano.
- Feature i runbook dokumenti prate šablone u
  `docs/03-development/templates/`.

## Git i handoff

- Ne briši niti resetuj tuđe izmene. Ne koristi destruktivne Git komande bez
  eksplicitnog zahteva.
- Commit poruka treba da bude imperativna i da navede oblast.
- U završnom izveštaju navedi rezultat, relevantne fajlove, provere i poznate
  rizike; ne tvrdi da je provera prošla ako nije pokrenuta.
