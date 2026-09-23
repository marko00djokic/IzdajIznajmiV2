# Технички baseline — почетак рада на верзији 0.2

> Статус: делимично извршен, без истраживачког dataset-а и endpoint мерења  
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
latency, cache impact нити H1–H3. Dataset, S1/S2, независне оцене и rank листе
још нису замрзнути; њихов статус је **није извршено**.
