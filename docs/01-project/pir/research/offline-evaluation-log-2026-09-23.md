# Ručna provera offline rangiranja — 23. septembar 2026.

**Status:** tehnički rezultat, bez nezavisnih ocena relevantnosti i bez zaključka
o H1. **Presek koda:** `1f39d57315bb674b68d9fab7f07c411f1695a054`.
Potpune rang liste su u [`offline-evaluation-v1.json`](offline-evaluation-v1.json)
(SHA-256 `53a145197f6dfaf5c91fa45187a3400b6cdd93f4d254f90790bb338d58b3b92f`).

Iz `backend/` je komanda `php artisan pir:evaluate` dva puta pokrenuta sa
`--dataset=../docs/01-project/pir/research/dataset-v1.json`,
`--scenarios=../docs/01-project/pir/research/scenarios-v1.json`,
`--manifest=../docs/01-project/pir/research/manifest-v1.json` i zasebnim
`--output=/tmp/pir-evaluation-run-{1,2}.json`. Oba pokretanja su završila
uspešno; prvi izlaz je sačuvan kao gore navedeni artefakt. `run_metadata`
sadrži vreme i trajanje i zato se razlikuje. Sva ostala JSON polja su jednaka.

SHA-256 ulaza odgovara manifestu: dataset
`9e3ee95b846c81450a157b8386523f1dd733d0ff497f506a89eca9234c62495a`,
scenariji `7e8b5bb57393d12bcb8e99d3ea9681a9feffe8fa1b994c41a68ff7ca62a9fee9`.
Dataset ima 30 sintetičkih oglasa, od kojih pet navodi missing meka polja.

| Scenario | Kandidati | Hard-filter prekršaji | Isti kodovi u sva tri reda | Prvih pet: bazni / heuristika / hibrid |
| --- | ---: | ---: | --- | --- |
| S1 | 16 | 0 | da | `001, 002, 003, 004, 005` / `007, 010, 004, 011, 005` / `004, 007, 010, 001, 002` |
| S2 | 13 | 0 | da | `002, 003, 004, 006, 007` / `007, 010, 011, 004, 006` / `004, 007, 016, 010, 011` |

Kodovi u tabeli imaju prefiks `PIR-`. Provera prekršaja ponovo je izračunala
grad, status, budžet i obavezne pogodnosti iz izvornog dataset-a i uporedila
skup sa svim trima rank listama. Bazni redosled je **offline proxy** za
`created_at DESC` pretragu na osnovu `published_at`; heuristika je **score-only
kontrola** bez produkcionog prikupljanja kandidata i source bonusa. Promena
redosleda ovde ne dokazuje bolju relevantnost.

U istom okruženju `PHP_INI_SCAN_DIR=:/tmp/pir-php-ext/conf.d php artisan test`
u `backend/` dao je **153 passed (608 assertions)**; ciljane četiri API/search
klase dale su **12 passed (40 assertions)**. To su postojeći testovi: u
`backend/tests` nema PIR evaluator/parity/CLI testova. `./vendor/bin/pint --test`
vratio je **1 style issue**, `backend/bootstrap/app.php` (`ordered_imports`).
Ova provera ne obuhvata PostgreSQL, Meilisearch, korisnički UI, latenciju,
pilot niti H1–H3.
