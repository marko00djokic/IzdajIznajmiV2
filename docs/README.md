# Dokumentacija projekta IzdajIznajmiV2

Ovo je početna tačka i za novog developera i za tehničku procenu projekta.
Kod i automatizovani testovi su izvor istine; dokumentacija daje navigaciju,
namenu i operativni kontekst.

## Onboarding za 2–5 minuta

1. Pročitaj [pregled proizvoda](01-project/product-overview.md).
2. Otvori [pregled arhitekture](02-architecture/overview.md).
3. Pokreni projekat kroz [Docker quick start](03-development/quick-start.md).

Za AI agenta root [`AGENTS.md`](../AGENTS.md) je jedini obavezni početni
dokument; on dalje rutira prema vrsti zadatka.

## Mapa po oblasti

| Oblast | Šta sadrži | Početni dokument |
| --- | --- | --- |
| `01-project` | proizvod, uloge, glossary, journey-i, ograničenja | [Project indeks](01-project/README.md) |
| `02-architecture` | komponente, data model, code map, ADR i izvori istine | [Architecture indeks](02-architecture/README.md) |
| `03-development` | quick start, Docker/native rad, servisi, env, doc pravila | [Development indeks](03-development/README.md) |
| `04-features` | aktivni domenski i security dokumenti | [Feature indeks](04-features/README.md) |
| `05-api` | overview, kanonski ugovor i cURL primeri | [API indeks](05-api/README.md) |
| `06-testing` | strategija, komande, manual i UAT planovi | [Testing indeks](06-testing/README.md) |
| `07-operations` | deployment, backup, queue, realtime, performance | [Operations indeks](07-operations/README.md) |
| `08-roadmap` | planirano i nezatvoren V1→V2 parity | [Roadmap](08-roadmap/README.md) |
| `09-archive` | istorijski planovi, release notes i zamenjeni dokumenti | [Archive indeks](09-archive/README.md) |

## Mapa po zadatku

| Želim da… | Otvori |
| --- | --- |
| razumem korisnike i poslovne tokove | [uloge i glossary](01-project/roles-and-glossary.md), [journey-i](01-project/user-journeys.md) |
| pronađem kod za feature | [code map](02-architecture/code-map.md), zatim odgovarajući [feature doc](04-features/README.md) |
| vidim šta nije završeno | [known limitations](01-project/known-limitations.md) i [roadmap](08-roadmap/README.md) |
| pokrenem lokalni stack | [quick start](03-development/quick-start.md) |
| proverim API | [contract](05-api/contract.md) i [examples](05-api/examples.md) |
| pokrenem testove ili UAT | [test strategy](06-testing/README.md) |
| deployujem ili rešim incident | [operations](07-operations/README.md) |
| proverim autoritativni izvor | [source-of-truth matrica](02-architecture/source-of-truth.md) |

## Status oznake

- `implemented` — potvrđeno u aktivnom kodu/testovima;
- `partial` — deo toka postoji, ali ima eksplicitno ograničenje;
- `planned` — nije implementirano i pripada roadmapu;
- `historical` — zapis prošlog stanja, ne operativno uputstvo;
- `stale` — zahteva proveru pre korišćenja.

Dokumenti skloni zastarevanju imaju metadata blok sa statusom, source of truth
i datumom poslednje ciljane provere.

## Pravilo održavanja

Ne kreiraj novi zbirni dokument ako tema već ima kanonski izvor. Ažuriraj
source of truth i sa drugih mesta samo linkuj. Izmena koda mora ažurirati
povezani dokument kada menja ponašanje, API ugovor, arhitekturu, setup,
operacije ili korisnički tok.
