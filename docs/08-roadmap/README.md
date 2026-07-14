# Roadmap

> Status: `planned`, osim eksplicitno označenih stavki
> Poslednja provera: 2026-07-15
> Vlasnik: owner projekta

Ovaj direktorijum je jedino mesto na kome planirano sme da izgleda kao plan.
Feature dokumenti linkuju ovde, ali ne predstavljaju stavke kao implementirane.

## Stabilizacija

- formalno odlučiti i ukloniti/migrirati legacy `BookingRequest` tok;
- ukinuti neversionisane `/api/*` alias rute uz klijentsku migraciju;
- zatvoriti [V1→V2 parity checklist](v1-v2-parity.md) owner potvrdom;
- proširiti real-API browser coverage za kritične tokove;
- rešiti aktivne [known limitations](../01-project/known-limitations.md).

## Stvarna produkcija

- pouzdan udaljeni hosting umesto laptop/self-hosted demonstracije;
- centralno upravljanje tajnama i strogi production env review;
- objekat storage/CDN strategija za medije i private dokumente;
- redovni backup/restore drill i definisani RPO/RTO;
- centralizovani logs/metrics/traces, alerting i on-call escalation;
- TLS/DNS/security-header i data-retention acceptance pre realnih podataka.

## Product nastavak

- server-side favoriti i migracija browser-local podataka;
- konsolidovan reservation/application naziv i UX;
- dalji payment/payout hardening i pravna validacija ugovora;
- uklanjanje showcase `/map` duplikata ili povezivanje sa stvarnom search mapom.

Promena statusa roadmap stavke mora ažurirati ovaj dokument,
`.ai/memory/project-status.md` i odgovarajući feature/known-limitations dokument.
