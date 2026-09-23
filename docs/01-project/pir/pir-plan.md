# PIR — plan rada i metodološke odluke

**Tema:** „Платформа за издавање смештаја са подршком у одлучивању
реализована у интегрисаним технологијама“  
**Predmet u okviru kog se razvija aplikacija:** Programiranje u integrisanim
tehnologijama  
**Status:** aktivan plan nastavka posle verzije 0.1; metod i formalna predaja još nisu odobreni<br>
**Datum provere:** 23. septembar 2026.<br>
**Presek nacrta:** verzija 0.1 od 3. septembra 2026, tehnički dokazi na commit-u `d1489fb9a80bf8444bd6564768a5cbab14f28588`<br>
**Izvori za ovu fazu:** [primer PIR rada](sources/pir-primer-aleksa-matejic.pdf),
[prezentacija sa uputstvom](sources/pir-uputstvo-prezentacija-v23-2.pptx),
[`master-rad-sazetak.md`](master-rad-sazetak.md) i postojeća projektna
dokumentacija

## Namena dokumenta

Ovaj dokument je kanonski plan daljeg razvoja PIR-a. Ranije odluke niže u
dokumentu objašnjavaju njihov nastanak, ali [verzija 0.1](pir-verzija-0-1.md)
i njeni [istraživački prilozi](research/README.md) određuju stanje nacrta.
Za implementirano ponašanje merodavni su kod, testovi i konfiguracija.
[Nulta verzija](pir-nulta-verzija.md) i [prompt za nju](pir-ai-prompt.md) su
istorijski radni materijali, ne aktivni nalog za novu nultu verziju.

Nepotvrđeni administrativni podaci i otvorene odluke označeni su eksplicitno i
ne smeju se izmišljati. Plan i sledeća verzija moraju biti usaglašeni sa
mentorom i predmetnim nastavnikom.

## Status na dan provere

| Oblast | Utvrđeno stanje | Trag |
| --- | --- | --- |
| Tekst | Verzija 0.1 je celovit pre-eksperimentalni nacrt za mentorski pregled: problem, RQ1–RQ3, H1–H3, literatura, model, metod i ograničenja. Nema potvrđenih rezultata glavnog istraživanja. | [Nacrt](pir-verzija-0-1.md) |
| Literatura | Matrica sadrži 24 upotrebljena izvora; deo metapodataka traži završnu nezavisnu proveru. | [Matrica literature](research/literature-matrix.md) |
| Tehnički dokaz | Postojeća heuristika preporuka je statički potvrđena. Model sa eksplicitnim težinama nije dokazan kao implementiran. Runtime baseline nije uspeo: Laravel testovi nisu pokrenuti; `backend/vendor` nedostaje i pri ovoj proveri. | [Matrica dokaza](research/evidence-matrix.md), [baseline](research/technical-baseline.md) |
| Istraživanje | U prilozima nema popunjenog dataset-a, fiksiranih scenarija, nezavisnih relevance ocena, pilota ili korisničkih rezultata. | [Scenariji](research/scenarios.md), [instrumenti](research/instruments.md) |
| Predaja | Nema DOCX/PDF verzije 0.1 sa proverljivom paginacijom, sadržajem i fusnotama, ni evidentiranog odobrenja mentora i nastavnika. | PIR direktorijum i [uputstvo](sources/pir-uputstvo-prezentacija-v23-2.pptx) |

Ovo je status dokumenata, ne tvrdnja da se van repozitorijuma ništa nije
desilo. Pre 0.2 ponovo proveriti kod i prikupiti postojeće spoljne potvrde.

## Obavezni okvir rada

- **Tema i jezik:** naslov koji je student potvrdio i koji stoji u 0.1;
  glavni tekst na srpskoj ćirilici, tehnički identifikatori u izvornom obliku.
- **Granica:** dugoročni najam u Srbiji; tražilac bira užu listu oglasa.
  Izdavalac je kontekst platforme, a rangiranje kandidata za njega nije deo
  H1–H3. Ne tvrditi da postoji trenirani ML model ili produkciona skala.
- **Argument:** praktični problem → teorija → postojeći sistem i nedostaci →
  hibrid tvrdih filtera i ponderisane sume → tehnička i korisnička evaluacija →
  rezultati, doprinos, primenjivost i ograničenja. Opis funkcija služi argumentu.
- **Celine iz uputstva:** naslovna strana, sadržaj, uvod sa predmetom, ciljem,
  značajem i metodologijom, obrada teme, zasebna analiza rezultata i stručnih
  doprinosa sa proverom hipoteza, zaključak i literatura. Slike i tabele imaju
  broj, naziv, pun izvor i pozivanje iz teksta.
- **Forma:** priložena prezentacija preporučuje **20–30 A4 strana**, margine
  2,5 cm, prored 1,5, Times New Roman 12 i citate u fusnotama. Raniji cilj
  30–35, odnosno do 40 strana, nije potvrđeno odstupanje od uputstva. Dok
  nastavnik ne odobri duži obim, planirati 20–30 strana.
- **Odobravanje:** mentor i predmetni nastavnik odobravaju temu i plan. Gotov
  rad se prvo šalje mentoru, pa posle njegovog odobrenja nastavniku. Uputstvo
  zahteva mentora iz organizacije i saradnju sa organizacijom; proveriti ime,
  kvalifikaciju i osnov saradnje pre formalne predaje.
- **Dokazi:** svaka tehnička tvrdnja ima kod/test/config i commit; svaka
  empirijska tvrdnja ima stvaran dataset, protokol i rezultat. Razdvojiti
  `planned`, `experimental`, `implemented`, `not run` i `approved`. Ne
  proglašavati H1–H3 potvrđenim pre merenja.

## Plan nastavka i uslovi prelaska

| Faza | Posao i artefakt | Uslov za prelazak |
| --- | --- | --- |
| Pre 0.2: usaglašavanje | Mentoru dostaviti granicu, H1–H3, scenarije, obim i eksperiment. Zabeležiti odluke o planu, obimu/prilozima, organizaciji i mentoru u njoj, radu sa učesnicima i protokolu podataka. | Otvorene tačke su jasno zapisane. Tehnički rad može teći nezavisno, a rad sa učesnicima i formalna predaja čekaju potrebne potvrde. |
| Pre 0.2: metod | Uskladiti nacrt i priloge: 0.1 predlaže `λ=0,25` i četiri odvojene Likert stavke; nulta verzija predlaže `λ=0,20`, a stari prompt/plan tri stavke. Pre merenja zaključati parametar, H1–H3, metrike i instrument. Rešiti postupak za razliku od jednog ordinalnog stepena između dva ocenjivača: medijana dve ocene može biti necelobrojna. | Jedan protokol bez protivurečnih vrednosti i naknadnog izbora parametra prema ishodu. |
| 0.2: tehnički rad | U izolovanom okruženju instalirati zaključane zavisnosti bez izmene lock fajlova i pokrenuti ciljane Laravel provere. Zamrznuti dozvoljen/sintetički dataset sa poreklom i SHA-256, S1/S2 i hard filtere. Napraviti offline adapter za hibrid, testirati filtere, normalizaciju, missing podatke, tie-break i determinističnost. Na istim kandidatima uporediti bazni redosled, postojeću heuristiku i hibrid; sačuvati verzije, parametre i rank liste. | Komande, test log, checksum i reproduktivni izlazi postoje. H1 se analizira tek uz nezavisne relevance ocene. |
| 0.2: tekst | Osvežiti tehničku matricu na novom commit-u, proveriti bibliografske zapise, dodati dijagrame komponenti, sekvence i podataka sa zasebnom svrhom. Pripremiti DOCX za pregled sa stvarnim sadržajem i fusnotama. | Nacrt odvaja postojeće, eksperimentalno i planirano; obim i forma su pregledani prema odobrenom okviru. |
| 0.3: pilot | Pripremiti izolovanu A/B UI varijantu i minimalni logging. Proveriti prevod SUS-a, saglasnost, zadatke, četiri sekvence, 12–18 kandidata po scenariju i težinu S1/S2. Sačuvati pilot izveštaj i reviziju protokola. | Protokol glavnog istraživanja je zamrznut; pilot se ne predstavlja kao glavni nalaz. |
| 0.4: istraživanje | Rekrutovati 10–20 punoletnih dobrovoljaca po odobrenom protokolu; sprovesti obe varijante i nezavisno ocenjivanje. Prikazati pojedinačne/parne razlike, medijanu i IQR; SUS i poverenje odvojeno. | Anonimizovani agregati i proverljiva analiza postoje; H1–H3 se tumače uz ograničenja uzorka. |
| 1.0: predaja | Doraditi diskusiju, doprinos i ograničenja; proveriti citate, tabele, slike, fusnote, paginaciju i priloge. Poslati mentoru, uneti korekcije, pa uz njegovo odobrenje nastavniku. | Formalna odobrenja su evidentirana pre statusa „spremno za predaju“. |

Za **0.2** prioritet je validan tehnički i dokumentacioni baseline. Korisnički
nalazi i potvrda hipoteza nisu njen uslov. Ako dataset, adapter ili ocene ne
budu završeni, navesti stvarno dostignuti podstatus, bez prikazivanja plana kao
rezultata.

## Handoff novom agentu

1. Pročitaj root `AGENTS.md`, ovaj plan, [0.1](pir-verzija-0-1.md) i ciljane
   [priloge](research/README.md). Proveri `git status --short` i sačuvaj
   postojeće izmene pri premeštanju PIR fajlova.
2. Tehničke navode iz 0.1 proveri prema trenutnom kodu, testovima i config-u.
   Za implementaciju preko više slojeva koristi Serena/OpenSpec postupak iz
   [vodiča za agente](../../03-development/ai-agent-tools.md).
3. Uz svaku verziju zabeleži datum, commit, status implementacije i merenja,
   izmenjene odluke i tačne komande sa ishodima.
4. Ne čuvaj sirove odgovore, kontakte, saglasnosti ni snimke u javnom
   repozitorijumu. Dok čekaš spoljnu potvrdu, radi nezavisne zadatke.

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

## Ranije radne odluke i metodološka razrada

### Radna specifikacija

Na osnovu potvrđenih odluka, radna specifikacija PIR-a je:

| Oblast | Odluka |
| --- | --- |
| Naslov | Student ga je potvrdio u navedenom ćiriličnom obliku; odobrenje mentora i nastavnika nije evidentirano |
| Tekst rada | Srpska ćirilica, uz izvorne tehničke termine |
| Naslovna strana | Model analiziranog primera, bez preuzimanja njegovog sadržaja |
| Poznati podaci | Potvrđeni su student, broj indeksa, mentor i predmetni nastavnik |
| Organizacija | Postoji; mentor iz organizacije ili sporazum još nije potvrđen |
| Prva predaja | Mentoru, pa tek posle odobrenja predmetnom nastavniku, prema uputstvu |
| Obim i format | Raniji radni cilj 30–35, najviše 35–40 strana zahteva potvrdu nastavnika; važeća preporuka je 20–30 strana. DOCX/PDF oblik proveriti sa nastavnikom. |
| Citiranje | Fusnote prema pravilima iz prezentacije |
| Rok | Raniji cilj „do dve nedelje“ potiče iz avgustovskog planiranja; novi rok treba potvrditi. |
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

- **H1 — kvalitet rangiranja:** predloženi hibrid treba da postigne veću
  usklađenost sa nezavisno ocenjenom relevantnošću oglasa od postojeće
  heuristike i baznog redosleda. Primarna mera u verziji 0.1 je `nDCG@5`.
- **H2 — efikasnost odluke:** korisnici uz rangiranje i objašnjenje rezultata
  završavaju zadatak izbora sa manje pregledanih oglasa nego uz baznu varijantu.
- **H3 — upotrebljivost i poverenje:** korisnici bolje ocenjuju upotrebljivost
  i poverenje u izbor kada vide ukupan skor, doprinos kriterijuma i kratko
  objašnjenje nego kada koriste samo baznu listu rezultata.

Preporuka je da se zadrže **tri** specifične hipoteze. One pokrivaju kvalitet
odluke, efikasnost i korisničku percepciju uz uzorak od 10–20 ispitanika. H3 treba tumačiti oprezno: SUS meri
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
objektivne metrike zadatka. Sledeće tri tvrdnje su raniji predlog; verzija 0.1
koristi četiri odvojene stavke iz [instrumenta](research/instruments.md), koje
treba zaključati pre merenja:

1. „Rangiranje odgovara preferencijama zadatim u scenariju.“
2. „Razumem zašto su prikazani oglasi dobili svoje pozicije.“
3. „Imao/la bih poverenja da ovaj prikaz koristim pri užem izboru smeštaja.“

Na kraju treba dodati jedno otvoreno pitanje: „Šta je najviše pomoglo, a šta je
otežalo izbor?“ UEQ-S ili TAM ne treba dodavati u istom malom istraživanju jer
bi produžili test bez jasne koristi za izabrane hipoteze.

### Razrešavanje ranije kontradikcije obima: više ravnopravnih uloga

Potvrđeni obim bira oba aktera, više ravnopravnih uloga i kombinaciju više
odluka. Međutim, potvrđeni kod trenutno personalizuje oglase samo za tražioca,
dok za izdavaoca ne postoji uporediv mehanizam rangiranja kandidata. Dva
ravnopravna mehanizma, dva skupa kriterijuma i dve korisničke populacije teško
staju u preporučen obim i rok koji tek treba potvrditi.

Predlog za nultu verziju je sledeći:

- platformu i problem opisati iz perspektive oba aktera;
- **primarno istraživanje i sve tri hipoteze ograničiti na odluku tražioca o
  izboru dugoročnog smeštaja**;
- odluku izdavaoca o izboru kandidata analizirati kroz zahteve i prikazati kao
  planirano proširenje, bez tvrdnje da je implementirana ili empirijski
  potvrđena.

Ako obe uloge zaista moraju biti ravnopravno implementirane i testirane, treba
ponovo dogovoriti obim, rok i hipoteze sa mentorom i nastavnikom. Verzija 0.1
primarno istraživanje već ograničava na tražioca.

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
prezentacije preporučuje 20–30 strana. Radni raspon 30–35 i najviše 35–40
strana može se koristiti samo ako ga nastavnik potvrdi; do tada sažeti
raspodelu iznad u 20–30 strana, bez širenja opštih opisa tehnologija.

## Raniji handoff za izradu nulte verzije (istorijski)

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
