# Oglasi i discovery

> Status: `implemented` sa eksplicitnim `partial` delovima
> Poslednja ciljana provera: 2026-07-15
> Source of truth: listing/search kontroleri, store/service adapteri i testovi

## Tok i pravila

Gost vidi aktivne oglase. Izdavalac kreira draft, menja ga i prelazi kroz
publish/unpublish/archive/restore/rented/available akcije. Policy ograničava
izmene na vlasnika/admina, a scheduled `listings:expire` ističe oglase prema
domenskom pravilu. Slike se uploaduju multipart zahtevom i obrađuju u queue-u.

Discovery postoji kroz legacy `/listings`, Search V2 `/search/listings`,
suggest/geocode i geografski map mode. `/search` sadrži stvarnu Leaflet/OSM mapu;
zaseban `/map` ekran je showcase (`partial`). Favoriti su browser-local
(`partial`), dok su saved searches trajni backend domen.

## Code map

| Sloj | Putanje |
| --- | --- |
| Frontend | `pages/Home.vue`, `Search.vue`, `ListingDetail.vue`, `ListingForm.vue`, `stores/listings.ts` |
| API/backend | `ListingController`, `LandlordListingController`, `SearchController`, `ListingPolicy`, `ListingStatusService` |
| Async | `ProcessListingImage`, search/geocode jobs, `ExpireListingsCommand` |
| Podaci | listings, listing_images, facilities, saved_searches |
| Testovi | `ListingsApi*`, `Search*`, `ListingLocation*`, `SavedSearch*` |

Search detalji su u [Search V2](search.md); potpuni endpoint-i u
[API ugovoru](../05-api/contract.md).
