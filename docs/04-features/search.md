# Search v2 (MeiliSearch)

> Status: `implemented`, feature flag/driver-zavisno
> Poslednja ciljana provera: 2026-07-15
> Source of truth: search config, kontroleri/services, listing store i testovi

## Overview
Search v2 adds facet-powered listing search and autosuggest behind a feature flag. The legacy SQL endpoint stays intact for map/radius search and as a fallback driver.

## Local MeiliSearch
Run MeiliSearch locally (Docker):
```bash
docker run --rm -p 7700:7700 -e MEILI_MASTER_KEY=masterKey getmeili/meilisearch:v1.8
```

## Environment variables
Backend (`backend/.env`):
- `SEARCH_DRIVER=sql|meili`
- `MEILISEARCH_HOST=http://localhost:7700`
- `MEILISEARCH_KEY=masterKey` (or empty if no key)
- `MEILISEARCH_INDEX=listings`
- `SEARCH_MAX_PER_PAGE=50`

Frontend (`frontend/.env`):
- `VITE_SEARCH_V2=true` to enable the new search UI + endpoints.

## Reindexing
After enabling Meili, reindex active listings:
```bash
cd backend
php artisan search:listings:reindex
```
If you need a clean rebuild (drops the index first):
```bash
php artisan search:listings:reindex --reset
```
Optional (best-effort sync):
```bash
php artisan search:listings:sync-missing
```

## API endpoints
Namespace: `/api/v1/search/*`

### GET /api/v1/search/listings
Query params:
- `q` (string)
- `city` (string)
- `category` (string)
- `guests` (number)
- `status` (string or list)
- `rooms` (number)
- `amenities[]` (array or comma-separated)
- `priceMin` / `priceMax` or `priceBucket` (e.g. `0-300`)
- `areaMin` / `areaMax` or `areaBucket` (e.g. `0-30`)
- `sort` (`price_asc|price_desc|newest|rating`)
- `page`, `perPage`

Response:
```json
{
  "data": [/* Listing cards */],
  "meta": { "page": 1, "perPage": 10, "total": 120, "lastPage": 12 },
  "facets": {
    "city": [{"value":"Belgrade","count":12}],
    "status": [{"value":"active","count":120}],
    "rooms": [{"value":"2","count":40}],
    "amenities": [{"value":"Wi-Fi","count":52}],
    "price_bucket": [{"value":"300-600","count":30}],
    "area_bucket": [{"value":"60-100","count":25}]
  }
}
```

### GET /api/v1/search/suggest
Params:
- `q` (string)
- `limit` (default 8)

Response:
```json
[{"label":"Belgrade","type":"city","value":"Belgrade"}]
```

## Notes
- Only `active` listings are searchable by default.
- Map/radius search continues to use `/api/v1/listings` with SQL geo filters.
- Index updates are queued on listing create/update/status changes and facility sync.
