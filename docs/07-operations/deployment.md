# Deployment i rollback runbook

> Status: `active` za production-like self-hosting
> Poslednja ciljana provera: 2026-07-15
> Source of truth: `docker-compose.production.yml`, `ops/deploy.sh`, `ops/rollback.sh`, deploy workflow-i

## Obim i preduslovi

Repo podržava SSH/script deployment i production-like Docker Compose sa Nginx
gateway-em i opcionim Cloudflare Tunnel-om. Trenutno okruženje koristi testne
podatke i nije potvrđena poslovna produkcija.

Pre deploya obezbedi host, Docker/ili native runtime prema izabranom toku,
ispravne tajne van Git-a, backup, DNS/TLS plan i SSH pristup. Pregledaj
`.env.production.compose.example`, backend/frontend production env primere i
target workflow.

## Docker production-like deploy

```bash
docker compose -f docker-compose.production.yml config --quiet
docker compose -f docker-compose.production.yml up -d --build
docker compose -f docker-compose.production.yml exec backend php artisan migrate --force
docker compose -f docker-compose.production.yml ps
```

Opcioni javni tunnel:

```bash
docker compose -f docker-compose.production.yml --profile public up -d tunnel
```

## Verifikacija

```bash
curl -fsS http://localhost/api/v1/health
curl -fsS http://localhost/api/v1/health/ready
curl -fsS http://localhost/api/v1/health/queue
docker compose -f docker-compose.production.yml logs --tail=100 backend queue scheduler reverb gateway
```

Proveri login/Sanctum, jedan public listing, queue job, scheduler list, Reverb
origin i storage pristup. Za stvarni domen dodatno proveri HTTPS, secure cookie,
CSP/HSTS i web push ako je uključen.

## Script/CI tok

`ops/deploy.sh` je idempotentni deployment helper, a `.github/workflows/
deploy-staging.yml` i `deploy-production.yml` ga mogu pozvati preko SSH-a.
Pre korišćenja pročitaj argumente i env očekivanja direktno u skripti; GitHub
Secrets ostaju van dokumentacije.

## Rollback

`ops/rollback.sh` vraća aplikacioni release. Database rollback nije automatski
bezbedan: pre ireverzibilne migracije obezbedi kompatibilan rollback ili restore
plan. Posle rollbacka ponovi health, login, listing i queue provere.

Ako je problem u podacima, koristi [backup/restore runbook](backups.md) i prvo
restore-uj u staging/test okruženje kad god je moguće.
