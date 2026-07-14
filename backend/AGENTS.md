# Backend lokalna pravila

Važe i pravila iz root `AGENTS.md`.

- Stack: Laravel 12, PHP 8.2+, Sanctum, Eloquent, database queue i Reverb.
- PSR-12 i 4 razmaka; koristi Form Request za validaciju, Resource za response i
  Policy/Gate za resource autorizaciju.
- Novi javni ugovor pripada `/api/v1`; ne širi neversionisani alias.
- Schema se menja novom migracijom. Ne menjaj postojeću istorijsku migraciju
  kada je promena namenjena već migriranim okruženjima.
- Osetljive podatke ne loguj; koristi postojeći structured/security logging i
  PII sanitization sloj.
- Async rad mora imati jasnu queue posledicu i operativnu proveru. Periodični
  rad registruje se u `bootstrap/app.php` i dokumentuje u services/runbook doc.
- Feature testovi su podrazumevani za API/policy tok; unit test koristi za
  izolovanu domensku logiku.

Minimalna provera:

```bash
php artisan test --filter=RelevantTest
./vendor/bin/pint --test
```

Za širu izmenu pokreni `php artisan test`. API i data source of truth su u
`routes/api.php`, Form Requests, Resources, migracijama i testovima.
