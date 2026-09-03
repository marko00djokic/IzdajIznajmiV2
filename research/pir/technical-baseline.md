# Технички baseline — верзија 0.1

> Датум/време: 2026-09-03 UTC  
> Commit: `d1489fb9a80bf8444bd6564768a5cbab14f28588`  
> Host: Linux 6.18.35, x86_64; PHP 8.4.22-dev  
> Dataset: није учитан; database/search driver: није runtime потврђен

**Табела 1. Стварно покренуте провере (извор: аутор).**

| Команда | Резултат | Тестови | Време | Статус |
|---|---|---:|---:|---|
| `php -v` | PHP 8.4.22-dev | — | није мерено | успешно |
| `cd backend && php artisan --version` | `vendor/autoload.php` недостаје | 0 | није мерено | неуспешно, окружење |
| `cd backend && php artisan route:list --path=api` | није стартовано; недостаје `vendor`, а `/usr/bin/time` није доступан | 0 | није доступно | није извршено |
| `cd backend && php artisan test --filter=RecommendationsApiTest` | није стартовано из истог разлога | 0 | није доступно | није извршено |
| `cd backend && php artisan test --filter='(SearchListingsApiTest\|ListingSearchGeoTest\|ListingFacilitiesFilterTest)'` | није стартовано | 0 | није доступно | није извршено |
| `php ops/check-docs-links.php` (почетни покушај) | није стартовано јер wrapper `/usr/bin/time` недостаје | — | није доступно | поновљено без wrapper-а |
| `php ops/check-docs-links.php` (завршна провера) | checker је стартован и пријавио 63 постојеће грешке искључиво под `backend/node_modules` | — | није мерено | неуспешно, dependency артефакти |

Ниједан број test метода није представљен као извршен тест. За понављање:

```bash
cd backend && composer install
php artisan route:list --path=api
php artisan test --filter=RecommendationsApiTest
php artisan test --filter='(SearchListingsApiTest|ListingSearchGeoTest|ListingFacilitiesFilterTest)'
```

За endpoint мерења потребни су изолован PostgreSQL test database, `SEARCH_DRIVER=sql`,
фиксни seed и test seeker. Скрипта треба да сними 20 идентичних позива после
једног warm-up-а, HTTP статус, `Server-Timing`/wall-clock latency, IDs, број
кандидата, разлоге и cache state. Извештај даје median/p95, checksum редоследа,
hard-filter violations и cold/warm разлику. Статус latency, determinism,
cold-start, filter violations и cache impact је **није извршено** — локална
апликација, зависности и dataset нису били доступни; продукција није мерена.
