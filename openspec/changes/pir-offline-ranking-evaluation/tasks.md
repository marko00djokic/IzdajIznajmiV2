# Tasks

**Polazni presek 23. septembra 2026. (`1f39d57315bb674b68d9fab7f07c411f1695a054`):**
dataset, scenariji, manifest, scorer, evaluator i CLI postoje. Kućice ispod
označavaju ceo kriterijum zadatka, uključujući traženi automatski test.
Ručno izvođenje i ishodi iz početnog preseka su u
[`offline-evaluation-log-2026-09-23.md`](../../../docs/01-project/pir/research/offline-evaluation-log-2026-09-23.md).

## 1. Zamrznuti ulaz

- [x] 1.1 Napraviti `dataset-v1.json` sa 24–36 sintetičkih oglasa prema `research/scenarios.md` i proveriti schema, poreklo, jedinstvene kodove i odsustvo ličnih podataka automatskim validator testom.
- [x] 1.2 Zaključati `scenarios-v1.json` za S1/S2, uključujući hard filtere, pet mekih kriterijuma, eksplicitne i ponašajne težine, fiksne pragove i `as_of`; proveriti testom da svaki scenario ostavlja 12–18 kandidata i Pareto kompromise.
- [x] 1.3 Upisati verzije i SHA-256 ulaza u manifest i proveriti testom da izmenjen bajt dataset-a ili scenarija zaustavlja evaluaciju.

**Provereno:** 30 sintetičkih oglasa, S1/S2, manifest i automatski testovi
schema, porekla, jedinstvenosti, Pareto kompromisa i odbijanja izmenjenih bajtova.

## 2. Postojeći scorer kao kontrola

- [x] 2.1 Izdvojiti fiksnu formulu iz `RecommendationService::scoreListing` u ponovo upotrebljiv scorer bez promene API ponašanja; proveriti parity testovima grad, cenu, sobe, površinu, pogodnosti, svežinu i source bonuse.
- [x] 2.2 Mapirati zamrznuti oglas i scenario `legacy_profile` na izdvojeni scorer uz prazan skup source tagova i fiksni `as_of`; proveriti testom da score-only kontrola obuhvata sve filtrirane kandidate.

**Provereno:** `LegacyRecommendationScorer` je pozvan iz produkcionog servisa i
offline evaluatora; testovi pokrivaju pojedinačne grane formule, mapiranje svih
filtriranih kandidata i postojeći recommendation API.

## 3. Hibridni evaluator

- [x] 3.1 Implementirati validaciju polja, težina i `λ`; proveriti ciljanim testovima odbijanje nepoznatih kriterijuma, negativnih vrednosti, pogrešnog zbira i nevalidnog hard-filter ulaza.
- [x] 3.2 Implementirati hard filtere i scenarijske korisnosti u `[0,1]` sa fiksnim pragovima; proveriti testovima obavezni `Wifi`, budžet, grad, missing obavezno polje i granične vrednosti.
- [x] 3.3 Implementirati mešanje težina za primarni `λ=0,25`, preraspodelu missing mekih kriterijuma, skor, doprinose i najviše tri verna razloga; proveriti numeričke primere i zbir doprinosa u testovima.
- [x] 3.4 Implementirati stabilan sort (`score DESC`, manje missing, `listing_code ASC`) i proveriti testom identičan redosled pri ponovljenom pozivu i permutovanom ulaznom nizu.

**Provereno:** evaluator i ciljani testovi pokrivaju filtere, ponderisanje,
doprinose, missing i stabilan sort po punoj preciznosti. Razlozi su verni
identifikatori kriterijuma; korisnički tekst pripada budućem UI radu.

## 4. Poređenje i zapis

- [x] 4.1 Dodati lokalnu Artisan komandu sa eksplicitnim putanjama i JSON izlazom za bazni, score-only i hibridni redosled; proveriti testom identičan skup kodova i potpune rank liste za S1/S2.
- [x] 4.2 U izlaz upisati checksum, verzije, commit, parametre, model i odvojeni `run_metadata`; proveriti testom da dva ponavljanja imaju identičan sadržaj rangiranja i da bez ocena nema relevance metrika niti tvrdnje o H1.
- [x] 4.3 Ručno pokrenuti komandu dva puta nad zamrznutim ulazom i proveriti SHA-256, 12–18 kandidata, nula hard-filter prekršaja, iste kodove/skorove/rangove i eksplicitnu oznaku da je bazni redosled offline proxy, a heuristika score-only kontrola; sačuvati izlaz i kratak log.

**Provereno:** CLI, ručno ponavljanje i automatski command/output testovi daju
identičan sadržaj van `run_metadata` i potpune liste na istim kandidatima.

## 5. Integracija i dokumentacija

- [x] 5.1 Pokrenuti ciljane evaluator/parity/API testove i `./vendor/bin/pint --test`; proveriti prolaz i sačuvati tačne komande sa ishodima.
- [x] 5.2 Pošto se menja postojeći scorer, pokrenuti ceo `php artisan test` suite u izolovanom okruženju; proveriti prolaz ili zapisati konkretne nepovezane padove bez menjanja njihovog koda.
- [x] 5.3 Ažurirati `research/scenarios.md`, `research/evidence-matrix.md`, tehnički baseline i tekst verzije 0.2 samo stvarno dobijenim artefaktima; proveriti `php ops/check-docs-links.php` i `git diff --check`.

**Provera 23. oktobra 2026:** ciljani PIR/API testovi: 23 passed, 564
assertions; puni backend suite sa privremeno učitanim `pdo_sqlite`: 164 passed,
1132 assertions; `pint --test`: prolaz, 388 fajlova. Tačne komande i granice su
u [`technical-baseline-2026-09-23.md`](../../../docs/01-project/pir/research/technical-baseline-2026-09-23.md).
