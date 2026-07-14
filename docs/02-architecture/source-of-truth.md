# Source-of-truth matrica

| Informacija | Autoritativni izvor | Dokument koji objašnjava |
| --- | --- | --- |
| API rute/middleware | `backend/routes/api.php`, `bootstrap/app.php` | `docs/05-api/contract.md` |
| Request/response polja | Form Requests, Resources, feature testovi | `docs/05-api/` |
| Schema i relacije | migracije i modeli | `data-model.md` |
| Frontend rute/guard | `frontend/src/router/index.ts` | `frontend-ui.md` |
| Mock/real ponašanje | `frontend/src/services/`, stores | `docs/03-development/api-modes.md` |
| Uloge i autorizacija | middleware, policies, permission seeder, testovi | security docs i glossary |
| Scheduled procesi | `backend/bootstrap/app.php` | `docs/03-development/services.md` |
| Queue ponašanje | Jobs, queue config, compose commands | queue runbook |
| Env nazivi/defaults | `.env*example`, config i compose | `docs/03-development/environment.md` |
| Docker topologija | oba Compose fajla | development/operations docs |
| Deploy/rollback | `ops/deploy.sh`, `ops/rollback.sh`, workflow-i | deployment runbook |
| Implementiran feature status | kod + automatizovani testovi | odgovarajući feature doc |
| Planirano | `docs/08-roadmap/` | samo roadmap |
| Trenutni projektni status | kod + roadmap odluke | `.ai/memory/project-status.md` |
| Istorija promene | Git istorija i release notes | `docs/09-archive/releases/` |

Kada se dokumenti preklapaju, ažurira se samo navedeni kanonski dokument, a
drugi dokument linkuje na njega.
