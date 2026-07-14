# IzdajIznajmiV2 backend

Laravel 12 API sa Sanctum SPA sesijama, policy autorizacijom, database queue-om,
schedulerom, Reverb-om, Meilisearch integracijom i PHPUnit testovima.

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve --port=8000
```

Za pun native tok u dodatnim terminalima pokreni `queue:work`, `schedule:work`
i po potrebi `reverb:start`. Podrazumevani onboarding je ipak
[Docker quick start](../docs/03-development/quick-start.md).

Kanonski ugovor je `/api/v1`; aktuelnu listu dobijaš sa:

```bash
php artisan route:list --path=api
php artisan schedule:list
php artisan test
./vendor/bin/pint --test
```

Seeder koristi lozinku `password` i početne naloge `admin@gmail.com`,
`stanodavac1@gmail.com`, `trazilac1@gmail.com`.

- [Lokalna backend pravila](AGENTS.md)
- [API dokumentacija](../docs/05-api/README.md)
- [Data model](../docs/02-architecture/data-model.md)
- [Development services](../docs/03-development/services.md)
