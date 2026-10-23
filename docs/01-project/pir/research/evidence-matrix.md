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

Горња табела је историјски пресек од 3. септембра 2026, када
`backend/vendor` није постојао. Статичка потврда не означава тест као успешно
извршен. Новији исходи су наведени испод.

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

## Техничка допуна после commit-а `1f39d573…`

| Тврдња | Доказ | Исход | Граница |
| --- | --- | --- | --- |
| Замрзнут је синтетички улаз | [`dataset-v1.json`](dataset-v1.json), [`scenarios-v1.json`](scenarios-v1.json), [`manifest-v1.json`](manifest-v1.json), SHA-256 | 30 огласа; S1 16, S2 13 кандидата; checksum-и се поклапају | Pareto компромиси и одбијање промењеног улаза нису аутоматски тестирани |
| Offline поређење постоји | `PirOfflineEvaluateCommand`, `PirOfflineEvaluator`, [`излаз`](offline-evaluation-v1.json) и [`лог`](offline-evaluation-log-2026-09-23.md) | базни proxy, score-only heuristika и хибрид имају исти скуп кандидата и нула hard-filter прекршаја; два run-а имају исти садржај без `run_metadata` | нема независних relevance оцена ни закључка о H1 |
| Постојећи scorer је издвојен | `LegacyRecommendationScorer`, `RecommendationService` | 12 циљаних API/search тестова и цео backend suite 153/608 пролазе | нема посебног parity теста за сваку грану scorer-а |
| Хибрид је локални истраживачки ток | `PirOfflineEvaluator`, `backend/routes/api.php` | нема нове јавне руте; продукциони API нема експлицитне тежине | нема PIR evaluator/CLI тестова ни корисничке B варијанте |
| Формални quality gate | `./vendor/bin/pint --test` | 1 `ordered_imports` налаз у `backend/bootstrap/app.php` | није пролаз |

Тачне команде, опсег runtime провере и SHA-256 су у
[логу](offline-evaluation-log-2026-09-23.md). Ова допуна не мења историјске
тврдње из горње табеле, али се за садашњи статус користе новији код и тестови.

## Provera OpenSpec promene — 23. oktobar 2026.

Ovaj odeljak zamenjuje ograničenja iz prethodne tabele koja su se odnosila na
odsustvo namenskih testova i prolaz stila; istorijski ishodi ostaju zapisani.

| Tvrdnja | Sadašnji dokaz | Ishod i granica |
| --- | --- | --- |
| Schema, poreklo i Pareto kompromisi | `PirOfflineEvaluator`, `PirOfflineEvaluatorTest`, zamrznuti JSON ulazi | 30 sintetičkih oglasa; S1 16, S2 13 kandidata; test proverava suprotstavljene prednosti, a evaluator odbija izmenjen ulaz bez kompromisa |
| Integritet ulaza i potpuni redosledi | `PirOfflineEvaluateCommandTest`, manifest i izlaz | izmenjen bajt oba ulaza se odbija; tri potpune rank liste imaju iste kodove i ponovljiv sadržaj van `run_metadata` |
| Paritet fiksne formule i API-ja | `PirOfflineEvaluatorTest`, `RecommendationsApiTest` | pojedinačne grane formule i mapiranje svih offline kandidata su provereni; API testovi prolaze u SQLite okruženju |
| Hibrid i stabilnost | `PirOfflineEvaluatorTest` | testovi pokrivaju težine, `λ`, missing, doprinos, granice i sort po punoj preciznosti |
| Integraciona i stilska provera | [tehnički baseline](technical-baseline-2026-09-23.md) | 23 ciljana testa / 564 asercija, pun suite 164 / 1132; `pint --test` prolazi za 388 fajlova |

Nema nezavisnih relevance ocena, `nDCG@5` ni zaključka o H1. Bazni redosled
ostaje offline proxy, a postojeća heuristika score-only kontrola bez
produkcione candidate generation. Sintetički ulaz nema polje trajanja najma.

## Provera 23. oktobra 2026.

Istorijska ograničenja iz tabele iznad više ne opisuju sve dostupne testove.
`PirOfflineEvaluatorTest` i `PirOfflineEvaluateCommandTest` sada automatski
proveravaju schema i poreklo ulaza, jedinstvene kodove, Pareto kompromise,
16/13 kandidata, tri potpune rank liste, hard filtere, težine, korisnosti,
missing vrednosti, numerički skor, stabilan redosled i odbijanje izmenjenih
bajtova. `php artisan test --filter='PirOfflineEvaluatorTest|PirOfflineEvaluateCommandTest'`
prošao je sa 8 testova i 441 asercijom. `./vendor/bin/pint --test` prošao je za
388 fajlova. `php ops/check-docs-links.php` i `git diff --check` su prošli.
PHP okruženje u ovom prolazu nema `pdo_sqlite`, pa postojeći API testovi nisu
ponovo izvršeni; raniji rezultat celog suite-a ostaje zaseban istorijski dokaz.
Ne postoje nezavisne ocene relevantnosti, metrika H1 niti korisnički pilot.
