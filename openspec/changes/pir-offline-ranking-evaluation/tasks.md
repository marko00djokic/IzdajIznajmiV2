# Tasks

## 1. Zamrznuti ulaz

- [ ] 1.1 Napraviti `dataset-v1.json` sa 24–36 sintetičkih oglasa prema `research/scenarios.md` i proveriti schema, poreklo, jedinstvene kodove i odsustvo ličnih podataka automatskim validator testom.
- [ ] 1.2 Zaključati `scenarios-v1.json` za S1/S2, uključujući hard filtere, pet mekih kriterijuma, eksplicitne i ponašajne težine, fiksne pragove i `as_of`; proveriti testom da svaki scenario ostavlja 12–18 kandidata i Pareto kompromise.
- [ ] 1.3 Upisati verzije i SHA-256 ulaza u manifest i proveriti testom da izmenjen bajt dataset-a ili scenarija zaustavlja evaluaciju.

## 2. Postojeći scorer kao kontrola

- [ ] 2.1 Izdvojiti fiksnu formulu iz `RecommendationService::scoreListing` u ponovo upotrebljiv scorer bez promene API ponašanja; proveriti parity testovima grad, cenu, sobe, površinu, pogodnosti, svežinu i source bonuse.
- [ ] 2.2 Mapirati zamrznuti oglas i scenario `legacy_profile` na izdvojeni scorer uz prazan skup source tagova i fiksni `as_of`; proveriti testom da score-only kontrola obuhvata sve filtrirane kandidate.

## 3. Hibridni evaluator

- [ ] 3.1 Implementirati validaciju polja, težina i `λ`; proveriti ciljanim testovima odbijanje nepoznatih kriterijuma, negativnih vrednosti, pogrešnog zbira i nevalidnog hard-filter ulaza.
- [ ] 3.2 Implementirati hard filtere i scenarijske korisnosti u `[0,1]` sa fiksnim pragovima; proveriti testovima obavezni `Wifi`, budžet, grad, missing obavezno polje i granične vrednosti.
- [ ] 3.3 Implementirati mešanje težina za primarni `λ=0,25`, preraspodelu missing mekih kriterijuma, skor, doprinose i najviše tri verna razloga; proveriti numeričke primere i zbir doprinosa u testovima.
- [ ] 3.4 Implementirati stabilan sort (`score DESC`, manje missing, `listing_code ASC`) i proveriti testom identičan redosled pri ponovljenom pozivu i permutovanom ulaznom nizu.

## 4. Poređenje i zapis

- [ ] 4.1 Dodati lokalnu Artisan komandu sa eksplicitnim putanjama i JSON izlazom za bazni, score-only i hibridni redosled; proveriti testom identičan skup kodova i potpune rank liste za S1/S2.
- [ ] 4.2 U izlaz upisati checksum, verzije, commit, parametre, model i odvojeni `run_metadata`; proveriti testom da dva ponavljanja imaju identičan sadržaj rangiranja i da bez ocena nema relevance metrika niti tvrdnje o H1.
- [ ] 4.3 Ručno pokrenuti komandu dva puta nad zamrznutim ulazom i proveriti SHA-256, 12–18 kandidata, nula hard-filter prekršaja, iste kodove/skorove/rangove i eksplicitnu oznaku da je bazni redosled offline proxy, a heuristika score-only kontrola; sačuvati izlaz i kratak log.

## 5. Integracija i dokumentacija

- [ ] 5.1 Pokrenuti ciljane evaluator/parity/API testove i `./vendor/bin/pint --test`; proveriti prolaz i sačuvati tačne komande sa ishodima.
- [ ] 5.2 Pošto se menja postojeći scorer, pokrenuti ceo `php artisan test` suite u izolovanom okruženju; proveriti prolaz ili zapisati konkretne nepovezane padove bez menjanja njihovog koda.
- [ ] 5.3 Ažurirati `research/scenarios.md`, `research/evidence-matrix.md`, tehnički baseline i tekst verzije 0.2 samo stvarno dobijenim artefaktima; proveriti `php ops/check-docs-links.php` i `git diff --check`.
