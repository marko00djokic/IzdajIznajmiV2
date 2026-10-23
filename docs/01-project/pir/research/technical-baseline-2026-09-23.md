# Технички baseline — почетак рада на верзији 0.2

> Статус: почетни runtime пресек; новија offline допуна је испод
> Датум: 23. септембар 2026.  
> Commit пре измена: `844a94ed387ed18586b3c37c8e9cf6e50cc12ef8`  
> Окружење: Linux, PHP 8.3.6 CLI, Laravel 12.47.0; тестови користе SQLite `:memory:` и `SEARCH_DRIVER=sql`

Ово је нови пресек; [baseline верзије 0.1](technical-baseline.md) остаје
историјски запис. Није покренут продукциони stack нити коришћен тржишни dataset.

**Табела 1. Стварно извршене провере (извор: аутор).**

| Команда | Исход | Статус |
|---|---|---|
| `git status --short` пре рада | празан излаз | успешно |
| `composer install --no-interaction --prefer-dist --no-progress` | први покушај: DNS грешка за `api.github.com`; поновљен у окружењу са мрежним приступом | први покушај неуспешан |
| `COMPOSER_CACHE_DIR=/tmp/pir-composer-cache COMPOSER_MAX_PARALLEL_HTTP=1 composer install --no-interaction --prefer-dist --no-progress` | зависности из lock фајла инсталиране; први паралелни покушај имао је оштећене празне ZIP датотеке, секвенцијални покушај завршен успешно | успешно |
| `composer validate --no-check-publish` | `composer.json` је валидан | успешно |
| `php artisan --version` | Laravel Framework 12.47.0 | успешно |
| `php artisan route:list --path=api` | потврђене руте `GET /api/v1/recommendations`, `GET /api/v1/listings` и `GET /api/v1/search/listings` | успешно |
| `php artisan test --filter=RecommendationsApiTest` | 3 теста стала пре асерција: системски PHP нема `pdo_sqlite` | неуспешно, окружење |
| `php artisan test --filter='(SearchListingsApiTest\|ListingSearchGeoTest\|ListingFacilitiesFilterTest)'` | 9 тестова стало пре асерција из истог разлога | неуспешно, окружење |
| `PHP_INI_SCAN_DIR=:/tmp/pir-php-ext/conf.d php artisan test --filter=RecommendationsApiTest` | 3 теста, 7 асерција | успешно |
| `PHP_INI_SCAN_DIR=:/tmp/pir-php-ext/conf.d php artisan test --filter='(SearchListingsApiTest\|ListingSearchGeoTest\|ListingFacilitiesFilterTest)'` | 9 тестова, 33 асерције | успешно |
| `php ops/check-docs-links.php` | 124 Markdown фајла, 242 интерна линка при почетној провери | успешно |

SQLite PDO модул је преузет као Ubuntu `php8.3-sqlite3` пакет и распакован у
`/tmp/pir-php-ext`; привремени `conf.d/pir-sqlite.ini` учитава
`/tmp/pir-php-ext/extracted/usr/lib/php/20230831/pdo_sqlite.so`. Системски
PHP и lock фајл нису мењани. Путања `/tmp` је локална и није део репозиторијума;
за поновљиву проверу на другој машини треба обезбедити сопствени `pdo_sqlite`
или Docker окружење. Локални `docker` CLI није доступан.

Сачувани излази поновљених тестова су
[`recommendations-test-2026-09-23.log`](recommendations-test-2026-09-23.log) и
[`search-test-2026-09-23.log`](search-test-2026-09-23.log).

Тестови доказују постојеће API токове над синтетичким test fixture-има. Они не
доказују нови хибрид, квалитет рангирања, PostgreSQL/Meilisearch понашање,
latency, cache impact нити H1–H3. У овом почетном пресеку dataset, S1/S2 и
rank листе још нису били замрзнути.

## Накнадна offline провера истог датума

На commit-у `1f39d57315bb674b68d9fab7f07c411f1695a054` постоје
замрзнути синтетички dataset, сценарији и checksum manifest. Два ручна
покретања `pir:evaluate` успела су и дала исти садржај рангирања ван
`run_metadata`: S1 16 и S2 13 кандидата, нула hard-filter прекршаја и три
потпуне rank листе по сценарију. Излаз и тачни checksum-и су у
[логу](offline-evaluation-log-2026-09-23.md). Нема независних relevance
оцена ни `nDCG@5` резултата.

`PHP_INI_SCAN_DIR=:/tmp/pir-php-ext/conf.d php artisan test` је прошао са
153 теста и 608 асерција. Четири циљане recommendation/search класе прошле
су са 12 тестова и 40 асерција. Нема PIR evaluator/parity/CLI тестова.
`./vendor/bin/pint --test` није прошао: један `ordered_imports` налаз у
`backend/bootstrap/app.php`. Ови резултати не доказују перформансе,
PostgreSQL/Meilisearch или корисничке H2/H3 исходе.

## Ponovna provera radnog stabla — 23. oktobar 2026.

Na commit-u `1f39d57315bb674b68d9fab7f07c411f1695a054` uz nezabeležene
izmene iz OpenSpec promene `pir-offline-ranking-evaluation` dodati su ciljani
PIR evaluator i CLI testovi. Sistemskom PHP 8.3.6 nedostaje `pdo_sqlite`, pa je
paket `php8.3-sqlite3` raspakovan samo u `/tmp/pir-php-ext` i učitan kroz
`PHP_INI_SCAN_DIR`; sistemska instalacija i lock fajlovi nisu menjani.

| Komanda iz `backend/` | Ishod |
| --- | --- |
| `php artisan test --filter='(PirOfflineEvaluatorTest|PirOfflineEvaluateCommandTest)'` | 11 passed, 524 assertions; ne koristi bazu |
| `PHP_INI_SCAN_DIR=:/tmp/pir-php-ext/conf.d php artisan test --filter='(PirOfflineEvaluatorTest|PirOfflineEvaluateCommandTest|RecommendationsApiTest|SearchListingsApiTest|ListingSearchGeoTest|ListingFacilitiesFilterTest)'` | 23 passed, 564 assertions |
| `PHP_INI_SCAN_DIR=:/tmp/pir-php-ext/conf.d php artisan test` | 164 passed, 1132 assertions |
| `./vendor/bin/pint --test` | prolaz, 388 fajlova |
| `php ops/check-docs-links.php` iz korena repozitorijuma | prolaz, 131 Markdown fajl i 298 internih linkova |
| `git diff --check` iz korena repozitorijuma | prolaz, bez whitespace grešaka |
| `openspec validate pir-offline-ranking-evaluation` iz korena repozitorijuma | promena je validna |

Početni pokušaj API i punog suite-a bez `pdo_sqlite` stao je na `could not find
driver`; to je ograničenje lokalnog okruženja, ne pad poslovne logike. Prethodni
nalaz `ordered_imports` u `backend/bootstrap/app.php` je ispravljen. Puni
suite potvrđuje postojeći API u SQLite test okruženju, a ne performanse,
PostgreSQL/Meilisearch ponašanje ili relevantnost rangiranja.
