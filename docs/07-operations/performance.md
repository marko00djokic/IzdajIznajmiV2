# Performance and DB Observability

> Status: `active reference`
> Poslednja ciljana provera: 2026-07-15
> Source of truth: database config, migrations/indexes i reporting commands

## Slow Query Logging (PostgreSQL)
Enable slow query logging in Postgres (`postgresql.conf`):

```conf
log_min_duration_statement = 250   # ms
log_statement = 'none'
log_line_prefix = '%m [%p] %u@%d '
```

Recommended for deeper analysis:
- `shared_preload_libraries = 'pg_stat_statements'`
- `pg_stat_statements.track = all`

Then inspect:
```sql
SELECT query, calls, total_exec_time, mean_exec_time
FROM pg_stat_statements
ORDER BY total_exec_time DESC
LIMIT 20;
```

## Recommended App Indexes
Added/verified for high-volume paths:
- Listings search: `(status, city, price_per_month, rooms, expired_at)` and `(status, expired_at)`
- Saved search matches unique: `(saved_search_id, listing_id)` unique
- Notifications: `(user_id, read_at)`
- Messages: `(conversation_id, created_at)`
- Rental transactions: `(listing_id, status)`

Check from app:
```bash
php artisan db:report-indexes
```
