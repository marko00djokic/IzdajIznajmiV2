# Design

## Context

Videti `proposal.md` i `specs/pir-offline-ranking-evaluation/spec.md`. `ListingSearchService::search` sada podrazumevano sortira po `created_at DESC`, a `RecommendationService` privatno boduje Eloquent `Listing` nad profilom izvedenim iz interakcija, zatim rezultate kešira sedam minuta. API testovi preporuka i pretrage prolaze na SQLite test okruženju; predloženi hibrid ne postoji. Istraživački izlaz mora biti ponovljiv bez baze, korisničke sesije ili javne rute.

## Goals / Non-Goals

**Goals:** isti zamrznuti kandidatni skup za tri reda; odvojena evidencija podataka, parametara i verzija; istinit prikaz `missing` i doprinosa; minimalna kompatibilna izmena postojećeg scorera.

**Non-Goals:** produkcioni recommendation endpoint, frontend B varijanta, novi skladišni model, integracija sa Meilisearch-om, učenje parametara, nezavisne relevance ocene i statistički zaključak o H1.

## Decisions

### 1. Ulaz i verzionisanje

Istraživački artefakti biće u `docs/01-project/pir/research/`: `dataset-v1.json`, `scenarios-v1.json`, manifest sa SHA-256 i poreklom, te mašinski čitljiv izlaz. Svi oglasi su sintetički u ovom koraku, sa `data_status=synthetic`, `source`, `collected_at`, `image_license=none` i bez kontakata. Koristi se propisana schema iz `research/scenarios.md`; `NA` se u JSON-u predstavlja kao `null`, a `missing_fields` navodi stvarno odsutna meka polja. `listing_code` je stabilan i jedinstven. Dataset sadrži 24–36 oglasa iz Beograda, Niša i Novog Sada; dva scenarija zaključavaju grad, budžet, obavezne pogodnosti, zone, težine, normalizacione pragove i referentni `as_of` datum. Manifest checksum pokriva izvorne bajtove, a učitavanje ga proverava pre obrade. Izmena ulaza zahteva novu verziju i checksum.

Razmotren je seed iz postojeće baze, ali on nije zamrznut istraživački ulaz, a promenljivo stanje bi otežalo poređenje.

### 2. Offline tok i poređenje

Nova Artisan komanda čita eksplicitne putanje ulaza i piše JSON rezultat u navedenu putanju. Ona ne menja bazu, cache, rute ni korisničke podatke. Čist evaluator prvo validira schema/težine, zatim jednom primeni hard filtere i isti niz prosledi trima komparatorima. S1 ima grad, budžet i status kao tvrde uslove; garaža je meki kriterijum. S2 dodatno zahteva `Wifi`. Broj 12–18 kandidata, jedinstvenost i odsustvo dominacije proveravaju se pri validaciji zamrznutog ulaza. Nevalidan ili nepotpun hard-filter atribut isključuje oglas; schema greška za globalno obavezno polje zaustavlja ceo run.

Bazni redosled koristi `published_at DESC`, zatim `listing_code ASC` za stabilnost. Za sintetički snapshot `published_at` predstavlja postojeći `created_at` redosled pretrage, što izlaz označava kao offline proxy. To ne tvrdi jednakost svih detalja živog search endpoint-a.

Razmotreno je pozivanje tri produkciona API toka, ali postojeći endpoint preporuka sam prikuplja kandidate iz istorije korisnika, pa ne može garantovati isti skup. Offline komparator zato poredi scorer, ne ceo produkcioni pipeline.

### 3. Legacy scorer bez promene API ponašanja

Fiksna formula iz `RecommendationService::scoreListing` izdvaja se u mali scorer koji prima listu atributa, profil, source tagove i referentni datum. Produkcioni servis poziva isti scorer sa sadašnjim vremenom i svojim postojećim source tagovima; izlaz, zaokruživanje i cache key se ne menjaju. Offline evaluacija mapira sintetičke atribute na isti format, koristi scenario `legacy_profile`, prazan skup source tagova i zamrznuti `as_of`. Izlaz eksplicitno označava izostavljene source bonuse i candidate generation. Parity testovi porede vrednosti pre/posle izdvajanja na reprezentativnim listing/profil kombinacijama, uključujući svežinu i source bonuse.

Razmotreno je dupliranje formule u CLI kodu, ali bi dve kopije lako odstupile. Razmotreno je i pokretanje postojećeg servisa nad test bazom; time bi upoređeni skup zavisio od signala i ograničenja broja kandidata.

### 4. Hibrid i objašnjenja

Kriterijumi verzije 1 su cena, lokacijska zona, površina, broj soba i poželjne pogodnosti. Svaki scenario sadrži fiksne granice korisnosti, uključujući cenovni pod i budžet, mapu zona, ciljne površine/sobe i listu poželjnih pogodnosti. Cena je opadajuća linearna funkcija unutar unapred zadatih granica; površina i sobe su rastuće do cilja pa ograničene na 1; zona koristi unapred zadatu vrednost u `[0,1]`; pogodnosti su udeo ispunjenih poželjnih stavki. Sve vrednosti se ograničavaju na `[0,1]`. Rating i svežina nisu skriveni kriterijumi u prvoj verziji hibrida.

Eksplicitni i ponašajni vektori imaju identičan skup imenovanih kriterijuma, nenegativne konačne vrednosti i zbir 1 uz malu unapred definisanu numeričku toleranciju. Primarni `λ=0,25` je zaključan 23. septembra 2026; `0` i `0,50` služe samo analizi osetljivosti. Za svaki oglas missing meki kriterijum se izuzima, a preostale težine se proporcionalno renormalizuju; ako nema nijednog dostupnog kriterijuma, ulaz je nevalidan. Izlaz navodi originalne i efektivne težine, `missing_fields`, doprinos svakog kriterijuma, skor, najviše tri razloga iz najvećih stvarnih doprinosa i `model_version`. Računanje koristi punu preciznost, a zaokruživanje se primenjuje tek pri serijalizaciji. Sort je `score DESC`, zatim broj missing kriterijuma ASC, pa `listing_code ASC`.

Razmotrena je min-max normalizacija prema trenutno filtriranim kandidatima, ali bi se skor oglasa promenio dodavanjem trećeg oglasa. Fiksni scenarijski pragovi održavaju isti smisao skora među ponavljanjima.

### 5. Izlaz i granica tvrdnji

JSON izlaz sadrži `dataset_version`, `scenario_version`, checksums, `model_version`, commit hash, opcije i pune rank liste za S1/S2. Nondeterminističko vreme i trajanje su u posebnom `run_metadata` delu, tako da se sadržaj rangiranja može porediti bajtno ili semantički bez tih polja. Bez nezavisnih ocena nema izračunavanja `nDCG@5`, `Precision@3` i top-1 relevance. Tek naknadni, zasebni korak sa zaključanim rubric-om i ocenjivačima može izračunati te metrike. `research/evidence-matrix.md` i tehnički baseline dobijaju komande, log i checksum; tekst 0.2 jasno odvaja implementirano od planiranog.

## Risks / Trade-offs

- Sintetički dataset može biti slab proxy za tržište → eksplicitno označiti poreklo i ograničiti zaključke.
- Izdvajanje legacy scorera može promeniti produkcioni rezultat → parity i postojeći API testovi pre i posle izmene; ne menjati candidate generation/cache.
- Scenario može nenamerno favorizovati hibrid → pragove i težine zaključati pre relevance ocena, a rubric držati nezavisnim od formule.
- Dva izvođenja mogu se razlikovati zbog vremena ili iteracionog redosleda → fiksan `as_of`, eksplicitni sort ključevi i odvojeni `run_metadata`.

## Migration Plan

Nema migracije ni deploy koraka. Novi CLI tok se koristi samo lokalno; rollback je uklanjanje izolovane komande i evaluatora uz vraćanje scorera u postojeći servis ako parity provera pokaže odstupanje. Nema upisa u produkcionu bazu.
