# Docker quick start

> Status: `implemented`
> Poslednja ciljana provera: 2026-07-15
> Source of truth: `docker-compose.yml`, Dockerfile-ovi i `.env` primeri

## Preduslovi

- Docker Engine/Desktop sa Compose v2;
- slobodni portovi 5173, 8000, 8080, 7700 i 5432;
- Git checkout repozitorijuma.

## Pokretanje

```bash
docker compose up -d --build
docker compose exec backend php artisan migrate --seed
docker compose exec backend php artisan storage:link
docker compose ps
```

Compose pokreće Postgres, Laravel, queue worker, scheduler, Reverb, frontend i
Meilisearch. Frontend je u real API režimu.

| Servis | Lokalni endpoint |
| --- | --- |
| frontend | `http://localhost:5173` |
| backend/API | `http://localhost:8000/api/v1` |
| Reverb | `ws://localhost:8080` |
| Meilisearch | `http://localhost:7700` |
| Postgres | `localhost:5432` |

Demo lozinka je `password`; koristi `admin@gmail.com`,
`stanodavac1@gmail.com` ili `trazilac1@gmail.com`.

## Prve provere

```bash
curl http://localhost:8000/api/v1/health
docker compose exec backend php artisan route:list --path=api
docker compose exec backend php artisan schedule:list
docker compose logs --tail=100 backend queue scheduler reverb
```

## Zaustavljanje

```bash
docker compose down
```

`docker compose down -v` briše trajne lokalne volume-e i podatke; koristi ga
samo kada je namerno potreban čist reset.

Za detalje vidi [Docker workflow](docker-workflow.md), a za procese
[development services](services.md).
