# API dokumentacija

> Status: `implemented`
> Poslednja ciljana provera: 2026-07-15
> Source of truth: `backend/routes/api.php`, Requests, Resources i feature testovi

Kanonski API prefiks je `/api/v1`. Isti route closure je trenutno registrovan i
bez `v1`, pa neversionisani `/api/*` radi kao tranzicioni alias; novi klijenti
ne treba da ga koriste.

- [Kanonski frontend-orijentisan ugovor](contract.md)
- [Praktični cURL primeri](examples.md)

## Konvencije

- Sanctum SPA auth: prvo `/sanctum/csrf-cookie`, zatim credential cookie
  zahtevi.
- Zaštićene rute koriste `auth:sanctum`, session activity, MFA i po potrebi role
  middleware/policies.
- Validacija tipično vraća `422`, auth `401`, autorizacija `403`, missing `404`,
  conflict `409`, throttle `429`.
- JSON response-i često koriste Resource `data` wrapper, ali proveri stvarni
  Resource/test za tačan payload.

Aktuelna mašinski izvedena lista:

```bash
cd backend
php artisan route:list --path=api
```

Promena rute, request-a ili response-a obavezno ažurira `contract.md`, relevantan
feature doc i test.
