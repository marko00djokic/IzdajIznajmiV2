# Spec Delta

## Purpose

Omogućava proverljivo offline poređenje baznog redosleda, postojeće heuristike i istraživačkog hibrida nad istim zamrznutim oglasima i scenarijima PIR-a.

## ADDED Requirements

### Requirement: Zamrznuti istraživački ulaz
Sistem SHALL prihvatati verzionisan dataset i scenarije sa potpunim poreklom, pravilima za missing polja i proverljivim SHA-256 checksum-om. Objavljeni ulaz MUST biti sintetički ili imati dokumentovanu dozvolu, bez ličnih podataka izdavalaca i neovlašćenih slika.

#### Scenario: Provera integriteta ulaza
- **WHEN** istraživač pokrene evaluaciju nad neizmenjenim dataset-om i scenarijima
- **THEN** rezultat sadrži njihove verzije i SHA-256 vrednosti identične vrednostima dobijenim iz ulaznih bajtova

#### Scenario: Nevalidan ulaz
- **WHEN** dataset ima dupliran `listing_code`, nepoznat `data_status`, nevalidnu cenu ili obavezno polje bez vrednosti
- **THEN** evaluacija staje sa jasnom greškom pre izračunavanja bilo koje rank liste

### Requirement: Isti kandidati i tvrdi uslovi
Sistem SHALL pre svakog rangiranja primeniti zaključane hard filtere scenarija nad celim dataset-om. Nepoznata vrednost obaveznog polja MUST isključiti oglas. Za S1 i S2 validiran zamrznuti ulaz SHALL dati po 12–18 kandidata, a svi komparatori MUST dobiti identičan skup `listing_code` vrednosti.

#### Scenario: Obavezna pogodnost
- **WHEN** S2 zahteva `Wifi`, a oglas nema `Wifi` ili nema poznatu listu pogodnosti
- **THEN** oglas ne ulazi ni u jednu od tri rank liste za S2

#### Scenario: Isti kandidatni skup
- **WHEN** se izračunaju bazni redosled, postojeća heuristika i hibrid za jedan scenario
- **THEN** svaka potpuna rank lista sadrži tačno isti skup jedinstvenih kodova, bez oglasa koji krše hard filtere

### Requirement: Validacija modela i ponderisani skor
Sistem SHALL odbiti nepoznat kriterijum, negativnu ili nenumeričku težinu, vektor čiji zbir nije 1 i `λ` van `[0,1]`. Za validan ulaz SHALL kombinovati eksplicitne i ponašajne težine kao `w_i=(1-λ)e_i+λb_i`, izračunati normalizovane korisnosti u `[0,1]`, `score=100×Σw_i x_i` i vratiti stvarne doprinose kriterijuma. Primarni rezultat MUST koristiti `λ=0,25`; rezultati za `0` i `0,50` SHALL biti posebno označeni kao analiza osetljivosti.

#### Scenario: Ispravan rezultat
- **WHEN** se rangira kandidat sa potpunim mekim podacima
- **THEN** rezultat sadrži skor u `[0,100]`, doprinose čiji zbir odgovara skoru uz deklarisano zaokruživanje i razloge zasnovane na stvarnim doprinosima

#### Scenario: Pogrešne težine
- **WHEN** scenario navede negativnu težinu ili zbir eksplicitnih težina različit od 1
- **THEN** evaluacija odbija scenario i ne zapisuje prividno validan rezultat

### Requirement: Missing podaci i stabilan redosled
Sistem SHALL predstavljati nepoznatu meku vrednost kao missing, ne kao nulu. Težinu missing kriterijuma SHALL preraspodeliti srazmerno samo dostupnim kriterijumima za dati oglas i prijaviti koji kriterijumi nedostaju. Rangiranje MUST koristiti `score DESC`, zatim manje missing kriterijuma, zatim `listing_code ASC`; isti ulaz i verzija modela MUST dati isti izlaz.

#### Scenario: Nepotpuna meka vrednost
- **WHEN** kandidatu nedostaje površina, a površina nije hard filter
- **THEN** kandidat ostaje u skupu, njegov izlaz označava missing površinu i skor je izračunat iz preostalih dostupnih kriterijuma

#### Scenario: Potpuna jednakost
- **WHEN** dva oglasa imaju isti skor i isti broj missing kriterijuma
- **THEN** oglas sa leksikografski manjim `listing_code` prethodi drugom u svakoj ponovljenoj evaluaciji

### Requirement: Reproduktivno poređenje bez preuranjenog zaključka
Offline rezultat SHALL sadržati potpune rank liste za bazni redosled, postojeću fiksnu heuristiku kao score-only komparator i hibrid, uz scenario, verziju modela, checksum ulaza, parametre i vreme izvršavanja. Bazni redosled SHALL predstavljati pretragu po `published_at DESC` sa stabilnim kodom za jednak datum. Izlaz MUST jasno označiti da heuristički komparator ne uključuje produkciono prikupljanje kandidata. Bez nezavisnih ocenjenih relevance vrednosti sistem MUST izostaviti `nDCG@5`, `Precision@3`, top-1 relevance i sud o H1.

#### Scenario: Izlaz bez ocenjivača
- **WHEN** se evaluacija pokrene bez nezavisnih relevance ocena
- **THEN** zapisuje sve tri rank liste i manifest, a nijedna metrika relevantnosti niti tvrdnja o potvrdi H1 nije prisutna

#### Scenario: Ponovljeno izvršavanje
- **WHEN** se evaluacija dva puta pokrene nad istim verzijama i parametrima
- **THEN** skupovi kandidata, skorovi i redosledi su identični; jedino polja označena kao run metadata smeju se razlikovati

### Requirement: Izolacija od javnog proizvoda
Istraživačka evaluacija MUST biti dostupna kroz lokalni offline tok i MUST ostaviti ugovor i rezultat `GET /api/v1/recommendations` nepromenjenim.

#### Scenario: Postojeći API
- **WHEN** se izvrše postojeći API testovi preporuka posle dodavanja offline toka
- **THEN** autorizacija, oblik odgovora i potvrđeno ponašanje postojećih preporuka ostaju isti
