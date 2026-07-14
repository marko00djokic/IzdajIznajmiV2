# Pravila za dokumentaciju

Važe i root `AGENTS.md` pravila.

- Piši na srpskom, latinicom; kod, rute, env i tehnički identifikatori ostaju
  na engleskom.
- Fajlovi/direktorijumi su lowercase kebab-case, osim standardnih `README.md`,
  `AGENTS.md` i `CHANGELOG.md`.
- Pronađi source of truth pre izmene. Ne pravi novi zbirni dokument ako tema
  već ima kanonsku lokaciju.
- `01`–`08` sadrže aktivnu dokumentaciju; jednokratni planovi i istorijski
  snapshot-i pripadaju `09-archive`.
- Feature i runbook koriste šablone u `03-development/templates/`.
- `planned`, `partial` i `stale` moraju biti eksplicitni. Ne predstavljaj
  roadmap kao implementirano.
- Dokumenti skloni zastarevanju imaju status, datum ciljane provere i source of
  truth. Ne upisuj tajne ni stvarne `.env` vrednosti.
- Posle premeštanja popravi sve interne linkove i pokreni:

```bash
php ops/check-docs-links.php
```
