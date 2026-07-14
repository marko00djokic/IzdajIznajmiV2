# Feature dokumentacija

Feature dokument opisuje status, poslovni tok, code map, testove i gotchas.
Endpoint detalji se ne dupliraju: kanonski su u [API ugovoru](../05-api/contract.md).

| Domen | Status | Dokument |
| --- | --- | --- |
| Oglasi, discovery i slike | `implemented` / pojedinačno `partial` | [listings-and-discovery](listings-and-discovery.md) |
| Prijave i obilasci | `implemented`, legacy booking `partial` | [applications-and-viewings](applications-and-viewings.md) |
| Chat/realtime | `implemented` sa polling fallbackom | [chat](chat.md) |
| Notifications/push | `implemented`, env-zavisno | [notifications](notifications.md) |
| Search V2 | `implemented`, flag/driver-zavisno | [search](search.md) |
| KYC | `implemented`, AV env-zavisno | [kyc](kyc.md) |
| Transactions | `implemented`, Stripe env-zavisno | [transactions](transactions.md) |
| Recommendations/badges | `implemented` | [recommendations](recommendations.md) |
| Security | `implemented`, hardening se nastavlja | [security indeks](security/README.md) |
| UI reference | `historical` | [UI slike](ui-reference/README.md) |

Centralna lista partial stavki je u
[known limitations](../01-project/known-limitations.md).
