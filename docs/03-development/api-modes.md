# Mock i real API režimi

| Osobina | Mock | Real Laravel API |
| --- | --- | --- |
| Env | `VITE_USE_MOCK_API=true` | `VITE_USE_MOCK_API=false` |
| Adapter | `src/services/mockApi.ts` | `src/services/realApi.ts` |
| Auth | lokalno simuliran/store | Sanctum cookie sesija |
| Backend/DB | nisu potrebni | potrebni za trajne tokove |
| Role switch | dostupan za demo | uloga dolazi od autentifikovanog korisnika |
| E2E smoke | podrazumevano koristi mock | integracioni tok zahteva stack |
| Ograničenja | ne dokazuje backend pravila | pojedini legacy UI helper-i vraćaju prazne liste |

`frontend/src/services/index.ts` bira adapter i za funkcije kojih nema u real
adapteru može pasti nazad na mock implementaciju. Zato pri dodavanju API funkcije
eksplicitno proveri oba adaptera; tihi fallback ne sme da prikrije nepotpun real
tok.

## Sanctum real režim

Axios client koristi credentials, `/sanctum/csrf-cookie` i jedan kontrolisan
retry posle `419`. Stateful domeni, frontend URL, CORS i cookie domen/scheme
moraju odgovarati browser origin-u. Kanonski API base je `/api/v1`.

## Search režimi

`VITE_SEARCH_V2=true` uključuje Meilisearch-orijentisan list UI. Map mode i
geografsko pretraživanje koriste listing/geospatial tok. `SEARCH_DRIVER` na
backend-u bira search implementaciju.
