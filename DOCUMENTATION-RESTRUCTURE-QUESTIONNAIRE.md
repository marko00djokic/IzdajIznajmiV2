# Upitnik za restrukturiranje projekta i dokumentacije

Ovaj upitnik prikuplja odluke potrebne za restrukturiranje `AGENTS.md`, `docs/`, glavnog `README.md` i prateće projektne memorije. Cilj je da novi AI agent ili čovek za nekoliko minuta razume projekat, pronađe autoritativan izvor i započne zadatak bez širokog skeniranja repozitorijuma.

## Kako se popunjava

- Kod svakog pitanja označi jedan odgovor promenom `[ ]` u `[x]`.
- Opcija označena sa **(preporuka)** je početni predlog na osnovu trenutnog repozitorijuma i strukture u `example/`; nije unapred izabrana.
- Ako nijedna ponuđena opcija ne odgovara, označi **E** i dopiši svoj odgovor.
- Ako želiš kombinaciju ponuđenih odgovora, izaberi **E** i napiši kombinaciju, npr. `A + C, ali bez ...`.
- Stavke u `example/` tretiraju se samo kao referentna dokumentacija i neće se smatrati delom aplikacije.

---

## 1. Cilj, publika i jezik

### 1. Koji je primarni cilj restrukturiranja?

- [x] A. Da AI agent što brže pronađe relevantan kod i pravila **(preporuka)**

### 2. Ko je primarna publika centralnog `AGENTS.md` fajla?

- [x] A. Svi AI coding agenti, nezavisno od alata **(preporuka)**

### 3. Ko je primarna ljudska publika dokumentacije?

- [x] A. Developer koji prvi put ulazi u projekat **(preporuka)**

### 4. Koliko tehničkog predznanja dokumentacija sme da pretpostavi?

- [x] C. Napredno poznavanje celog stacka

### 5. Koji zadaci treba da budu najbrže podržani pri ulasku novog agenta?

- [x] A. Dijagnostika i implementacija feature/bug zadataka **(preporuka)**

### 6. Na kom jeziku treba da bude centralna dokumentacija?

- [x] A. Srpski, uz engleske tehničke identifikatore **(preporuka)**

### 7. Kojim pismom treba pisati dokumentaciju na srpskom?

- [x] A. Latinicom **(preporuka)**

### 8. Kakav stil dokumentacije želiš?

- [x] A. Kratak, operativan i sa linkovima ka detaljima **(preporuka)**

### 9. Da li glavni `README.md` treba da zadrži portfolio/prodajni ton?

- [x] B. Da; portfolio predstavljanje je njegova glavna namena

### 10. Koliko brzo novi agent treba da stekne dovoljan početni kontekst?

- [x] A. Za 2–5 minuta, čitanjem najviše 3 kratka dokumenta **(preporuka)**
---

## 2. Identitet, obim i trenutno stanje projekta

### 11. Koji naziv treba svuda koristiti kao kanonski naziv projekta?

- [x] A. `IzdajIznajmiV2` **(preporuka na osnovu repozitorijuma)**

### 12. Kako treba opisati svrhu proizvoda u jednoj rečenici?

- [x] A. Marketplace za kratkoročno i srednjoročno izdavanje smeštaja **(preporuka)**

### 13. Koji status najbolje opisuje projekat danas?

- [x] B. MVP je funkcionalan, sledi stabilizacija i produkcija **(preporuka)**

### 14. Da li postoji aktivno produkciono okruženje koje dokumentacija mora navesti?

- [ ] A. Ne postoji **(preporuka dok se ne potvrdi suprotno)**
- [ ] B. Postoji jedno produkciono okruženje
- [ ] C. Postoje staging i production
- [ ] D. Postoji više okruženja/tenant instalacija
- [x] E. Moj odgovor: Kreirani su dva docker container-a koji jedan predstavlja razvojno a drugi produkciono okruzenje, ali pod produkcionim okruzenjem mislim na hostovanje na stvarnom domenu ali sa svog racunara. I dalje su podaci iskljucivo testni tako da nisam siguran moze li se u potpunosti nazvati produkcionim okruzenjem

### 15. Šta je autoritativan izvor za trenutno implementirane funkcionalnosti?

- [x] A. Kod i automatizovani testovi; dokumentacija ih objašnjava **(preporuka)**

### 16. Kako dokumentovati razliku između implementiranog, delimičnog i planiranog?

- [x] A. Obavezne statusne oznake u feature i roadmap dokumentima **(preporuka)**

### 17. Kako tretirati postojeće termine `seeker`, `tenant`, `landlord` i `admin`?

- [x] A. Definisati jedan kanonski termin po ulozi i mapirati sinonime u glossary-ju **(preporuka)**

### 18. Koji termin treba da bude kanonski za `booking request` / `application` / `inquiry` tok?

- [x] A. Jedan termin izabran u glossary-ju, uz mapu postojećih naziva **(preporuka)**

### 19. Kako tretirati V1 → V2 parity dokumentaciju?

- [x] A. Zadržati je kao istorijski/migracioni kontekst dok parity nije formalno zatvoren **(preporuka)**

### 20. Kako tretirati root Laravel direktorijume pomenute u sadašnjem `AGENTS.md` kao „legacy” (`app/`, `database/`), koji trenutno nisu vidljivi u mapi repoa?

- [x] A. Proveriti istoriju/namenu i ukloniti tvrdnju ako nije tačna **(preporuka)**

---

## 3. Centralni `AGENTS.md` i ponašanje AI agenata

### 21. Koliko obiman treba da bude root `AGENTS.md`?

- [x] A. Kratak ulazni dokument, približno 100–180 linija **(preporuka)**

### 22. Šta agent mora pročitati na početku svakog zadatka?

- [x] A. Samo `AGENTS.md`; dodatne dokumente bira po routing tabeli **(preporuka)**

### 23. Da li želiš posebnu „projektnu memoriju” nalik `example/.cursor/memory/`?

- [x] A. Da, ali vendor-neutral, npr. `.ai/memory/` ili `docs/status/` **(preporuka)**

### 24. Kada agent treba da čita dokument o trenutnom statusu/progresu?

- [x] A. Samo za zadatke vezane za roadmap, nastavak rada ili kada prompt to traži **(preporuka)**

### 25. Da li agent treba automatski da ažurira changelog posle svakog zadatka?

- [x] A. Samo kada zadatak menja ponašanje proizvoda, arhitekturu ili operacije **(preporuka)**

### 26. Da li agent treba automatski da ažurira progress/tasks fajlove?

- [ ] A. Samo kada je konkretna praćena stavka završena ili joj se promeni status **(preporuka)**
- [ ] B. Posle svakog zadatka
- [ ] C. Samo na eksplicitan zahtev
- [ ] D. Ne želim progress/tasks fajlove u repozitorijumu
- [ ] E. Moj odgovor: 

### 27. Da li je potreban trajni dnevnik tehničkih odluka (ADR/decisions)?

- [ ] A. Da; kratki ADR dokumenti za značajne i teško reverzibilne odluke **(preporuka)**
- [ ] B. Da; jedan kumulativni `decisions.md` kao u primeru
- [ ] C. Samo odluke u feature dokumentima
- [ ] D. Ne; commitovi i PR-ovi su dovoljni
- [ ] E. Moj odgovor: 

### 28. Da li želiš obaveznu evidenciju vremena (`timesheet`) kao u primeru?

- [ ] A. Ne, osim ako postoji poslovna potreba **(preporuka na osnovu trenutnog repoa)**
- [ ] B. Da, agent je ažurira posle svakog zadatka
- [ ] C. Da, ali samo na eksplicitan zahtev
- [ ] D. Evidencija postoji van repozitorijuma
- [ ] E. Moj odgovor: 

### 29. Da li `AGENTS.md` treba da propiše protokol pre izmene koda?

- [ ] A. Da: utvrdi obim, pročitaj ciljane dokumente, proveri postojeće izmene i tek onda menjaj **(preporuka)**
- [ ] B. Samo da zahteva čitanje relevantnih testova
- [ ] C. Samo za rizične backend/ops izmene
- [ ] D. Ne; agentu ostaviti potpunu slobodu
- [ ] E. Moj odgovor: 

### 30. Da li `AGENTS.md` treba da sadrži routing tabelu „vrsta zadatka → dokumenti/putanje/komande”?

- [ ] A. Da, to treba da bude glavni navigacioni mehanizam **(preporuka)**
- [ ] B. Samo tabelu direktorijuma
- [ ] C. Samo link ka `docs/README.md`
- [ ] D. Ne, dovoljan je opis arhitekture
- [ ] E. Moj odgovor: 

### 31. Kako dokumentovati kritične „gotchas” (Sanctum, queue, mock/real API, Reverb, image processing)?

- [ ] A. Najkritičnije u `AGENTS.md`, detalji u domenskim dokumentima **(preporuka)**
- [ ] B. Sve gotchas samo u `AGENTS.md`
- [ ] C. Samo u troubleshooting dokumentima
- [ ] D. Kao komentare u kodu, ne u dokumentaciji
- [ ] E. Moj odgovor: 

### 32. Da li treba dodati ugnježdene `AGENTS.md` fajlove u `frontend/`, `backend/`, `docs/` ili `ops/`?

- [ ] A. Da, samo tamo gde postoje stvarno različita lokalna pravila **(preporuka)**
- [ ] B. Da, u svakom glavnom direktorijumu
- [ ] C. Samo `frontend/AGENTS.md` i `backend/AGENTS.md`
- [ ] D. Ne; root `AGENTS.md` mora sadržati sva pravila
- [ ] E. Moj odgovor: 

---

## 4. Informaciona arhitektura `docs/` direktorijuma

### 33. Koji osnovni model organizacije dokumentacije želiš?

- [ ] A. Numerisani domeni: `01-project`, `02-architecture`, `03-development`, itd. **(preporuka)**
- [ ] B. Nenumerisani domeni: `project`, `architecture`, `development`, itd.
- [ ] C. Organizacija po aplikacijama: `frontend`, `backend`, `ops`, `product`
- [ ] D. Organizacija po fazama/milestone-ima
- [ ] E. Moj odgovor: 

### 34. Kako treba nazvati direktorijum dokumentacije?

- [ ] A. Zadržati lowercase `docs/` **(preporuka)**
- [ ] B. Preimenovati u `Docs/` kao u primeru
- [ ] C. Preimenovati u `documentation/`
- [ ] D. Podeliti dokumentaciju između root-a i modula
- [ ] E. Moj odgovor: 

### 35. Koje kategorije treba da postoje na prvom nivou `docs/`?

- [ ] A. Project, architecture, development, product/features, API, testing, operations, roadmap, archive **(preporuka)**
- [ ] B. Samo architecture, guides, features, roadmap i archive kao u primeru
- [ ] C. Frontend, backend, infrastructure i product
- [ ] D. Minimalno: reference, guides i archive
- [ ] E. Moj odgovor: 

### 36. Gde treba da živi opis proizvoda, korisničkih uloga i poslovnih tokova?

- [ ] A. U posebnom `project/` ili `product/` domenu **(preporuka)**
- [ ] B. U glavnom `README.md`
- [ ] C. U `docs/full-docs.md` kao jednoj velikoj knjizi
- [ ] D. Unutar feature dokumenata bez centralnog pregleda
- [ ] E. Moj odgovor: 

### 37. Šta uraditi sa sadašnjim velikim `docs/full-docs.md`?

- [ ] A. Razložiti ga na product overview, role capabilities i ključne user journeys **(preporuka)**
- [ ] B. Zadržati ga kao kanonski sveobuhvatni dokument
- [ ] C. Zadržati ga kao generisan zbir drugih dokumenata
- [ ] D. Arhivirati ga bez zamene
- [ ] E. Moj odgovor: 

### 38. Kako organizovati dokumentaciju funkcionalnosti kao što su KYC, search, transactions, recommendations i chat?

- [ ] A. Jedan feature dokument po domenu, sa statusom, tokovima i code mapom **(preporuka)**
- [ ] B. Poseban direktorijum za svaki feature kao sada
- [ ] C. Jedan zajednički `features.md`
- [ ] D. Odvojeno po frontend i backend implementaciji
- [ ] E. Moj odgovor: 

### 39. Kako organizovati bezbednosnu dokumentaciju koja sada ima mnogo zasebnih fajlova?

- [ ] A. Jedan security indeks + tematski dokumenti, uz jasno odvajanje politike i implementacije **(preporuka)**
- [ ] B. Spojiti sve u jedan `security.md`
- [ ] C. Zadržati trenutnu strukturu bez promena
- [ ] D. Premestiti sve u ops dokumentaciju
- [ ] E. Moj odgovor: 

### 40. Kako organizovati API dokumentaciju?

- [ ] A. Kratak API overview + kanonski contract/reference + praktični primeri **(preporuka)**
- [ ] B. Zadržati samo `api-contract.md`
- [ ] C. Jedan dokument po API resursu
- [ ] D. Koristiti isključivo generisani OpenAPI/Swagger
- [ ] E. Moj odgovor: 

### 41. Kako tretirati release notes i istorijske hotfix dokumente?

- [ ] A. Zadržati strukturisane release notes, a tehničke detalje povezati sa feature/ADR dokumentima **(preporuka)**
- [ ] B. Spojiti sve u jedan `CHANGELOG.md`
- [ ] C. Premestiti sve u `archive/`
- [ ] D. Ukloniti ih i osloniti se na Git istoriju
- [ ] E. Moj odgovor: 

### 42. Gde treba držati roadmap i planirane funkcionalnosti?

- [ ] A. Poseban `roadmap/` sa jasnim statusom „planirano, nije implementirano” **(preporuka)**
- [ ] B. U aktivnoj projektnoj memoriji (`tasks.md`)
- [ ] C. Samo u GitHub Issues/Projects
- [ ] D. U istim feature dokumentima kao implementirane mogućnosti
- [ ] E. Moj odgovor: 

### 43. Kako tretirati završene implementacione planove i jednokratne analize?

- [ ] A. Premestiti u `archive/plans/`, uz link samo kada imaju trajnu vrednost **(preporuka)**
- [ ] B. Ostaviti među aktivnom dokumentacijom
- [ ] C. Izbrisati nakon završetka zadatka
- [ ] D. Čuvati isključivo u PR opisima
- [ ] E. Moj odgovor: 

### 44. Kako organizovati UI reference slike?

- [ ] A. Smislena imena + indeks koji mapira sliku na ekran i status reference **(preporuka)**
- [ ] B. Zadržati postojeće screenshot nazive i samo poboljšati `README`
- [ ] C. Premestiti u `frontend/` pored UI koda
- [ ] D. Ukloniti iz repozitorijuma i držati u eksternom design alatu
- [ ] E. Moj odgovor: 

### 45. Da li svaki docs poddirektorijum treba da ima svoj `README.md` indeks?

- [ ] A. Samo direktorijumi sa više od nekoliko dokumenata ili posebnim pravilima **(preporuka)**
- [ ] B. Da, svaki bez izuzetka
- [ ] C. Ne, dovoljan je jedan centralni indeks
- [ ] D. Umesto `README.md` koristiti `index.md`
- [ ] E. Moj odgovor: 

### 46. Koju ulogu treba da ima `docs/README.md`?

- [ ] A. Mapa dokumentacije po ulozi i vrsti zadatka, sa kratkim opisima **(preporuka)**
- [ ] B. Samo abecedni spisak svih dokumenata
- [ ] C. Potpun onboarding vodič
- [ ] D. Automatski generisan sadržaj bez ručnih opisa
- [ ] E. Moj odgovor: 

---

## 5. Tehnički sadržaj koji agentu daje kontekst

### 47. Koliko detaljan treba da bude pregled arhitekture?

- [ ] A. Komponente, granice, ključni tokovi, entry points i code map; detalji iza linkova **(preporuka)**
- [ ] B. Samo high-level dijagram
- [ ] C. Detaljan opis skoro svakog direktorijuma i klase
- [ ] D. Odvojeni potpuni dokumenti za frontend i backend bez zajedničkog pregleda
- [ ] E. Moj odgovor: 

### 48. Da li dokumentacija treba da sadrži eksplicitnu mapu „feature → frontend → API → backend → tabela/testovi”?

- [ ] A. Da, za glavne i složene tokove **(preporuka)**
- [ ] B. Da, za svaki feature bez izuzetka
- [ ] C. Samo za backend domene
- [ ] D. Ne; pretraga koda je dovoljna
- [ ] E. Moj odgovor: 

### 49. Kako dokumentovati bazu podataka?

- [ ] A. Domeni i ključne relacije ručno, dok su migracije izvor istine **(preporuka)**
- [ ] B. Potpun ručno održavan katalog svih tabela i kolona
- [ ] C. Samo generisani ER dijagram
- [ ] D. Ne praviti poseban DB dokument
- [ ] E. Moj odgovor: 

### 50. Kako dokumentovati rute i endpoint-e koji se vremenom menjaju?

- [ ] A. Navesti ključne entry points i komande za dobijanje potpune aktuelne liste **(preporuka)**
- [ ] B. Ručno održavati svaku frontend i backend rutu u dokumentaciji
- [ ] C. Dokumentovati samo javne API endpoint-e
- [ ] D. Potpuno generisati iz routera/OpenAPI-ja
- [ ] E. Moj odgovor: 

### 51. Kako dokumentovati lokalni razvoj?

- [ ] A. Jedan quick start + odvojeni detalji za native i Docker workflow **(preporuka)**
- [ ] B. Docker kao jedini podržani način
- [ ] C. Native Node/PHP kao jedini podržani način
- [ ] D. Poseban kompletan vodič za svaki operativni sistem
- [ ] E. Moj odgovor: 

### 52. Koji lokalni režim treba predstaviti kao podrazumevani?

- [ ] A. Frontend + backend sa real API-jem, uz mock kao brzu alternativu **(preporuka)**
- [ ] B. Frontend mock mode bez backend-a
- [ ] C. Docker Compose kompletan stack
- [ ] D. Native procesi bez Docker-a
- [ ] E. Moj odgovor: 

### 53. Kako objasniti mock/real API razliku?

- [ ] A. Kratka matrica ponašanja, env promenljivih, ograničenja i odgovarajućih testova **(preporuka)**
- [ ] B. Samo opis `VITE_USE_MOCK_API` promenljive
- [ ] C. Poseban dugačak vodič samo za mock mode
- [ ] D. Ukloniti mock mode iz dokumentacije jer je privremen
- [ ] E. Moj odgovor: 

### 54. Kako dokumentovati procese koji moraju paralelno da rade (queue, scheduler, Reverb, Meilisearch)?

- [ ] A. Jedan development services dokument sa matricom „obavezno/opciono/za koji feature” **(preporuka)**
- [ ] B. Sve komande u `AGENTS.md`
- [ ] C. Samo unutar feature dokumenata
- [ ] D. Samo kroz Docker Compose
- [ ] E. Moj odgovor: 

### 55. Kako organizovati test dokumentaciju?

- [ ] A. Test strategy + command matrix + aktivni manual/UAT planovi po domenu **(preporuka)**
- [ ] B. Jedan veliki test plan
- [ ] C. Samo README fajlovi unutar test direktorijuma
- [ ] D. Bez zasebne test dokumentacije; testovi su dovoljni
- [ ] E. Moj odgovor: 

### 56. Kada agent mora pokretati testove?

- [ ] A. Ciljane testove uvek kada je moguće; puni suite prema riziku i obimu **(preporuka)**
- [ ] B. Uvek kompletan frontend i backend suite
- [ ] C. Samo ako korisnik eksplicitno traži
- [ ] D. Samo pre commita ili PR-a
- [ ] E. Moj odgovor: 

### 57. Kako dokumentovati environment varijable i tajne?

- [ ] A. Grupisati javne nazive/svrhu i upućivati na `.env.example`; nikad stvarne vrednosti **(preporuka)**
- [ ] B. Potpunu tabelu svih promenljivih ručno održavati u docs
- [ ] C. Dokumentovati ih samo u `.env.example` komentarima
- [ ] D. Odvojiti interne vrednosti u privatni dokument unutar repoa
- [ ] E. Moj odgovor: 

### 58. Kako dokumentovati produkciju, deploy i rollback?

- [ ] A. Odvojeni runbookovi sa preduslovima, proverama, rollbackom i linkovima ka `ops/` skriptama **(preporuka)**
- [ ] B. Jedan objedinjeni deployment dokument
- [ ] C. Samo komentari u `ops/` skriptama
- [ ] D. Produkciona dokumentacija treba da bude van repozitorijuma
- [ ] E. Moj odgovor: 

---

## 6. Održavanje, pouzdanost i sprečavanje konfuzije

### 59. Kako označiti autoritativan dokument kada se informacije preklapaju?

- [ ] A. Eksplicitno navesti „source of truth” i iz drugih dokumenata samo linkovati **(preporuka)**
- [ ] B. Dozvoliti dupliranje radi lakšeg čitanja
- [ ] C. Autoritet određuje najnoviji datum izmene
- [ ] D. Glavni `README.md` je uvek autoritativan
- [ ] E. Moj odgovor: 

### 60. Da li dokumenti treba da imaju metadata zaglavlje (status, vlasnik, poslednja provera)?

- [ ] A. Samo dokumenti skloni zastarevanju: runbook, roadmap, security i feature status **(preporuka)**
- [ ] B. Da, svaki dokument
- [ ] C. Samo datum poslednje izmene
- [ ] D. Ne; Git istorija je dovoljna
- [ ] E. Moj odgovor: 

### 61. Ko je odgovoran za ažurnost dokumentacije?

- [ ] A. Autor svake izmene koda mora ažurirati povezane dokumente **(preporuka)**
- [ ] B. Jedan imenovani documentation owner
- [ ] C. AI agent automatski posle svakog zadatka
- [ ] D. Periodični ručni audit, nezavisno od izmena koda
- [ ] E. Moj odgovor: 

### 62. Kada izmena koda obavezno zahteva izmenu dokumentacije?

- [ ] A. Kada menja ponašanje, ugovor, arhitekturu, setup, operacije ili korisnički tok **(preporuka)**
- [ ] B. Kod svake izmene koda
- [ ] C. Samo kod novih feature-a
- [ ] D. Samo kada korisnik to zatraži
- [ ] E. Moj odgovor: 

### 63. Kako rešavati zastarele dokumente?

- [ ] A. Odmah ažurirati ili označiti `stale`; zatim arhivirati ako više nisu aktivni **(preporuka)**
- [ ] B. Brisati čim se otkrije da su zastareli
- [ ] C. Čuvati bez oznake radi istorije
- [ ] D. Dodavati novi dokument, a stari ne menjati
- [ ] E. Moj odgovor: 

### 64. Da li treba automatizovati proveru Markdown linkova i strukture?

- [ ] A. Da, lagana lokalna/CI provera linkova i osnovnih pravila **(preporuka)**
- [ ] B. Samo povremena ručna provera
- [ ] C. Samo Markdown lint bez provere linkova
- [ ] D. Ne uvoditi dodatni tooling
- [ ] E. Moj odgovor: 

### 65. Da li CI treba da blokira merge zbog dokumentacionih grešaka?

- [ ] A. Samo zbog polomljenih internih linkova ili nevalidne generisane reference **(preporuka)**
- [ ] B. Zbog svih Markdown lint upozorenja
- [ ] C. Samo upozorenje, nikad blokiranje
- [ ] D. Dokumentaciju ne proveravati u CI-u
- [ ] E. Moj odgovor: 

### 66. Kako treba imenovati dokumente i direktorijume?

- [ ] A. Lowercase kebab-case, osim standardnih `README.md`, `AGENTS.md`, `CHANGELOG.md` **(preporuka)**
- [ ] B. UPPERCASE za sve referentne dokumente
- [ ] C. Naslovi sa razmacima radi čitljivosti
- [ ] D. Zadržati trenutne nazive bez standardizacije
- [ ] E. Moj odgovor: 

### 67. Da li feature dokumenti treba da prate zajednički šablon?

- [ ] A. Da: svrha, status, korisnici, tok, pravila, code map, testovi, gotchas, povezani docs **(preporuka)**
- [ ] B. Samo naslov, opis i code map
- [ ] C. Različit format prema potrebama svakog feature-a
- [ ] D. Generisati ih isključivo iz koda
- [ ] E. Moj odgovor: 

### 68. Da li operativni runbookovi treba da prate zajednički šablon?

- [ ] A. Da: signal/problem, preduslovi, koraci, verifikacija, rollback/escalation **(preporuka)**
- [ ] B. Samo lista komandi
- [ ] C. Slobodan format
- [ ] D. Runbookovi nisu potrebni u repozitorijumu
- [ ] E. Moj odgovor: 

### 69. Kako dokumentovati poznate praznine, placeholder-e i nepotpune funkcije?

- [ ] A. Centralni known-limitations dokument + oznake u relevantnim feature docs **(preporuka)**
- [ ] B. Samo TODO komentari u kodu
- [ ] C. Samo GitHub issues
- [ ] D. U roadmap dokumentu bez povezivanja sa aktivnim funkcijama
- [ ] E. Moj odgovor: 

### 70. Kako sprečiti da budući agent ponovo napravi veliki, dupliran dokument?

- [ ] A. Pravilo u `AGENTS.md`: prvo pronađi source of truth, ažuriraj njega i linkuj **(preporuka)**
- [ ] B. Ograničiti svaki dokument na najviše 200 linija
- [ ] C. Zahtevati odobrenje pre kreiranja svakog `.md` fajla
- [ ] D. Periodično automatski spajati slične dokumente
- [ ] E. Moj odgovor: 

---

## 7. Migracija postojeće dokumentacije i željeni rezultat

### 71. Kako želiš da se restrukturiranje sprovede?

- [ ] A. U fazama: inventar i klasifikacija → nova struktura → migracija → provera linkova **(preporuka)**
- [ ] B. Jednim velikim premeštanjem i prepisivanjem
- [ ] C. Prvo samo novi `AGENTS.md` i indeks, ostalo kasnije
- [ ] D. Samo preporuka/planski dokument, bez izmene fajlova
- [ ] E. Moj odgovor: 

### 72. Koliko postojećeg teksta treba sačuvati?

- [ ] A. Sačuvati tačne i korisne činjenice, ali slobodno sažeti, podeliti i ukloniti duplikate **(preporuka)**
- [ ] B. Sačuvati svaki tekst, samo ga premestiti
- [ ] C. Napisati dokumentaciju gotovo od nule na osnovu koda
- [ ] D. Menjati samo indekse i linkove
- [ ] E. Moj odgovor: 

### 73. Kako tretirati netačne ili međusobno kontradiktorne tvrdnje?

- [ ] A. Verifikovati ih prema kodu/testovima/config-u, ispraviti i zabeležiti bitne odluke **(preporuka)**
- [ ] B. Pitati te za svaku kontradikciju pre izmene
- [ ] C. Zadržati obe verzije uz upozorenje
- [ ] D. Prednost uvek dati novijem dokumentu
- [ ] E. Moj odgovor: 

### 74. Kako tretirati `example/` nakon završetka restrukturiranja?

- [ ] A. Ne dirati ga tokom rada; na kraju pitati da li ostaje ili se uklanja **(preporuka)**
- [ ] B. Zadržati ga trajno kao referencu
- [ ] C. Premestiti ga u `docs/archive/example/`
- [ ] D. Ukloniti ga čim nova struktura bude gotova
- [ ] E. Moj odgovor: 

### 75. Koji rezultat očekuješ odmah nakon što popuniš ovaj upitnik?

- [ ] A. Analizu odgovora, predlog finalne strukture i plan migracije pre izmena **(preporuka)**
- [ ] B. Odmah kompletnu restrukturaciju bez dodatne potvrde
- [ ] C. Samo novi `AGENTS.md`
- [ ] D. Novi `AGENTS.md`, `docs/README.md` i prazne šablone; migracija kasnije
- [ ] E. Moj odgovor: 

### 76. Koliko detaljno treba proveriti postojeću dokumentaciju prema stvarnom kodu?

- [ ] A. Ciljani audit svih tvrdnji koje će ostati u aktivnoj dokumentaciji **(preporuka)**
- [ ] B. Potpuna linija-po-linija verifikacija svakog dokumenta
- [ ] C. Proveriti samo setup, arhitekturu i deploy
- [ ] D. Ne proveravati kod; zadatak je samo reorganizacija
- [ ] E. Moj odgovor: 

### 77. Da li je dozvoljeno preimenovanje i premeštanje velikog broja fajlova uz očuvanje Git istorije?

- [ ] A. Da, ako postoji jasna mapa stara → nova putanja i svi linkovi se poprave **(preporuka)**
- [ ] B. Da, bez potrebe za migracionom mapom
- [ ] C. Samo nekoliko najvažnijih fajlova
- [ ] D. Ne; postojeće putanje moraju ostati stabilne
- [ ] E. Moj odgovor: 

### 78. Da li treba ostaviti redirect/stub dokumente na starim putanjama?

- [ ] A. Samo za često korišćene ili spolja linkovane dokumente **(preporuka)**
- [ ] B. Da, za svaku staru putanju
- [ ] C. Ne; svi interni linkovi će biti ažurirani
- [ ] D. Ne premeštati fajlove, pa stubovi nisu potrebni
- [ ] E. Moj odgovor: 

### 79. Da li želiš završni izveštaj o restrukturiranju?

- [ ] A. Da: nova mapa, source-of-truth matrica, migrirane/arhivirane stavke i provere **(preporuka)**
- [ ] B. Samo kratak rezime izmena
- [ ] C. Samo Git diff/commit poruka
- [ ] D. Nije potreban poseban izveštaj
- [ ] E. Moj odgovor: 

### 80. Koji kriterijum znači da je restrukturiranje uspešno završeno?

- [ ] A. Novi agent iz `AGENTS.md` brzo nalazi relevantan kontekst, nema aktivnih kontradikcija ni polomljenih linkova **(preporuka)**
- [ ] B. Svi postojeći dokumenti su raspoređeni u nove foldere
- [ ] C. `AGENTS.md` vizuelno prati primer iz `example/`
- [ ] D. Dokumentacija je kraća nego sada
- [ ] E. Moj odgovor: 

---

## Dodatni kontekst koji nije obuhvaćen pitanjima

Ovde dodaj ograničenja, prioritete, spoljne izvore dokumentacije, poslovne zahteve ili druge napomene koje treba uzeti u obzir:



## Evidencija popunjavanja

- Popunio/la: 
- Datum: 
- Dodatne napomene: 
