# Admin Log Viewer

> Status: `implemented`
> Poslednja strukturna provera: 2026-07-15
> Source of truth: StructuredLogController, AdminLogs.vue i admin tests

Feature added during Security Phase 6 post-audit, 2026-03-02.

Provides a paginated, filterable UI for the structured security log at `/admin/logs`.
Only users with the `admin` role can access it (protected by `role:admin`, `admin_mfa`, `mfa` middleware).

---

## 1. API endpoint

```
GET /api/v1/admin/logs
```

**Auth**: Sanctum stateful session — role `admin`, MFA enrollment required, MFA session verified.

### Query parameters

| Parameter | Type | Default | Description |
|---|---|---|---|
| `date` | `YYYY-MM-DD` | today | Which daily log file to read |
| `action` | string | — | Substring match against `context.action` or `message` |
| `level` | string | — | Exact match against `level_name` (case-insensitive): `debug`, `info`, `warning`, `error`, `critical` |
| `security_event` | boolean | — | When `true`, returns only entries where `context.security_event` is truthy |
| `user_id` | string/int | — | Exact match against `context.user_id` |
| `limit` | int | 200 | Max entries returned (hard cap: 500) |

### Response

Array of JSON objects — each entry is one line from the structured daily log, parsed as-is.
Entries are returned **newest first** (the log file is reversed before slicing).

```json
[
  {
    "message": "fraud.signal_recorded",
    "context": {
      "action": "fraud.signal_recorded",
      "security_event": true,
      "user_id": 7,
      "signal": "kyc_multi_user_ip",
      "weight": 20,
      "ip_hash": "f70ee805...",
      "distinct_users": 2
    },
    "level": 200,
    "level_name": "INFO",
    "channel": "structured",
    "datetime": "2026-03-02T14:32:05.000000+00:00",
    "extra": {}
  }
]
```

Returns `[]` when the log file for the requested date does not exist.

### Security

- `date` parameter is validated against `/^\d{4}-\d{2}-\d{2}$/` to prevent path traversal.
- Invalid format returns HTTP 422.

### Controller

`app/Http/Controllers/Admin/StructuredLogController.php`

---

## 2. Frontend

**Route**: `/admin/logs`
**Component**: `frontend/src/pages/AdminLogs.vue`
**Service function**: `getAdminLogs(params?)` in `frontend/src/services/`

### Filter UI

| Field | Maps to |
|---|---|
| Date picker | `date` |
| Action filter (text) | `action` |
| Level select | `level` |
| Security events only (checkbox) | `security_event=true` |
| User ID (text) | `user_id` |
| Refresh button | re-triggers `load()` |

### Pagination

Client-side — the backend returns up to 200 entries; the frontend slices them into **pages of 50 rows**.

- Page counter and entry range (`1–50 / 183`) displayed below the table.
- Navigation: first `«`, previous `‹`, next `›`, last `»`.
- Paginator is hidden when all results fit on a single page.
- Expanding a row is cleared when navigating to a different page.
- `currentPage` is reset to 1 on every new `load()`.

### Row expand

Click any row to expand it. The full `context` object (minus the `action` field, which is already shown in the Action column) is displayed as pretty-printed JSON.

### Level badge colours

| Level | Colour |
|---|---|
| CRITICAL / ALERT / EMERGENCY | red |
| ERROR | orange |
| WARNING | yellow |
| INFO | blue |
| DEBUG | slate |

### Security icon

Entries with `context.security_event = true` show a shield icon next to the action name.

---

## 3. i18n keys

All UI strings live under `admin.logs.*` in `frontend/src/stores/language.ts`.

| Key | English |
|---|---|
| `admin.nav.logs` | Logs |
| `admin.logs.title` | Structured Log Viewer |
| `admin.logs.date` | Date |
| `admin.logs.action` | Action filter |
| `admin.logs.level` | Level |
| `admin.logs.securityOnly` | Security events only |
| `admin.logs.userId` | User ID |
| `admin.logs.refresh` | Refresh |
| `admin.logs.empty` | No log entries found. |
| `admin.logs.time` | Time |
| `admin.logs.levelCol` | Level |
| `admin.logs.actionCol` | Action |
| `admin.logs.userCol` | User |
| `admin.logs.details` | Details |

---

## 4. Nav link

`/admin/logs` is reachable from the top navigation bar on every admin page:
`AdminDashboard`, `AdminModeration`, `AdminRatings`, `AdminUsers`, `AdminKyc`, `AdminKycAuditLog`.

---

*Last updated: 2026-03-02*
