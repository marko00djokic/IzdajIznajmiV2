# Native setup

Native workflow je alternativa za ciljanu dijagnostiku. Podrazumevani onboarding
je [Docker quick start](quick-start.md).

## Preduslovi

- PHP 8.2+, Composer i potrebne PHP ekstenzije (uključujući GD);
- Node.js 20+ i npm;
- SQLite za najjednostavniji start ili podešen PostgreSQL;
- opciono Meilisearch za Search V2.

## Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve --port=8000
```

U dodatnim terminalima pokreni procese potrebne za feature:

```bash
php artisan queue:work
php artisan schedule:work
php artisan reverb:start --host=0.0.0.0 --port=8080
```

## Frontend

```bash
cd frontend
npm ci
cp .env.example .env
npm run dev -- --host --port=5173
```

Za Laravel API postavi `VITE_USE_MOCK_API=false`; za izolovani UI rad koristi
`true`. Ako je `VITE_API_BASE_URL` prazan, Vite proxy prosleđuje `/api` i
`/sanctum` backend-u prema `vite.config.ts`.

Kompletna matrica pomoćnih servisa je u [services](services.md).
