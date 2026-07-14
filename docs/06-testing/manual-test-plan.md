# Test Plan - IzdajIznajmiV2

> Status: `active manual plan`
> Poslednja ciljana provera setupa: 2026-07-15
> Source of truth: aktuelni UI/API i automatizovani testovi

This document defines backend and frontend manual test coverage for development/staging validation.

## A) Environment Setup
- Backend:
  ```bash
  cd backend
  composer install
  cp .env.example .env
  php artisan key:generate
  php artisan migrate:fresh --seed
  php artisan serve --host=0.0.0.0 --port=8000
  ```
- Frontend:
  ```bash
  cd frontend
  npm install
  npm run dev -- --host --port 5173
  ```
- Base URLs / CORS:
  - API: `http://localhost:8000/api/v1`
  - SPA: `http://localhost:5173`
  - Ensure Sanctum/CORS is configured for stateful cookies.
- Demo users (password for all: `password`):
  - `admin@gmail.com` (admin)
  - `stanodavac1@gmail.com`, `stanodavac2@gmail.com` (landlords)
  - `trazilac1@gmail.com`, `trazilac2@gmail.com`, `trazilac3@gmail.com` (seekers/tenants)
- Reference docs:
  - `docs/05-api/contract.md`
  - `docs/05-api/examples.md`
  - `docs/02-architecture/frontend-ui.md`

## B) Manual Test Cases

### Auth and role access
| ID | Precondition | Steps | Expected result | Notes |
| --- | --- | --- | --- | --- |
| AUTH-01 | Backend running | 1) `GET /sanctum/csrf-cookie` 2) `POST /api/v1/auth/register` with new email | `201`, session cookie set, `user.role=seeker` | register |
| AUTH-02 | AUTH-01 session | 1) `GET /api/v1/auth/me` with session cookie | `200`, returns user with role | me |
| AUTH-03 | Seeker session | 1) `POST /api/v1/landlord/listings` as seeker | `403 Forbidden` | policy |
| AUTH-04 | No session | 1) `GET /api/v1/landlord/listings` | `401` | auth guard |

### Listings browse/filter/detail
| ID | Precondition | Steps | Expected result | Notes |
| --- | --- | --- | --- | --- |
| LST-01 | Seed data | 1) `GET /api/v1/listings` | `200`, list count >0, camelCase payload | list |
| LST-02 | Seed data | 1) `GET /api/v1/listings?category=villa&priceMin=100&priceMax=300&rating=4.5` | `200`, all returned items satisfy filters | filter |
| LST-03 | Seed data | 1) `GET /api/v1/listings/{id}` | `200`, includes `images[]`, `facilities[]` | detail |

### Recommendations and badges
| ID | Precondition | Steps | Expected result | Notes |
| --- | --- | --- | --- | --- |
| REC-01 | Seeker session | 1) `GET /api/v1/listings/{id}` 2) Repeat within 12h | Exactly one view event in `listing_events` | dedupe |
| REC-02 | Seeker session + active listing | 1) `GET /api/v1/listings/{id}/similar` | `200`, excludes self/inactive listings | similar |
| REC-03 | Seeker session + view/saved search history | 1) `GET /api/v1/recommendations` | `200`, active listings + optional reasons | feed |
| BADGE-01 | Admin session | 1) `GET /api/v1/admin/users/{landlord}/security` | Returns `landlordMetrics` + `landlordBadges` | admin |
| BADGE-02 | Admin session | 1) `PATCH /api/v1/admin/users/{landlord}/badges topLandlord=false` | Badge override persisted | override |

### Search v2 (MeiliSearch)
| ID | Precondition | Steps | Expected result | Notes |
| --- | --- | --- | --- | --- |
| SRCH-01 | `SEARCH_DRIVER=meili`, indexed data | 1) `GET /api/v1/search/listings?q=belgrade` | `200`, response includes `data` and facet keys | v2 search |
| SRCH-02 | SRCH-01 | 1) `GET /api/v1/search/listings?priceBucket=0-300&rooms=2` | `200`, facet counts respect filters | facets |
| SRCH-03 | SRCH-01 | 1) `GET /api/v1/search/suggest?q=bel` | `200`, city/amenity/query suggestions | autosuggest |

### Landlord listings CRUD and policies
| ID | Precondition | Steps | Expected result | Notes |
| --- | --- | --- | --- | --- |
| LL-01 | Landlord session | 1) `POST /api/v1/landlord/listings` with valid payload | `201`, listing created with `coverImage=images[0]` | create |
| LL-02 | LL-01 listing | 1) `PUT /api/v1/landlord/listings/{id}` change `title` | `200`, title updated | update |
| LL-03 | Landlord B session, listing owned by landlord A | 1) `PUT /api/v1/landlord/listings/{A-id}` | `403` | policy |
| LL-04 | Landlord session | 1) `GET /api/v1/landlord/listings` | `200`, only owner listings with facilities/images | owner filter |

### KYC and verified landlord flow
| ID | Precondition | Steps | Expected result | Notes |
| --- | --- | --- | --- | --- |
| KYC-01 | Landlord session | 1) `POST /api/v1/kyc/submissions` with required files | `201`, status `pending` | submit |
| KYC-02 | KYC-01 pending | 1) Submit again | `409 Conflict` | duplicate blocked |
| KYC-03 | Admin session | 1) `GET /api/v1/admin/kyc/submissions?status=pending` | `200`, pending queue list | admin queue |
| KYC-04 | Admin session | 1) `PATCH /api/v1/admin/kyc/submissions/{id}/approve` | `200`, user verification status becomes `approved` | approve |
| KYC-05 | Admin session | 1) `PATCH /api/v1/admin/kyc/submissions/{id}/reject` with note | `200`, status `rejected`, note saved | reject |
| KYC-06 | Non-owner session | 1) `GET /api/v1/kyc/documents/{id}` | `403` | access control |

### Transactions (contracts, signatures, deposit)
| ID | Precondition | Steps | Expected result | Notes |
| --- | --- | --- | --- | --- |
| TX-01 | Landlord session, listing + accepted application exists | 1) `POST /api/v1/transactions` (`listingId`, `seekerId`, `depositAmount`, `rentAmount`) | `201`, status `initiated` | start |
| TX-02 | TX-01 | 1) `POST /api/v1/transactions/{id}/contracts` (`startDate`) | `201`, contract PDF in private storage | contract |
| TX-03 | TX-02 | 1) Seeker signs 2) Landlord signs (`POST /api/v1/contracts/{contract}/sign`) | Contract `final`, transaction status `landlord_signed` | signing |
| TX-04 | TX-03 + Stripe CLI forwarding | 1) `POST /api/v1/transactions/{id}/payments/deposit/session` 2) Process `checkout.session.completed` | Status `deposit_paid` | payment |
| TX-05 | TX-04 | 1) `POST /api/v1/transactions/{id}/move-in/confirm` by landlord | Status `move_in_confirmed` | move-in |
| TX-06 | Admin session | 1) `POST /api/v1/admin/transactions/{id}/payout` | Status `completed` | payout |
| TX-07 | Non-participant session | 1) `GET /api/v1/transactions/{id}` | `403` | authz |

### Ratings and profile
| ID | Precondition | Steps | Expected result | Notes |
| --- | --- | --- | --- | --- |
| RAT-01 | Completed transaction between seeker and landlord for listing | 1) `POST /api/v1/listings/{listing}/ratings` (`ratee_user_id=landlord`, `rating=5`) | `201 Created` | landlord rating |
| RAT-02 | No completed transaction | 1) same endpoint with rating | `403 Forbidden` | blocked before completion |
| RAT-03 | Completed transaction | 1) `POST /api/v1/listings/{listing}/ratings` (listing rating) | `201 Created` | listing rating |
| RAT-04 | Landlord session | 1) Landlord rates own side in listing endpoint | `403 Forbidden` | not allowed |
| RAT-05 | Landlord or seeker session, existing user rating | 1) `POST /api/v1/ratings/{rating}/report` | `201 Created`, moderation report created | report user rating |
| RAT-06 | Landlord or seeker session, existing listing rating | 1) `POST /api/v1/listing-ratings/{id}/report` | `201 Created`, moderation report created | report listing rating |
| PROF-01 | Authenticated user | 1) `PATCH /api/v1/me/profile` (`full_name`, `phone`, `address_book`) | `200`, user updated | edit profile |
| PROF-02 | Authenticated user | 1) `PATCH /api/v1/me/password` (`current_password`, new password + confirmation) | `200`, password updated | change password |
| VER-01 | Authenticated user, email not verified | 1) `POST /api/v1/me/verification/email/request` 2) `POST /api/v1/me/verification/email/confirm` (`code`) | `email_verified=true` | email verification |

### Saved searches and alerts
| ID | Precondition | Steps | Expected result | Notes |
| --- | --- | --- | --- | --- |
| SS-01 | Seeker session | 1) `POST /api/v1/saved-searches` (`filters`, optional `name`) | `201`, saved search with normalized filters | create |
| SS-02 | SS-01 exists | 1) Repeat same filters payload | `409 Conflict` | dedupe |
| SS-03 | Seeker session + new matching active listing | 1) Run `php artisan saved-searches:match` 2) `GET /api/v1/notifications` | `200`, creates match + `listing.new_match` notification | matcher |
| SS-04 | SS-03 done | 1) Run matcher again without new listings | No duplicate matches/notifications | idempotent |

### Applications (listing inquiry flow)
| ID | Precondition | Steps | Expected result | Notes |
| --- | --- | --- | --- | --- |
| APP-01 | Seeker session, listing is active | 1) `POST /api/v1/listings/{listing}/apply` with `startDate`, `endDate` (>= 1 month) and optional message | `201`, status `submitted` | create |
| APP-02 | APP-01 exists (period not expired) | 1) Repeat apply on same listing with `startDate` before or on previous `endDate` | `422`, duplicate blocked | dedupe |
| APP-02b | APP-01 exists (period not expired) | 1) Apply on same listing with `startDate` strictly after previous `endDate` | `201`, new application created | re-apply after period |
| APP-02c | APP-01 exists, previous `endDate` in the past | 1) Apply on same listing with any valid `startDate` | `201`, new application created | re-apply after expiry |
| APP-03 | Seeker session | 1) `GET /api/v1/seeker/applications` | `200`, only seeker-owned applications | seeker view |
| APP-04 | Landlord session | 1) `GET /api/v1/landlord/applications` | `200`, incoming applications | landlord view |
| APP-05 | Landlord session | 1) `PATCH /api/v1/applications/{id}` `status=accepted` | `200`, status `accepted` | accept |
| APP-06 | Seeker session, own submitted application | 1) `PATCH /api/v1/applications/{id}` `status=withdrawn` | `200`, status `withdrawn` | withdraw |
| APP-07 | Unauthorized role/ownership | 1) Update application status without permission | `403` | policy |
| APP-08 | Seeker session, listing is active | 1) Apply with reservation window shorter than one month | `422`, validation error for minimum reservation period | min window |

### Viewing slots and requests
| ID | Precondition | Steps | Expected result | Notes |
| --- | --- | --- | --- | --- |
| VW-01 | Landlord session, active listing | 1) `POST /api/v1/listings/{listing}/viewing-slots` | `201`, slot created | create slot |
| VW-02 | Seeker session, slot exists | 1) `POST /api/v1/viewing-slots/{slot}/request` | `201`, status `requested` | request viewing |
| VW-03 | Landlord session, VW-02 exists | 1) `PATCH /api/v1/viewing-requests/{id}/confirm` | `200`, status `confirmed` | confirm |
| VW-04 | Participant session, confirmed request | 1) `GET /api/v1/viewing-requests/{id}/ics` | `200`, `text/calendar` blob | calendar export |
| VW-05 | Seeker or landlord session | 1) `PATCH /api/v1/viewing-requests/{id}/cancel` | `200`, status `cancelled` | cancel |

### Messaging skeleton
| ID | Precondition | Steps | Expected result | Notes |
| --- | --- | --- | --- | --- |
| MSG-01 | Seeker session | 1) `GET /api/v1/conversations` | `200`, returns conversations where seeker participates | conversation list |
| MSG-02 | Participant session | 1) `GET /api/v1/conversations/{id}/messages` | `200`, up to 50 messages, sorted ascending | messages |
| MSG-03 | Non-participant session | 1) `GET /api/v1/conversations/{id}/messages` | `403` | authz |

### Frontend sanity checks
| ID | Precondition | Steps | Expected result | Notes |
| --- | --- | --- | --- | --- |
| FE-01 | Frontend dev server, mock store | 1) Login (mock role switch) 2) Navigate to `/favorites` as guest | Redirect to `/`, "Access denied" toast | route guard |
| FE-02 | Role switch to landlord | 1) `/profile` -> switch to Landlord 2) "My Listings" link opens `/landlord/listings` | Works and displays listing cards | navigation |
| FE-03 | Slow/failure simulation | 1) Simulate slow network 2) Open `/search` and `/map` 3) Verify skeleton/empty/error states | Expected UI states visible | UX |
| FE-04 | Seeker or landlord with existing applications | 1) Open `/bookings?tab=reservations&section=requests` 2) Click `Details` | Modal shows reservation period, created/updated/withdrawn timestamps, calculated price, and participant full names | reservations details |

## C) API cURL Notes
Base URL: `http://localhost:8000`

Use Sanctum cookie/session flow:
1. `GET /sanctum/csrf-cookie`
2. Use cookie jar for authenticated API calls
3. Send `X-XSRF-TOKEN` header for state-changing requests

See `docs/05-api/examples.md` for complete cURL sequences.

## D) Negative Tests (401/403/422/404)
- `401`: `GET /api/v1/landlord/listings` without session -> `{"message":"Unauthenticated."}`
- `403`: seeker attempts `PATCH /api/v1/applications/{id}` with `status=accepted`
- `403`: landlord B attempts `PUT /api/v1/landlord/listings/{listingA}`
- `422`: duplicate `POST /api/v1/listings/{listing}/apply`
- `422`: `POST /api/v1/viewing-slots/{slot}/request` when capacity is full
- `422`: `POST /api/v1/landlord/listings` with invalid category (example `cabin`)
- `404`: `GET /api/v1/listings/99999`

## E) Smoke Checklist (about 10 minutes)
1. `POST /api/v1/auth/login` as tenant -> authenticated session established.
2. `GET /api/v1/listings` -> `200`, returns data array.
3. `GET /api/v1/listings/{id}` -> `200`, includes images/facilities.
4. `POST /api/v1/listings/{id}/apply` as seeker with valid monthly reservation window -> `201 submitted`.
5. `GET /api/v1/seeker/applications` -> includes newly created application.
6. `PATCH /api/v1/applications/{id}` as landlord -> `status=accepted`, `200`.
7. `GET /api/v1/landlord/listings` as landlord -> `200`, only owner listings.
8. `PUT /api/v1/landlord/listings/{id}` as owner -> `200`.
9. `GET /api/v1/conversations` as tenant -> `200` conversation list.
10. Frontend check: Home/Search renders cards; Profile role switch works; route guards block unauthorized routes.
