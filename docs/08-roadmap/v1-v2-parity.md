# V1 → V2 parity checklist

> Status: `partial`; istorijski migracioni kontekst još nije formalno zatvoren
> Poslednja ciljana provera: 2026-07-15
> Source of truth: aktuelni kod, testovi i owner acceptance

Većina nekadašnjih parity oblasti je implementirana: Sanctum auth, role/policies,
listing lifecycle i filteri, `Application`, listing-scoped chat, ocene, demo
seed, admin KPI/moderacija/impersonation i osnovni frontend tokovi.

## Otvoreno za formalno zatvaranje

- [ ] Owner potvrđuje da je V1→V2 scope i dalje relevantan i kompletan.
- [ ] Legacy `BookingRequest` se uklanja ili dobija eksplicitnu migracionu odluku.
- [ ] Neversionisane `/api/*` alias rute dobijaju deprecation i removal plan.
- [ ] Real API E2E potvrđuje auth → listing → application → chat kritični tok.
- [ ] Seed/demo credentials i acceptance scenariji potvrđeni su na čistoj bazi.
- [ ] Known limitations su prihvaćene kao post-parity roadmap ili rešene.

Po zatvaranju promeni status u `completed`, zabeleži owner/date i premesti ovaj
dokument u `09-archive/plans/`.
