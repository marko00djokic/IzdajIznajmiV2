# Upitnik za restrukturiranje projekta i dokumentacije

Ovaj upitnik prikuplja odluke potrebne za restrukturiranje `AGENTS.md`, `docs/`, glavnog `README.md` i prateće projektne memorije. Cilj je da novi AI agent ili čovek za nekoliko minuta razume projekat, pronađe autoritativan izvor i započne zadatak bez širokog skeniranja repozitorijuma.

## Kako se popunjava

- Kod svakog pitanja označi jedan odgovor promenom `[x]` u `[x]`.
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

- [x] A. Samo kada je konkretna praćena stavka završena ili joj se promeni status **(preporuka)**

### 27. Da li je potreban trajni dnevnik tehničkih odluka (ADR/decisions)?

- [x] A. Da; kratki ADR dokumenti za značajne i teško reverzibilne odluke **(preporuka)**

### 28. Da li želiš obaveznu evidenciju vremena (`timesheet`) kao u primeru?

- [x] A. Ne, osim ako postoji poslovna potreba **(preporuka na osnovu trenutnog repoa)**

### 29. Da li `AGENTS.md` treba da propiše protokol pre izmene koda?

- [x] A. Da: utvrdi obim, pročitaj ciljane dokumente, proveri postojeće izmene i tek onda menjaj **(preporuka)**

### 30. Da li `AGENTS.md` treba da sadrži routing tabelu „vrsta zadatka → dokumenti/putanje/komande”?

- [x] A. Da, to treba da bude glavni navigacioni mehanizam **(preporuka)**

### 31. Kako dokumentovati kritične „gotchas” (Sanctum, queue, mock/real API, Reverb, image processing)?

- [x] A. Najkritičnije u `AGENTS.md`, detalji u domenskim dokumentima **(preporuka)**

### 32. Da li treba dodati ugnježdene `AGENTS.md` fajlove u `frontend/`, `backend/`, `docs/` ili `ops/`?

- [x] A. Da, samo tamo gde postoje stvarno različita lokalna pravila **(preporuka)**

---

## 4. Informaciona arhitektura `docs/` direktorijuma

### 33. Koji osnovni model organizacije dokumentacije želiš?

- [x] A. Numerisani domeni: `01-project`, `02-architecture`, `03-development`, itd. **(preporuka)**

### 34. Kako treba nazvati direktorijum dokumentacije?

- [x] A. Zadržati lowercase `docs/` **(preporuka)**

### 35. Koje kategorije treba da postoje na prvom nivou `docs/`?

- [x] A. Project, architecture, development, product/features, API, testing, operations, roadmap, archive **(preporuka)**

### 36. Gde treba da živi opis proizvoda, korisničkih uloga i poslovnih tokova?

- [x] A. U posebnom `project/` ili `product/` domenu **(preporuka)**

### 37. Šta uraditi sa sadašnjim velikim `docs/full-docs.md`?

- [x] A. Razložiti ga na product overview, role capabilities i ključne user journeys **(preporuka)**

### 38. Kako organizovati dokumentaciju funkcionalnosti kao što su KYC, search, transactions, recommendations i chat?

- [x] A. Jedan feature dokument po domenu, sa statusom, tokovima i code mapom **(preporuka)**

### 39. Kako organizovati bezbednosnu dokumentaciju koja sada ima mnogo zasebnih fajlova?

- [x] A. Jedan security indeks + tematski dokumenti, uz jasno odvajanje politike i implementacije **(preporuka)**

### 40. Kako organizovati API dokumentaciju?

- [x] A. Kratak API overview + kanonski contract/reference + praktični primeri **(preporuka)**

### 41. Kako tretirati release notes i istorijske hotfix dokumente?

- [x] A. Zadržati strukturisane release notes, a tehničke detalje povezati sa feature/ADR dokumentima **(preporuka)**

### 42. Gde treba držati roadmap i planirane funkcionalnosti?

- [x] A. Poseban `roadmap/` sa jasnim statusom „planirano, nije implementirano” **(preporuka)**

### 43. Kako tretirati završene implementacione planove i jednokratne analize?

- [x] A. Premestiti u `archive/plans/`, uz link samo kada imaju trajnu vrednost **(preporuka)**

### 44. Kako organizovati UI reference slike?

- [x] A. Smislena imena + indeks koji mapira sliku na ekran i status reference **(preporuka)**

### 45. Da li svaki docs poddirektorijum treba da ima svoj `README.md` indeks?

- [x] A. Samo direktorijumi sa više od nekoliko dokumenata ili posebnim pravilima **(preporuka)**

### 46. Koju ulogu treba da ima `docs/README.md`?

- [x] A. Mapa dokumentacije po ulozi i vrsti zadatka, sa kratkim opisima **(preporuka)**
- [x] C. Potpun onboarding vodič
- [X] E. Moj odgovor: Kombinacija A i C. Readme treba biti koristan kako potencijalnom klijentu tako i tehnickim licima

---

## 5. Tehnički sadržaj koji agentu daje kontekst

### 47. Koliko detaljan treba da bude pregled arhitekture?

- [x] A. Komponente, granice, ključni tokovi, entry points i code map; detalji iza linkova **(preporuka)**

### 48. Da li dokumentacija treba da sadrži eksplicitnu mapu „feature → frontend → API → backend → tabela/testovi”?

- [x] A. Da, za glavne i složene tokove **(preporuka)**

### 49. Kako dokumentovati bazu podataka?

- [x] A. Domeni i ključne relacije ručno, dok su migracije izvor istine **(preporuka)**

### 50. Kako dokumentovati rute i endpoint-e koji se vremenom menjaju?

- [x] A. Navesti ključne entry points i komande za dobijanje potpune aktuelne liste **(preporuka)**

### 51. Kako dokumentovati lokalni razvoj?

- [x] A. Jedan quick start + odvojeni detalji za native i Docker workflow **(preporuka)**

### 52. Koji lokalni režim treba predstaviti kao podrazumevani?

- [x] C. Docker Compose kompletan stack

### 53. Kako objasniti mock/real API razliku?

- [x] A. Kratka matrica ponašanja, env promenljivih, ograničenja i odgovarajućih testova **(preporuka)**

### 54. Kako dokumentovati procese koji moraju paralelno da rade (queue, scheduler, Reverb, Meilisearch)?

- [x] A. Jedan development services dokument sa matricom „obavezno/opciono/za koji feature” **(preporuka)**

### 55. Kako organizovati test dokumentaciju?

- [x] A. Test strategy + command matrix + aktivni manual/UAT planovi po domenu **(preporuka)**

### 56. Kada agent mora pokretati testove?

- [x] A. Ciljane testove uvek kada je moguće; puni suite prema riziku i obimu **(preporuka)**

### 57. Kako dokumentovati environment varijable i tajne?

- [x] A. Grupisati javne nazive/svrhu i upućivati na `.env.example`; nikad stvarne vrednosti **(preporuka)**

### 58. Kako dokumentovati produkciju, deploy i rollback?

- [x] A. Odvojeni runbookovi sa preduslovima, proverama, rollbackom i linkovima ka `ops/` skriptama **(preporuka)**

---

## 6. Održavanje, pouzdanost i sprečavanje konfuzije

### 59. Kako označiti autoritativan dokument kada se informacije preklapaju?

- [x] A. Eksplicitno navesti „source of truth” i iz drugih dokumenata samo linkovati **(preporuka)**

### 60. Da li dokumenti treba da imaju metadata zaglavlje (status, vlasnik, poslednja provera)?

- [x] A. Samo dokumenti skloni zastarevanju: runbook, roadmap, security i feature status **(preporuka)**

### 61. Ko je odgovoran za ažurnost dokumentacije?

- [x] A. Autor svake izmene koda mora ažurirati povezane dokumente **(preporuka)**
- [x] C. AI agent automatski posle svakog zadatka
- [X] E. Moj odgovor: Kombinacija A i C jer ce autor svake izmene uglavnom biti AI agent. Ja cu samo davati instrukcije za izmenu ai agentima.

### 62. Kada izmena koda obavezno zahteva izmenu dokumentacije?

- [x] A. Kada menja ponašanje, ugovor, arhitekturu, setup, operacije ili korisnički tok **(preporuka)**

### 63. Kako rešavati zastarele dokumente?

- [x] A. Odmah ažurirati ili označiti `stale`; zatim arhivirati ako više nisu aktivni **(preporuka)**

### 64. Da li treba automatizovati proveru Markdown linkova i strukture?

- [x] A. Da, lagana lokalna/CI provera linkova i osnovnih pravila **(preporuka)**

### 65. Da li CI treba da blokira merge zbog dokumentacionih grešaka?

- [x] A. Samo zbog polomljenih internih linkova ili nevalidne generisane reference **(preporuka)**

### 66. Kako treba imenovati dokumente i direktorijume?

- [x] A. Lowercase kebab-case, osim standardnih `README.md`, `AGENTS.md`, `CHANGELOG.md` **(preporuka)**

### 67. Da li feature dokumenti treba da prate zajednički šablon?

- [x] A. Da: svrha, status, korisnici, tok, pravila, code map, testovi, gotchas, povezani docs **(preporuka)**

### 68. Da li operativni runbookovi treba da prate zajednički šablon?

- [x] A. Da: signal/problem, preduslovi, koraci, verifikacija, rollback/escalation **(preporuka)**

### 69. Kako dokumentovati poznate praznine, placeholder-e i nepotpune funkcije?

- [x] A. Centralni known-limitations dokument + oznake u relevantnim feature docs **(preporuka)**

### 70. Kako sprečiti da budući agent ponovo napravi veliki, dupliran dokument?

- [x] A. Pravilo u `AGENTS.md`: prvo pronađi source of truth, ažuriraj njega i linkuj **(preporuka)**

---

## 7. Migracija postojeće dokumentacije i željeni rezultat

### 71. Kako želiš da se restrukturiranje sprovede?

- [x] B. Jednim velikim premeštanjem i prepisivanjem

### 72. Koliko postojećeg teksta treba sačuvati?

- [x] A. Sačuvati tačne i korisne činjenice, ali slobodno sažeti, podeliti i ukloniti duplikate **(preporuka)**

### 73. Kako tretirati netačne ili međusobno kontradiktorne tvrdnje?

- [x] A. Verifikovati ih prema kodu/testovima/config-u, ispraviti i zabeležiti bitne odluke **(preporuka)**

### 74. Kako tretirati `example/` nakon završetka restrukturiranja?

- [x] A. Ne dirati ga tokom rada; na kraju pitati da li ostaje ili se uklanja **(preporuka)**

### 75. Koji rezultat očekuješ odmah nakon što popuniš ovaj upitnik?

- [x] A. Analizu odgovora, predlog finalne strukture i plan migracije pre izmena **(preporuka)**

### 76. Koliko detaljno treba proveriti postojeću dokumentaciju prema stvarnom kodu?

- [x] A. Ciljani audit svih tvrdnji koje će ostati u aktivnoj dokumentaciji **(preporuka)**

### 77. Da li je dozvoljeno preimenovanje i premeštanje velikog broja fajlova uz očuvanje Git istorije?

- [x] A. Da, ako postoji jasna mapa stara → nova putanja i svi linkovi se poprave **(preporuka)**

### 78. Da li treba ostaviti redirect/stub dokumente na starim putanjama?

- [x] A. Samo za često korišćene ili spolja linkovane dokumente **(preporuka)**

### 79. Da li želiš završni izveštaj o restrukturiranju?

- [x] A. Da: nova mapa, source-of-truth matrica, migrirane/arhivirane stavke i provere **(preporuka)**

### 80. Koji kriterijum znači da je restrukturiranje uspešno završeno?

- [x] A. Novi agent iz `AGENTS.md` brzo nalazi relevantan kontekst, nema aktivnih kontradikcija ni polomljenih linkova **(preporuka)**

---

## Dodatni kontekst koji nije obuhvaćen pitanjima

Ovde dodaj ograničenja, prioritete, spoljne izvore dokumentacije, poslovne zahteve ili druge napomene koje treba uzeti u obzir: /



## Evidencija popunjavanja

- Popunio/la:  Marko Djokic (owner)
- Datum: 14.07.2026.
- Dodatne napomene: /
