# Trenutni status projekta

- Status: `active`
- Poslednja provera: 2026-07-15
- Faza: funkcionalan MVP → stabilizacija → stvarna produkcija

## Sažetak

Glavni marketplace, security, KYC, chat, notifications, search, viewings,
recommendations i transaction domeni imaju implementiran kod i test coverage.
Podrazumevani development workflow je kompletan Docker Compose stack.

Postoje development i production-like self-hosted okruženje; oba koriste testne
podatke. Javno hostovanje sa lokalnog računara nije tretirano kao potvrđena
poslovna produkcija.

## Aktivni fokus

- ukloniti ili formalno migrirati legacy `BookingRequest` i neversionisane API
  alias rute;
- zatvoriti V1→V2 parity uz eksplicitnu owner potvrdu;
- produkcioni hosting, tajne, backup/restore drill i observability;
- rešavati partial stavke iz `docs/01-project/known-limitations.md`.

Detalji i status planiranog su isključivo u `docs/08-roadmap/`.
