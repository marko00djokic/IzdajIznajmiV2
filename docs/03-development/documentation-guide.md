# Održavanje dokumentacije

## Tok izmene

1. Pronađi temu u [source-of-truth matrici](../02-architecture/source-of-truth.md).
2. Proveri tvrdnju prema kodu/config-u/testovima.
3. Ažuriraj kanonski dokument; sa drugih mesta samo linkuj.
4. Označi `partial`, `planned`, `historical` ili `stale` kada je potrebno.
5. Pokreni `php ops/check-docs-links.php`.

Feature dokument koristi [feature šablon](templates/feature.md), a incidentni
vodič [runbook šablon](templates/runbook.md). Metadata je obavezna za feature
status, roadmap, security i operativne dokumente sklone zastarevanju.

Release note se dodaje samo kada se menja ponašanje proizvoda, arhitektura ili
operacije. Kratak ADR je potreban za značajne i teško reverzibilne odluke.
