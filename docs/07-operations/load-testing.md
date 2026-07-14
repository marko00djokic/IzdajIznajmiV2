# Load Testing (k6)

> Status: `active baseline`; thresholds must be calibrated per environment
> Poslednja ciljana provera: 2026-07-15
> Source of truth: `ops/loadtest/*.js`

Baseline k6 scripts are in `ops/loadtest/`:
- `auth-login.js`
- `search-listings.js`
- `listing-detail.js`
- `chat-send-message.js`
- `saved-search-create.js`

## Prerequisites
- Install k6: https://k6.io/docs/get-started/installation/
- Seed backend demo data and run API locally or in staging.

## Common Environment Variables
- `BASE_URL` (default `http://localhost:8000`)
- `K6_VUS` (default `5`)
- `K6_DURATION` (default `30s`)
- `K6_P95_MS` (default `500`)

Authenticated scripts also require:
- `LOGIN_EMAIL`
- `LOGIN_PASSWORD`

Optional:
- `LISTING_ID` (force listing target)
- `CONVERSATION_ID` (force chat thread)
- `SEARCH_CITY` (search filter)

## Run Commands
```bash
# 1) Auth login
BASE_URL=http://localhost:8000 \
LOGIN_EMAIL=trazilac1@gmail.com \
LOGIN_PASSWORD=password \
k6 run ops/loadtest/auth-login.js

# 2) Search listings
BASE_URL=http://localhost:8000 \
SEARCH_CITY=Zagreb \
k6 run ops/loadtest/search-listings.js

# 3) Listing detail
BASE_URL=http://localhost:8000 \
k6 run ops/loadtest/listing-detail.js

# 4) Chat open + send message
BASE_URL=http://localhost:8000 \
LOGIN_EMAIL=trazilac1@gmail.com \
LOGIN_PASSWORD=password \
k6 run ops/loadtest/chat-send-message.js

# 5) Saved search create/delete
BASE_URL=http://localhost:8000 \
LOGIN_EMAIL=trazilac1@gmail.com \
LOGIN_PASSWORD=password \
k6 run ops/loadtest/saved-search-create.js
```

## Acceptance Thresholds (Local Baseline)
- `http_req_duration` p95 `< 500ms`
- `http_req_failed` rate `< 1%`

Adjust thresholds via `K6_P95_MS` for staging/production baselines.
