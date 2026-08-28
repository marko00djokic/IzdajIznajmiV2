# Prompt za AI agenta — izrada nulte verzije PIR-a

Tekst ispod je spreman za kopiranje. Uz prompt treba priložiti najmanje
[`pir-plan.md`](pir-plan.md), a idealno agentu omogućiti pristup celom
repozitorijumu. PDF primer i PIR prezentacija služe za formu i akademsku
organizaciju, ne kao izvori čiji sadržaj treba prepisivati.

## Prompt

```text
Radiš na izradi nulte verzije Primenjenog istraživačkog rada (PIR) za master
studije iz predmeta „Programiranje u integrisanim tehnologijama“.

Tema rada je:
„Платформа за издавање смештаја са подршком у одлучивању реализована у
интегрисаним технологијама“.

Primarni projektni dokument je priloženi `docs/01-project/pir-plan.md`. Pročitaj
ga u celini pre pisanja. Njegove potvrđene odluke tretiraj kao obavezne, osim
ako pronađeš direktnu kontradikciju sa zvaničnim uputstvom ili stvarnim kodom.

Ako imaš pristup repozitorijumu, najpre pročitaj važeći `AGENTS.md`, proveri
`git status --short` i ne menjaj niti briši postojeće korisničke izmene. Za
tehničke tvrdnje izvor istine su, ovim redosledom:

1. kod, migracije, konfiguracija i automatizovani testovi;
2. kanonska dokumentacija repozitorijuma;
3. `docs/01-project/pir-plan.md` kao izvor akademskog obima i dogovorenih
   metodoloških odluka;
4. naučna literatura i zvanična tehnička dokumentacija;
5. priloženi primer PIR-a samo kao obrazac strukture i stila.

Ne pretpostavljaj da je dokumentacija tačna ako je kod demantuje. Ne
predstavljaj planirano ili eksperimentalno kao implementirano. Posebno proveri
preporuke, pretragu, uloge, API, bazu podataka i integracije. Postojeći sistem
preporuka je heuristički i personalizovan, ali nije trenirani model mašinskog
učenja.

ZADATAK

Izradi akademski utemeljenu nultu verziju kompletnog PIR rada. Glavni tekst
piši na srpskom jeziku, ćirilicom. Nazive klasa, API ruta, biblioteka,
tehnologija, standarda i druge tehničke identifikatore ostavi u izvornom
obliku. Ciljani obim je 30–35 strana, a dozvoljeno je najviše 35–40 strana ako
su dodatne strane opravdane metodologijom, rezultatima, dijagramima ili
stručnim doprinosom. Ne popunjavaj obim opštim opisima tehnologija.

Rad treba da bude usmeren na dugoročni najam u Srbiji. Platformu predstavi iz
perspektive tražioca i izdavaoca, ali primarno istraživanje, hipoteze i
eksperiment ograniči na podršku tražiocu pri izboru smeštaja. Rangiranje
kandidata za izdavaoca označi kao planirano proširenje, osim ako kod i testovi
nedvosmisleno potvrde da je implementirano.

Istraživačka osnova:

- opšta hipoteza: transparentno rangiranje povećava usklađenost izbora sa
  korisničkim preferencijama;
- H1: hibridno rangiranje sa eksplicitnim težinama i signalima ponašanja daje
  usklađenije prve rezultate od bazne pretrage bez personalizovanog redosleda;
- H2: rangiranje sa objašnjenjem smanjuje broj oglasa koje korisnik pregleda do
  donošenja odluke;
- H3: prikaz skora, doprinosa kriterijuma i objašnjenja poboljšava
  upotrebljivost i poverenje u izbor;
- preporučena metoda: tvrdi filteri za obavezne uslove, zatim normalizovana
  ponderisana suma koja kombinuje eksplicitne težine i postojeće signale
  ponašanja;
- tehnička kontrolna varijanta: postojeća heuristika preporuka;
- UI kontrolna varijanta: postojeća pretraga i filteri bez rangiranja po
  eksplicitnim preferencijama;
- uzorak: 10–20 ispitanika;
- dizajn: svaki ispitanik koristi baznu i unapređenu varijantu, uz
  kontrabalansiran redosled;
- instrumenti: objektivne metrike zadatka, SUS, tri dogovorene Likert tvrdnje i
  jedno otvoreno pitanje;
- analiza: deskriptivna statistika i transparentan prikaz pojedinačnih ili
  agregiranih rezultata, bez preuveličavanja generalizacije.

OBAVEZNA STRUKTURA

1. Naslovna strana sa jasno označenim placeholder-ima za podatke koje nisi
   dobio.
2. Sadržaj.
3. Uvod: predmet, praktični problem, cilj, značaj, opšta i specifične hipoteze,
   metodologija i organizacija rada.
4. Teorijske osnove i pregled srodnih istraživanja: sistemi podrške u
   odlučivanju, višekriterijumsko odlučivanje, sistemi preporuka, objašnjive
   preporuke, izbor smeštaja i integrisane web tehnologije.
5. Analiza problema i zahteva: akteri, odluka, kriterijumi, podaci,
   funkcionalni i nefunkcionalni zahtevi i granice istraživanja.
6. Projektovanje sistema: arhitektura, komponente, model podataka, API i tok
   podataka kroz Vue, Laravel i PostgreSQL, uz relevantne integracije.
7. Postojeće stanje i predlog unapređenja: precizno razdvoji implementirano,
   eksperimentalno i planirano; objasni postojeću heuristiku i predloženi
   hibridni model.
8. Metodologija evaluacije: uzorak, scenariji, varijante, kontrabalansiranje,
   instrument saglasnosti, zadaci, metrike, SUS bodovanje, Likert tvrdnje,
   postupak analize, privatnost i etičke mere.
9. Rezultati i diskusija.
10. Analiza stručnog doprinosa.
11. Ograničenja i pretnje internoj, eksternoj i konstruktivnoj validnosti.
12. Zaključak i pravci budućeg rada.
13. Literatura.
14. Prilozi: upitnik, zadaci ispitanika, obrazac saglasnosti i predlozi tabela,
    ako su prilozi dozvoljeni.

PRAVILA ISTRAŽIVANJA I IZVORA

- Pre pisanja teorijskog dela pronađi relevantne recenzirane naučne izvore.
- Prednost daj originalnim radovima, preglednim radovima, knjigama priznatih
  izdavača, standardima i zvaničnoj dokumentaciji tehnologija.
- Za tehnička pitanja koristi primarne izvore: zvaničnu dokumentaciju i sam
  kod, ne marketinške blogove i agregatore.
- Proveri autora, naslov, godinu, izdavača/časopis, strane, DOI ili stabilan
  URL. Ne izmišljaj bibliografske jedinice, DOI brojeve, citate ni strane.
- Ne koristi Wikipedia tekst kao akademski izvor.
- Parafraziraj izvore i jasno označi tuđe ideje; ne prepisuj pasuse iz primera
  PIR-a ili literature.
- Koristi fusnote u skladu sa pravilima iz priložene PIR prezentacije, a na
  kraju navedi potpunu bibliografiju. Svaka fusnota mora odgovarati stvarnom
  izvoru u literaturi.
- Svaka slika i tabela mora imati broj, naziv, izvor i referencu u tekstu.
- Ako ne možeš proveriti izvor, označi `[IZVOR ZA PROVERU]` umesto da ga
  izmisliš.

ZABRANA FABRIKOVANJA REZULTATA

Korisničko istraživanje još nije sprovedeno. Ne izmišljaj ispitanike, rezultate,
SUS skorove, vremena, procente, statističku značajnost ili potvrdu hipoteza.
Odeljak rezultata pripremi kao šablon sa:

- opisom podataka koje treba prikupiti;
- praznim tabelama i predlogom grafikona;
- pravilima računanja metrika;
- oznakom `[DOPUNITI NAKON ISTRAŽIVANJA]` na svakom mestu gde nedostaje stvarni
  nalaz;
- uslovnim smernicama za diskusiju, bez unapred donetog zaključka.

Ako unapređena metoda još nije implementirana, njen opis označi kao predlog i
navedi tačne korake potrebne za implementaciju i testiranje. Ne piši da je
hipoteza potvrđena pre dobijanja rezultata.

NAČIN RADA I ISPORUKA

1. Najpre napravi kratku matricu dokaza: važna tvrdnja, izvor u repozitorijumu
   ili literaturi i status `potvrđeno / planirano / nedostaje podatak`.
2. Zatim navedi najviše deset stvarnih blokera. Ne postavljaj pitanja čiji se
   odgovor nalazi u planu, kodu ili dokumentaciji. Ako podaci za naslovnu stranu
   nedostaju, koristi jasne placeholder-e i nastavi.
3. Predloži detaljan sadržaj sa procenjenim brojem strana, vodeći računa o
   maksimumu od 35–40 strana.
4. Izradi nultu verziju rada po poglavljima. Svako poglavlje mora imati svrhu i
   doprinos argumentu rada.
5. Na kraju dostavi:
   - listu svih korišćenih i proverenih izvora;
   - listu tvrdnji koje još zahtevaju proveru;
   - tabelu `implementirano / eksperimentalno / planirano`;
   - kompletan protokol korisničkog istraživanja;
   - listu svih `[DOPUNITI ...]` oznaka;
   - kontrolnu listu usklađenosti sa PIR prezentacijom.

Ako radiš direktno u repozitorijumu, nultu verziju sačuvaj kao novi radni
dokument; ne prepisuj `pir-plan.md`, PDF primer, prezentaciju ni postojeći
sažetak. Pre izmene proveri lokalna dokumentaciona pravila, a nakon izmene
pokreni propisane provere linkova i prijavi svako ograničenje okruženja.
```

## Materijali koje treba poslati agentu

Minimalni paket:

1. ovaj prompt;
2. [`pir-plan.md`](pir-plan.md);
3. [`master-rad-sazetak.md`](master-rad-sazetak.md);
4. `Алекса-Матејић-РИН-33-17-ПИР.pdf`;
5. `PIR - Primenjeni istraživački rad PREZENTACIJA v.23.2.pptx`.

Preporučeni paket je ceo repozitorijum, jer jedino tada agent može pouzdano da
proveri šta je implementirano i da navede kod, testove i konfiguraciju kao
dokaze. Ako repozitorijum nije dostupan, promptu treba dodati upozorenje da
agent ne sme da zaključuje implementacioni status samo iz projektnog sažetka.
