# Docker workflow

> Status: `implemented`
> Poslednja ciljana provera: 2026-07-15
> Source of truth: `docker-compose.yml` i `docker-compose.production.yml`

## Development stack

`docker compose up -d --build` koristi bind mount-e za kod i named volume-e za
vendor, node_modules, storage, Postgres i Meilisearch podatke. Frontend Vite
server sluša na 5173, backend `artisan serve` na 8000.

```bash
docker compose ps
docker compose logs -f backend
docker compose restart queue scheduler reverb
docker compose exec backend php artisan migrate
docker compose exec backend php artisan test
```

Posle promene `composer.lock`, `package-lock.json`, Dockerfile-a ili build-time
env vrednosti rebuild-uj odgovarajući servis.

## Production-like stack

```bash
docker compose -f docker-compose.production.yml up -d --build
docker compose -f docker-compose.production.yml exec backend php artisan migrate --force
```

Ovaj stack dodaje Nginx `gateway`, Vite production build/preview i opcioni
Cloudflare `tunnel` profil. On nije potvrđena poslovna produkcija i trenutno se
koristi sa testnim podacima.

```bash
docker compose -f docker-compose.production.yml --profile public up -d
```

Stvarne vrednosti dolaze iz lokalnog `.env.production`/Compose environment-a;
ne commituj ih. Primer je `.env.production.compose.example`.

## Troubleshooting

- `419`: proveri frontend/backend URL, `SANCTUM_STATEFUL_DOMAINS`, CORS i cookie
  secure/samesite vrednosti.
- slike ostaju `pending`: proveri `queue` log i storage link/volume.
- chat nema događaje: proveri Reverb env/origin i `reverb`/`queue` logove;
  polling fallback ne dokazuje da WebSocket radi.
- Search V2 ne vraća rezultat: proveri `meilisearch`, key/host i reindex komande.
- kod nije osvežen: rebuild je potreban za dependency/build-time promene; bind
  mount pokriva obične source izmene u dev stacku.

Operativni deploy i rollback nisu razvojni workflow; vidi
[deployment runbook](../07-operations/deployment.md).
