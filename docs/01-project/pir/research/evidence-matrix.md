# Матрица техничких доказа

**Табела 1. Тврдње и проверљиви докази (извор: аутор).**

| Тврдња | Тип доказа | Путања/команда | Commit | Резултат | Статус |
|---|---|---|---|---|---|
| Канонски API користи `/api/v1` | рута | `backend/routes/api.php` (`Route::prefix('v1')`) | `d1489fb…` | versioned група постоји; alias остаје транзиционо | потврђено статички |
| Препоруке су доступне тражиоцу | рута, controller, тест | `RecommendationsController`; `RecommendationsApiTest` | `d1489fb…` | `GET /api/v1/recommendations`; seeker/admin, landlord 403 | потврђено статички |
| Хеуристика користи прегледе, saved searches и search snapshots | сервис, модели | `RecommendationService::buildPayload` | `d1489fb…` | сигнали улазе у профил и кандидате | потврђено статички |
| Модел није тренирани ML модел | сервис | `RecommendationService::scoreListing` | `d1489fb…` | фиксна правила; нема fit/train параметара | потврђено статички |
| Одговор има највише три разлога | сервис | `RecommendationService::buildReasons` | `d1489fb…` | `array_slice(..., 0, 3)` | потврђено статички |
| Експлицитне тежине нису имплементиране | API/controller/service | `RecommendationsController`, `RecommendationService` | `d1489fb…` | request нема preference weights | потврђено статички |
| Candidate ranking за издаваоца није имплементиран | руте и претрага кода | `rg -n 'rank|score' backend/app backend/routes` | `d1489fb…` | нема одговарајуће руте/сервиса | није пронађено |
| Map/radius ток је SQL | сервис и документација | `ListingSearchService::applyGeoFilter`; `docs/04-features/search.md` | `d1489fb…` | Haversine SQL над `lat`/`lng` | потврђено |
| Meilisearch зависи од конфигурације | config/service/UI | `backend/config/search.php`; `VITE_SEARCH_V2` у `Search.vue` | `d1489fb…` | `SEARCH_DRIVER=sql|meili`; UI flag | потврђено |
| Polling fallback остаје доступан | frontend код | `Chat.vue`; `NotificationBell.vue`; `stores/chat.ts` | `d1489fb…` | периодични refresh уз realtime | потврђено |
| `Application` је активан; `BookingRequest` је legacy | руте и schema | `backend/routes/api.php`; миграције | `d1489fb…` | `/apply` постоји; booking route не постоји | потврђено |
| Favorити су browser-local | frontend и руте | `frontend/src/stores/listings.ts`; `api.php` | `d1489fb…` | кључ `ii-favorites`; API endpoint не постоји | потврђено |
| `/map` је showcase, стварна мапа је у search току | router/pages/store | `Map.vue`; `Search.vue`; `stores/listings.ts` | `d1489fb…` | search тражи до 300 SQL map pin-ова | потврђено |
| Production-like није потврђена пословна продукција | compose/docs | `docker-compose.production.yml`; `docs/08-roadmap/README.md` | `d1489fb…` | стварни hosting је roadmap | потврђено |

Динамичко извршавање backend доказа није било могуће јер `backend/vendor` не
постоји. Статичка потврда не означава тест као успешно извршен.

## Допуна од 23. септембра 2026.

На commit-у `844a94ed387ed18586b3c37c8e9cf6e50cc12ef8` поново су
прочитани `backend/routes/api.php`, `RecommendationsController`,
`RecommendationService` и циљани API тестови. `GET /api/v1/recommendations`
и даље прихвата само `seeker`/`admin`; сервис и даље користи фиксне поене,
седмоминутни cache и највише три разлога. У controller-у нема улаза за
експлицитне тежине, а у сервису нема формуле хибрида из поглавља 4.
`php artisan route:list --path=api` потврдио је versioned руте.

После инсталације закључаних зависности и привременог учитавања `pdo_sqlite`,
`RecommendationsApiTest` је прошао са 3 теста и 7 асерција, а три циљане
search класе са 9 тестова и 33 асерције. Ово је доказ за наведене постојеће
токове, не за предложени хибрид. Команде и ограничења су у
[новом baseline-у](technical-baseline-2026-09-23.md); остатак табеле изнад
задржава првобитни статички пресек и није аутоматски поново потврђен.
