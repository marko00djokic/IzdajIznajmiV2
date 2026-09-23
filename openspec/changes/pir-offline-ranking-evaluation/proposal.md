# Proposal

## Why

PIR 0.1 opisuje hibridno rangiranje, ali nema zamrznut dataset, izvršiv adapter ni uporedive rank liste. Verziji 0.2 potreban je ponovljiv tehnički eksperiment pre nezavisnih relevance ocena i bilo kakvog tumačenja H1.

## What Changes

- Dodaje se sintetički, verzionisan dataset od 24–36 oglasa sa poreklom, pravilima za missing vrednosti i SHA-256 kontrolom, kao i zaključani scenariji S1/S2 sa 12–18 kandidata posle hard filtera.
- Dodaje se offline Laravel CLI adapter za hard filtere, eksplicitne i ponašajne težine, normalizovanu ponderisanu sumu, doprinose kriterijuma i deterministički redosled. Primarni `λ=0,25` i analiza osetljivosti `{0; 0,25; 0,50}` zaključani su pre merenja.
- Na istim filtriranim kandidatima beleže se bazni redosled, postojeća fiksna heuristika kao score-only komparator i novi hibrid; izlaz sadrži verzije, checksum, parametre i potpune rank liste.
- Postojeći scorer se izdvaja samo koliko je potrebno za offline pozivanje, uz parity testove koji čuvaju sadašnji API rezultat.
- Dodaju se automatizovane provere filtera, validacije, missing podataka, doprinosa, tie-break pravila, determinističnosti i ponovljivosti izlaza; PIR prilozi beleže stvarni status i komande.

## Capabilities

### New Capabilities

- `pir-offline-ranking-evaluation`: kontrolisano, ponovljivo offline poređenje rangiranja nad zamrznutim istraživačkim podacima.

### Modified Capabilities

Nema. Produkcioni API ugovor i ponašanje postojećih preporuka ostaju isti.

## Impact

Korisnik aplikacije ne vidi promenu u ovom koraku. Rad zahvata Laravel servis/CLI i ciljane testove, kao i `docs/01-project/pir/research/`; nema nove javne rute, migracije, korisničkog UI-a, scraping-a niti rada sa učesnicima. Dobijene rank liste nisu potvrda H1 bez nezavisnih ocena. Baseline od 23. septembra 2026. potvrđuje 12 postojećih testova i 40 asercija na PHP/SQLite okruženju, ali ne potvrđuje novi adapter.
