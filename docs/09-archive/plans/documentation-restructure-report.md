# Izveštaj o restrukturiranju dokumentacije

- Status: `historical` zapis migracije
- Datum: 2026-07-15
- Odluka: [ADR 0001](../../02-architecture/decisions/0001-numbered-documentation-architecture.md)

## Rezultat

Dokumentacija je podeljena u numerisane domene `01-project` do `09-archive`.
Root `AGENTS.md` je agent routing, root `README.md` portfolio, a `docs/README.md`
onboarding i navigacija. Projekat ima vendor-neutral memoriju u `.ai/memory/`,
lokalna AGENTS pravila, zajedničke šablone i proveru internih Markdown linkova.

## Glavne migracije

| Staro | Novo / odluka |
| --- | --- |
| `docs/full-docs.md` | podeljeno na `01-project/product-overview.md`, `roles-and-glossary.md`, `user-journeys.md`; original arhiviran |
| `docs/dev-setup.md` | `03-development/quick-start.md` + `native-setup.md` |
| `docs/docker-manual-sr.md` | `03-development/docker-workflow.md` |
| `docs/api-contract.md`, `api-examples.md` | `05-api/contract.md`, `examples.md` |
| `docs/security/*` | `04-features/security/*`; UAT/E2E planovi u `06-testing/security/` |
| `docs/ops/*`, `docs/deploy/*` | `07-operations/*` |
| `docs/releases/*` | `09-archive/releases/release-notes/*` |
| `docs/parity/*` | `08-roadmap/v1-v2-parity.md` |
| `docs/ui-reference/*` | smislena imena i indeks u `04-features/ui-reference/` |
| root questionnaire | arhiviran u `09-archive/plans/` bez gubitka odgovora |

## Ispravljene kontradikcije

- Laravel nema root legacy `app/`/`database/`; aktivan je samo `backend/`.
- Okruženje je production-like sa testnim podacima, ne potvrđena produkcija.
- `Application` je kanonski aktivni tok; `BookingRequest` nema aktivne rute.
- `/api/v1` je kanonski; neversionisani API je tranzicioni alias.
- `/search` ima Leaflet mapu, dok je zasebni `/map` showcase.
- demo nalozi su usklađeni sa aktuelnim seederom.

## Kompatibilnost

Često korišćene stare putanje imaju kratke migration stubove. Interni linkovi se
proveravaju sa `php ops/check-docs-links.php` i CI workflow-om.
