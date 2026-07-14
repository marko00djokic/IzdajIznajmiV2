# Code map po domenu

| Domen | Frontend | API/backend | Podaci | Testovi |
| --- | --- | --- | --- | --- |
| Auth/sesije/MFA | `stores/auth.ts`, Login/Register/SettingsSecurity | `AuthController`, `Security/*`, auth middleware | users, user_sessions, trusted_devices, mfa_* | AuthSanctum, Security/* |
| Oglasi i slike | listings store, ListingDetail/Form/LandlordListings | Listing/LandlordListing kontroleri, ListingPolicy, ProcessListingImage | listings, listing_images, facilities | ListingsApi*, ListingLocation* |
| Search/mapa | Search.vue, MapExplorer, listings store | Search/Listings/Geocode kontroleri, search services | listings + Meilisearch index | Search*, ListingSearch*, Geocode* |
| Prijave | Bookings.vue, requests store | ApplicationController/Policy/Resource | applications | ApplicationsApiTest |
| Obilasci | Bookings.vue, viewings store | ViewingSlot/Request kontroleri i policies | viewing_slots, viewing_requests | ViewingsApiTest |
| Chat | Messages/Chat, chat store, echo service | Conversation/ChatSignal/Attachment kontroleri, events | conversations, messages, chat_attachments | ChatApiTest, ChatSignalsTest |
| Notifications | NotificationBell/Notifications, notification store, push service | Notification/Preference/Push kontroleri i jobs | notifications, preferences, push_subscriptions | Notifications*, PushSubscriptions* |
| Saved search | SavedSearches, savedSearches store | SavedSearchController, matcher/digest komande | saved_searches, matches | SavedSearch* |
| Ocene/moderacija | Reviews/Admin* | Rating/Report/Admin kontroleri | ratings, listing_ratings, reports | RatingsApi, AdminOperations |
| KYC | KycVerification/AdminKyc | KycSubmission/Document/Admin kontroleri, Scan job | kyc_submissions, kyc_documents, audit_logs | KycApiTest |
| Transakcije | Transactions/TransactionDetail/Admin* | RentalTransaction, Contract, Payment kontroleri | rental_transactions, contracts, signatures, payments | TransactionsApiTest |
| Preporuke/badges | home/listing prikazi | Recommendations/Similar kontroleri, observers/commands | listing_events, snapshots, metrics | Recommendations, SimilarListings, Badges |

Putanje u tabeli su relativne na `frontend/src/` odnosno `backend/app/`.
Potpuna lista endpoint-a dobija se komandom:

```bash
cd backend
php artisan route:list --path=api
```
