# Strategija testiranja

> Status: `active`
> Poslednja ciljana provera: 2026-09-03
> Source of truth: package scripts, PHPUnit/Vitest/Playwright config i test kod

## Slojevi

- Backend feature testovi proveravaju API, policy i domenske tokove; unit testovi
  izolovanu logiku.
- Frontend Vitest proverava store/util/component logiku; `npm run build` radi
  TypeScript proveru i Vite build.
- Playwright smoke pokriva browser tok u mock režimu.
- Manual/UAT planovi dopunjuju automatizaciju za vizuelne i prihvatne scenarije.
- k6 skripte služe za kontrolisan load baseline, ne kao CI unit gate.

## Command matrica

| Obim | Komanda |
| --- | --- |
| svi backend testovi | `cd backend && php artisan test` |
| ciljani backend test | `cd backend && php artisan test --filter=TestName` |
| PHP stil | `cd backend && ./vendor/bin/pint --test` |
| frontend unit | `cd frontend && npm run test` |
| TypeScript/build | `cd frontend && npm run build` |
| browser smoke | `cd frontend && npm run test:e2e` |
| docs linkovi | `php ops/check-docs-links.php` |
| Compose schema | `docker compose config --quiet` |

Ciljane testove pokreni uvek kada je moguće. Puni suite bira se prema riziku i
obimu, a široka cross-stack promena zahteva backend + frontend build/test i
relevantan browser/manual scenario.

## CI okidanje i dependency audit

Backend, frontend i E2E workflow-i ostaju obavezne regresione provere na svakom
pull request-u, a mogu i ručno da se pokrenu preko `workflow_dispatch`.

Provera ranjivosti nije deo test job-a. Kanonski `Dependency Audit` workflow
proverava zaključane produkcione Composer i npm zavisnosti:

- na svakoj izmeni manifest/lock fajlova;
- jednom nedeljno, kako bi nove bezbednosne objave bile otkrivene i bez izmene
  repozitorijuma;
- ručno preko `workflow_dispatch`, uključujući proveru izmena samog audit
  workflow-a.

Na ovaj način neuspeh dependency audita ostaje vidljiv kao bezbednosni signal,
ali se više ne predstavlja kao pad PHPUnit/Vitest provere na nepovezanom PR-u.

## Aktivni planovi

- [Engineering manual test plan](manual-test-plan.md)
- [UAT plan](uat-test-plan.md)
- [File authorization UAT](security/file-authorization-uat.md)
- [Monitoring security E2E plan](security/monitoring-e2e-plan.md)
- [Dependency hygiene E2E plan](security/dependency-hygiene-e2e-plan.md)

Kada se korisnički tok promeni, ažuriraj automatizaciju i manual/UAT plan koji
QA koristi.
