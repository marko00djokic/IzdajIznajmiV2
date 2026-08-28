# PIR — plan rada i metodološke odluke

**Tema:** „Платформа за издавање смештаја са подршком у одлучивању
реализована у интегрисаним технологијама“  
**Predmet u okviru kog se razvija aplikacija:** Programiranje u integrisanim
tehnologijama  
**Status:** odgovori evidentirani; otvorene metodološke odluke čekaju potvrdu<br>
**Datum analize:** 27. avgust 2026.<br>
**Izvori za ovu fazu:** primer PIR rada „Алекса-Матејић-РИН-33-17-ПИР.pdf“,
uputstvo „PIR - Primenjeni istraživački rad PREZENTACIJA v.23.2.pptx“,
[`master-rad-sazetak.md`](master-rad-sazetak.md) i postojeća projektna
dokumentacija

## Namena dokumenta

Ovaj dokument objedinjuje zaključke analize, potvrđene odgovore studenta i
preostale metodološke odluke za izradu nulte verzije PIR-a. Prvobitni upitnik je
uklonjen nakon evidentiranja odgovora: više nije aktivan radni instrument, a
njegovo zadržavanje bi dupliralo odluke navedene u nastavku.

Nepotvrđeni administrativni podaci i otvorene odluke označeni su eksplicitno i
ne smeju se izmišljati. Plan i nulta verzija moraju biti potvrđeni sa predmetnim
nastavnikom i mentorom.

## Zaključci iz analize materijala

### Formalni okvir iz prezentacije

Prezentacija definiše PIR kao rešavanje praktičnog problema iz oblasti
studijskog programa, povezano sa budućim master radom i praktičnom realizacijom
u organizaciji. Predviđa prethodno usaglašavanje teme i plana između studenta,
mentora i predmetnog nastavnika, kao i odobrenje mentora pre predaje i odbrane.

Propisana struktura obuhvata naslovnu stranu, sadržaj, uvod, obradu teme,
analizu rezultata, analizu stručnih doprinosa, zaključak i literaturu. U uvodu
treba navesti predmet, cilj, značaj teme i obrazloženu metodologiju. Poseban
analitički deo treba da razmotri rezultate, stručni doprinos, ispunjenost opšte
i specifičnih hipoteza, primenjivost i ograničenja rezultata.

Preporučena forma je 20–30 strana formata A4, margine 2,5 cm, prored 1,5 i
Times New Roman 12. Izvori se citiraju u fusnotama; slike i tabele moraju imati
broj, naziv, izvor i jasnu referencu u tekstu. Literatura mora sadržati potpune
bibliografske podatke, uz poželjnu upotrebu relevantnih izvora na stranim
jezicima i izbegavanje popularnih wiki izvora.

### Šta je korisno preuzeti iz primera, a šta ne

Primer je koristan kao obrazac akademske organizacije: od problema i teorijske
osnove prelazi ka izboru tehnologija, realizaciji, prikazu i analizi rezultata,
doprinosu, ograničenjima, zaključku i literaturi. Korisni su i numerisanje
celina, povezivanje slika sa tekstom i odvajanje opisa realizacije od procene
dobijenih rezultata.

Njegovu konkretnu temu, hipoteze, metode, hardversko-softverske komponente,
rezultate i bibliografiju ne treba preslikavati. Naš rad mora da polazi od
problema izbora smeštaja i integracije web tehnologija, a svaki navod o
IzdajIznajmiV2 mora da bude proveren prema kodu, testovima i konfiguraciji.

### Polazna specifičnost naše teme

Postojeći sažetak dobro opisuje marketplace i širok skup integracija, ali naslov
PIR-a uvodi uži istraživački fokus: **podršku u odlučivanju**. Zato konačni rad
ne bi trebalo da bude samo katalog funkcija ili opis implementacije. Potrebno
je precizirati:

1. ko donosi odluku (tražilac smeštaja, izdavalac ili oba aktera);
2. koju odluku sistem podržava;
3. na osnovu kojih kriterijuma i podataka;
4. koja metoda ili postupak daje rangiranje, preporuku ili jasnije poređenje;
5. kako se meri da li je podrška korisna;
6. koji deo postoji u aplikaciji, a koji je predmet PIR eksperimenta ili
   planiranog nastavka master rada.

Bez ovih odluka tvrdnja „sa podrškom u odlučivanju“ ostala bi nedovoljno
operacionalizovana i teško bi se proveravale hipoteze.

## Potvrđene odluke i metodološka razrada

### Radna specifikacija

Na osnovu potvrđenih odluka, radna specifikacija PIR-a je:

| Oblast | Odluka |
| --- | --- |
| Naslov | Konačno odobren, u navedenom ćiriličnom obliku |
| Tekst rada | Srpska ćirilica, uz izvorne tehničke termine |
| Naslovna strana | Model analiziranog primera, bez preuzimanja njegovog sadržaja |
| Poznati podaci | Potvrđeni su student, broj indeksa, mentor i predmetni nastavnik |
| Organizacija | Postoji; mentor iz organizacije ili sporazum još nije potvrđen |
| Prva predaja | Predmetnom nastavniku PIR-a |
| Obim i format | Ciljano 30–35, a najviše 35–40 strana ako sadržaj to opravdava; DOCX za reviziju, PDF za konačnu predaju |
| Citiranje | Fusnote prema pravilima iz prezentacije |
| Rok | Prva kompletna verzija u roku do dve nedelje |
| Tržište | Dugoročni najam u Srbiji |
| Primarni cilj | Veća usklađenost izabrane ponude sa preferencijama korisnika |
| Sekundarni cilj | Upotrebljivost korisničkog interfejsa |
| Van obima | Produkciono skaliranje na veliki broj korisnika |
| Objašnjenje rezultata | Skor, doprinos kriterijuma i kratko tekstualno objašnjenje |
| Bazna varijanta | Postojeća pretraga i filteri bez rangiranja po preferencijama |
| Podaci | Kombinacija sintetičkih i dozvoljenih realnih podataka |
| Evaluacija | Tehnički test i malo korisničko istraživanje sa 10–20 ispitanika |
| Eksperimentalni dizajn | Svaki ispitanik koristi obe UI varijante, uz promenjen redosled |
| Obrada rezultata | Deskriptivna statistika i transparentan prikaz rezultata |
| Etičke mere | Informisani pristanak, anonimni podaci i pravo na odustajanje |
| Arhitektonski fokus | Vue klijent, Laravel REST API i PostgreSQL |
| Dijagrami | Komponentni, sekvencni i model podataka, svaki sa posebnom svrhom |
| Status funkcionalnosti | Tabela „implementirano / eksperimentalno / planirano“ |
| Literatura | Naučni izvori za tvrdnje i primarni tehnički izvori za implementaciju |
| Sledeći artefakt | Nulta verzija kompletnog PIR rada |

Izbor dugoročnog najma predstavlja istraživački podskup šire platforme. U PIR-u
zato treba jasno navesti da platforma podržava i druge periode najma, ali da se
eksperiment, kriterijumi i zaključci odnose samo na dugoročni najam u Srbiji.

### Važno razjašnjenje: mehanizam preporuka već postoji

Prvobitna pretpostavka da mehanizam ne postoji nije potvrđena kodom. Trenutna aplikacija već ima implementiran mehanizam personalizovanih
preporuka za tražioca smeštaja:

- API ruta `GET /api/v1/recommendations` dostupna je ulozi `seeker` i
  administratoru;
- profil preferencija izvodi se iz pregledanih oglasa, sačuvanih pretraga i
  skorijih snimaka filtera;
- kandidati dolaze iz sličnih oglasa, sačuvanih i skorijih pretraga i novih
  oglasa u preferiranom gradu;
- heuristički skor koristi grad, cenovni opseg, broj soba, površinu, pogodnosti,
  svežinu oglasa i izvor kandidata;
- rezultat sadrži kratke razloge preporuke;
- endpoint je povezan sa frontend servisom i pokriven API testovima.

To **nije model mašinskog učenja**: nema treniranja modela, naučenih parametara,
validacionog skupa ni predikcije naučenog modela. To je sadržajno i ponašajno
personalizovana, pravilima zasnovana ponderisana heuristika. Zbog toga PIR ne
treba da tvrdi da će mehanizam biti napravljen od nule. Ispravan opis početnog
stanja je:

> Postoji heuristički personalizovani sistem preporuka za tražioca smeštaja,
> ali nema korisnički podesive težine kriterijuma, formalizovanu
> višekriterijumsku metodu, eksperimentalno poređenje sa baznom pretragom ni
> evaluaciju usklađenosti preporuka sa eksplicitnim preferencijama.

PIR može da doprinese formalizovanjem i unapređenjem ovog mehanizma, uvođenjem
eksplicitnih težina, transparentnijeg prikaza skora i kontrolisanom evaluacijom.

### Specifične hipoteze — značenje i predlog

Potvrđena opšta hipoteza glasi:

> Transparentno rangiranje povećava usklađenost izbora sa korisničkim
> preferencijama.

**Specifične hipoteze** razlažu ovu široku tvrdnju na nekoliko užih, merljivih
tvrdnji. Svaka mora da poveže konkretnu promenu sistema sa rezultatom koji se
može izmeriti. One nisu dodatne funkcionalnosti niti unapred dokazani zaključci.

Za ovaj PIR predlažu se tri specifične hipoteze:

- **H1 — kvalitet rangiranja:** rangiranje koje kombinuje eksplicitno zadate
  težine i postojeće signale ponašanja daje veću usklađenost prvih rezultata sa
  zadatim preferencijama nego postojeća pretraga i filtriranje bez
  personalizovanog redosleda.
- **H2 — efikasnost odluke:** korisnici uz rangiranje i objašnjenje rezultata
  završavaju zadatak izbora sa manje pregledanih oglasa nego uz baznu varijantu.
- **H3 — upotrebljivost i poverenje:** korisnici bolje ocenjuju upotrebljivost
  i poverenje u izbor kada vide ukupan skor, doprinos kriterijuma i kratko
  objašnjenje nego kada koriste samo baznu listu rezultata.

Preporuka je da se zadrže **tri** specifične hipoteze. One pokrivaju kvalitet
odluke, efikasnost i korisničku percepciju, a još su izvodljive za rad do 35–40
strana i uzorak od 10–20 ispitanika. H3 treba tumačiti oprezno: SUS meri
upotrebljivost, dok se poverenje meri odvojenim Likert tvrdnjama.

### Nezavisne i zavisne promenljive

Dogovoreni obim uključuje i prisustvo mehanizma i izbor algoritma. To su dve
različite nezavisne promenljive i njihovo istovremeno menjanje u jednom
dvovarijantnom korisničkom testu ne bi pokazalo koja promena je izazvala
rezultat. Predlaže se razdvajanje evaluacije u dve faze:

1. **Tehnička evaluacija algoritma:** na istom skupu oglasa porediti postojeću
   fiksnu heuristiku i unapređenu hibridnu ponderisanu metodu.
2. **Korisnička evaluacija interfejsa:** porediti baznu pretragu bez podrške i
   unapređenu varijantu sa rangiranjem i objašnjenjem.

U korisničkom testu glavna nezavisna promenljiva je zato **varijanta interfejsa
i podrške** (`bazna` / `unapređena`), a primarna zavisna promenljiva je **ocena
usklađenosti izabrane ponude sa unapred zadatim preferencijama**. Sekundarne
zavisne promenljive su broj pregledanih oglasa, vreme izvršenja, uspešnost
zadatka, SUS rezultat i ocena poverenja.

### Objašnjenje mogućih metoda rangiranja

#### 1. Ponderisana suma

Svaki kriterijum se svodi na uporedivu skalu, na primer od 0 do 1, množi se
njegovom težinom i sabira u ukupan skor. Metoda je laka za implementaciju i
objašnjenje: korisniku se može pokazati koliko su cena, lokacija ili pogodnosti
doprinele rezultatu. Slabost je što normalizacija i težine moraju biti pažljivo
obrazložene, a veoma dobar rezultat po jednom kriterijumu može nadoknaditi loš
rezultat po drugom ako obavezni uslovi nisu prethodno filtrirani.

#### 2. AHP i TOPSIS

`AHP` (Analytic Hierarchy Process) određuje težine tako što korisnik ili ekspert
poredi kriterijume u parovima, na primer da li je cena važnija od lokacije i
koliko. `TOPSIS` zatim rangira oglase prema udaljenosti od zamišljene idealne i
najlošije alternative. Metoda ima dobro akademsko uporište i pogodna je za
formalni višekriterijumski rad, ali parna poređenja postaju naporna kada ima
mnogo kriterijuma, a implementacija i objašnjenje su složeniji.

#### 3. Pravila i pragovi

Sistem primenjuje pravila kao što su „cena ne sme preći budžet“ ili „oglas mora
biti u izabranom gradu“. Veoma je transparentan i dobar za obavezne uslove, ali
sam po sebi ne daje fin redosled među svim prihvatljivim ponudama i teško
personalizuje kompromise.

#### 4. Mašinsko učenje

Model uči obrasce iz istorijskih interakcija, izbora ili ocena korisnika. Može
da pronađe složene odnose koje ručno definisana pravila ne vide, ali zahteva
dovoljno kvalitetnih istorijskih podataka, jasno označen cilj, podelu na skupove
za obuku i proveru i kontrolu pristrasnosti. Sa 10–20 ispitanika i pretežno
sintetičkim podacima ne bi bilo metodološki opravdano tvrditi da je obučen
pouzdan personalizovani ML model.

#### Preporuka za ovaj PIR

Najprikladniji je **hibrid pravila i ponderisane sume**:

1. stvarno obavezne uslove tretirati kao tvrde filtere, a ne samo kao veoma
   velike težine;
2. preostale alternative bodovati normalizovanom ponderisanom sumom;
3. težine kombinovati iz eksplicitnog korisničkog unosa i postojećih signala
   ponašanja;
4. prikazati ukupan skor, doprinose kriterijuma i kratko objašnjenje;
5. postojeću heuristiku koristiti kao tehničku kontrolnu varijantu.

Ovaj pristup koristi već implementirane signale, dovoljno je transparentan za
hipoteze i realno je izvodljiv u roku. Mašinsko učenje može se predstaviti kao
budući rad kada platforma prikupi dovoljan, zakonito obrađen skup interakcija.
AHP/TOPSIS može se opisati kao razmotrena alternativa ili koristiti u manjoj
offline analizi, ali nije potreban za prvu verziju eksperimenta.

### Instrumenti za procenu upotrebljivosti

- **SUS (System Usability Scale)** ima deset tvrdnji sa odgovorima na skali od
  1 do 5 i daje zbirni rezultat od 0 do 100. Kratak je, široko korišćen i
  pogodan za poređenje dve varijante, ali ne objašnjava sam po sebi zašto je
  korisnik dao određenu ocenu i ne meri direktno poverenje u preporuku.
- **UEQ-S (kratki User Experience Questionnaire)** ima osam parova suprotnih
  prideva i razdvaja pragmatični kvalitet (jasnoća, efikasnost) od hedoničkog
  kvaliteta (zanimljivost, podsticajnost). Koristan je kada je celokupan doživljaj
  važniji od same upotrebljivosti, ali je manje usmeren na uspeh konkretnog
  zadatka odlučivanja.
- **TAM (Technology Acceptance Model)** ispituje percipiranu korisnost, lakoću
  korišćenja i nameru prihvatanja tehnologije. Pogodan je za pitanje da li bi
  korisnici prihvatili sistem, ali je širi i zahtevniji od potreba malog PIR
  eksperimenta.
- **Sopstveni upitnik** može direktno da pita da li je objašnjenje bilo jasno i
  da li korisnik veruje rangiranju. Fleksibilan je, ali nema validiran zbirni
  rezultat i ne treba da bude jedini instrument.

Za ovaj PIR preporučuje se **SUS kao glavni standardizovani instrument**, uz
objektivne metrike zadatka i tri dopunske Likert tvrdnje:

1. „Rangiranje odgovara preferencijama zadatim u scenariju.“
2. „Razumem zašto su prikazani oglasi dobili svoje pozicije.“
3. „Imao/la bih poverenja da ovaj prikaz koristim pri užem izboru smeštaja.“

Na kraju treba dodati jedno otvoreno pitanje: „Šta je najviše pomoglo, a šta je
otežalo izbor?“ UEQ-S ili TAM ne treba dodavati u istom malom istraživanju jer
bi produžili test bez jasne koristi za izabrane hipoteze.

### Otvorena kontradikcija obima: više ravnopravnih uloga

Potvrđeni obim bira oba aktera, više ravnopravnih uloga i kombinaciju više
odluka. Međutim, potvrđeni kod trenutno personalizuje oglase samo za tražioca,
dok za izdavaoca ne postoji uporediv mehanizam rangiranja kandidata. Dva
ravnopravna mehanizma, dva skupa kriterijuma i dve korisničke populacije teško
staju i u maksimalnih 35–40 strana i rok od dve nedelje.

Predlog za nultu verziju je sledeći:

- platformu i problem opisati iz perspektive oba aktera;
- **primarno istraživanje i sve tri hipoteze ograničiti na odluku tražioca o
  izboru dugoročnog smeštaja**;
- odluku izdavaoca o izboru kandidata analizirati kroz zahteve i prikazati kao
  planirano proširenje, bez tvrdnje da je implementirana ili empirijski
  potvrđena.

Ako obe uloge zaista moraju biti ravnopravno implementirane i testirane, treba
produžiti rok, povećati obim ili smanjiti broj kriterijuma i hipoteza. Ova odluka
mora biti potvrđena pre finalizacije nulte verzije.

### Predloženi dalji tok izrade

1. Zaključati administrativne podatke, jezik/pismo, rok i pravila citiranja.
2. Jednom rečenicom definisati praktični problem, predmet i cilj istraživanja.
3. Izabrati jednog primarnog donosioca odluke i jednu merljivu odluku.
4. Napraviti pregled literature o marketplace sistemima, višekriterijumskom
   odlučivanju, rangiranju/preporukama i integrisanim web tehnologijama.
5. Evidentirati početno stanje i ograničenja postojećeg sistema.
6. Definisati opštu i specifične hipoteze, nezavisne/zavisne promenljive,
   uzorak, zadatke, metrike i kriterijume prihvatanja.
7. Realizovati ili jasno izdvojiti eksperimentalni mehanizam podrške u
   odlučivanju i dokumentovati arhitekturu integracija.
8. Prikupiti rezultate, obraditi ih tabelarno/grafički i odvojiti nalaze od
   tumačenja.
9. Kritički proceniti hipoteze, stručni doprinos, primenjivost, pretnje
   validnosti i ograničenja.
10. Tek na kraju finalizovati uvod, zaključak, sažetak, slike, fusnote i spisak
    literature.

### Radni predlog strukture PIR-a

Raspodela je orijentaciona i mora se uskladiti sa nastavnikom:

1. **Naslovna strana** — podaci koje zahteva ustanova.
2. **Sadržaj**.
3. **Uvod** (2–3 strane) — predmet, problem, cilj, značaj, hipoteze, metode i
   organizacija rada.
4. **Teorijske osnove i pregled srodnih rešenja** (5–6 strana) — platforme za
   smeštaj, podrška u odlučivanju i relevantne integrisane tehnologije.
5. **Analiza problema i zahteva** (3–4 strane) — akteri, scenariji odluke,
   kriterijumi, izvori podataka i funkcionalni/nefunkcionalni zahtevi.
6. **Projektovanje integrisanog sistema** (4–5 strana) — arhitektura,
   komponente, tok podataka, API i obrazloženje tehnoloških izbora.
7. **Realizacija prototipa/platforme** (5–6 strana) — samo delovi relevantni za
   istraživačko pitanje, sa jasnim odvajanjem postojećeg i novog rada.
8. **Metodologija evaluacije** (3–4 strane) — uzorak ili skup podataka,
   instrument, procedure, metrike i etičke mere.
9. **Rezultati i diskusija** (4–5 strana) — nalazi, provera hipoteza,
   poređenje, primenjivost i ograničenja.
10. **Analiza stručnog doprinosa** (1–2 strane).
11. **Zaključak i budući rad** (1–2 strane).
12. **Literatura** i, ako su dozvoljeni, **prilozi**.

Ukupan obim treba da bude posledica sadržaja, ne cilj sam po sebi. Uputstvo iz
prezentacije preporučuje 20–30 strana, dok je za ovaj rad prihvatljiv radni
raspon 30–35 i najviše 35–40 strana kada dodatni obim nose metodologija,
rezultati, dijagrami i stručni doprinos, a ne opšti opisi tehnologija.

## Handoff za izradu nulte verzije

AI agentu uz ovaj plan treba dostaviti i
[`pir-ai-prompt.md`](pir-ai-prompt.md). Plan je autoritativan za dogovoreni
akademski obim, ali nije izvor istine za implementirano ponašanje aplikacije.
Agent mora proveriti tehničke tvrdnje prema kodu, testovima i konfiguraciji,
koristiti relevantne naučne i primarne tehničke izvore i jasno označiti sve
što nije potvrđeno.

Nulta verzija nije isto što i izmišljena završna verzija: odeljci koji zavise od
buduće implementacije ili još nesprovedenog eksperimenta moraju sadržati plan,
instrument, očekivani oblik tabele i oznaku `[DOPUNITI NAKON ISTRAŽIVANJA]`, a
ne fabrikovane rezultate, brojke, ispitanike ili zaključke o hipotezama.
