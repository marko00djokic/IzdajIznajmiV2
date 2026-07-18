# Razvoj integrisane platforme za izdavanje smeštaja

## IzdajIznajmiV2

**Predlog teme master rada**  
**Predmet:** Programiranje u integrisanim tehnologijama  
**Student:** Marko Đokić

Beograd, 2026.

<div style="page-break-after: always;"></div>

## Sažetak projekta

IzdajIznajmiV2 je integrisana web platforma namenjena kratkoročnom, srednjoročnom i dugoročnom izdavanju i iznajmljivanju smeštaja na tržištu Srbije. Svrha projekta je da na jednom mestu objedini objavljivanje oglasa, pronalaženje odgovarajućeg smeštaja i upravljanje procesom najma. Projekat rešava problem rasutih informacija, otežanog poređenja ponuda i nepovezane komunikacije između tražilaca smeštaja i izdavalaca. Sistem razlikuje četiri osnovne uloge: gosta, tražioca smeštaja, izdavaoca i administratora. Gost može da pregleda javno dostupne oglase, dok se registrovani tražilac nakon verifikacije naloga prijavljuje za smeštaj, zakazuje obilazak i prati dalji tok prijave. Izdavalac kreira i uređuje oglase, upravlja pristiglim prijavama i zahtevima za obilazak i komunicira sa zainteresovanim korisnicima. Administrator nadgleda rad sistema i pristupa funkcijama za upravljanje korisnicima, verifikaciju i moderaciju. Posebno važan deo aplikacije čine ugrađeni razgovori i obaveštenja, koji omogućavaju da komunikacija i informacije o promenama statusa ostanu unutar jedinstvenog sistema. Pretraga i filtriranje oglasa pomažu korisniku da efikasnije pronađe ponudu koja odgovara njegovim zahtevima. Korisnički interfejs razvijen je kao Vue 3 aplikacija uz TypeScript, Vite, Pinia i Vue Router, a sa serverskim delom komunicira preko verzionisanog REST API-ja. Serverski deo zasnovan je na Laravelu 12, koristi Sanctum za autentifikaciju i autorizaciju, a trajne podatke čuva u PostgreSQL bazi. Meilisearch podržava naprednu pretragu, Reverb komunikaciju u realnom vremenu, dok Leaflet i OpenStreetMap omogućavaju funkcionalnosti zasnovane na lokaciji. Povezivanje frontend, backend i tehnologija baze podataka sa navedenim servisima pokazuje izradu višeslojne aplikacije i praktičnu integraciju heterogenih tehnologija u jedinstven informacioni sistem. Docker Compose orkestrira aplikacione i infrastrukturne kontejnere na lokalnoj mašini, dok Cloudflare Tunnel omogućava bezbedno izlaganje produkciono nalik demonstracionog okruženja na javnom domenu [www.izdajiznajmi.com](https://www.izdajiznajmi.com). GitHub Actions tokovi za kontinuiranu integraciju i isporuku povezuju repozitorijum sa automatizovanom izgradnjom i kontrolisanim postavljanjem aplikacije, čime projekat obuhvata i savremene CI/CD procese.

## Doprinos master rada

Doprinos master rada nije samo izrada još jedne CRUD web aplikacije za oglase, već projektovanje i implementacija integrisanog sistema za donošenje odluke o najmu smeštaja. Rad povezuje pretragu, geolokaciju, komunikaciju, statusni tok prijave, verifikaciju korisnika i operativnu statistiku u jedinstven proces koji smanjuje informacionu asimetriju između tražioca smeštaja i izdavaoca. Poseban akcenat je na tome da korisnik ne dobije samo listu oglasa, nego strukturisan pregled ponuda, kontekst tržišta i signale pouzdanosti koji mu pomažu da proceni odnos cene, lokacije, opremljenosti i rizika pre slanja prijave ili zakazivanja obilaska.

Sa tehničke strane, doprinos rada je demonstracija višeslojne arhitekture u kojoj Vue SPA, Laravel REST API, PostgreSQL, Meilisearch, Reverb, Docker Compose i CI/CD tokovi funkcionišu kao povezan informacioni sistem. Sa aplikativne strane, doprinos je model procesa najma koji obuhvata javnu pretragu, upoređivanje ponuda, prijavu, zakazivanje obilaska, komunikaciju, administrativnu moderaciju i metrike odlučivanja. Time se tema podiže iznad nivoa osnovne implementacije formi i tabela, jer se obrađuju integracija heterogenih tehnologija, životni ciklus poslovnog procesa i korisnička odluka zasnovana na podacima.

## Statistika i podrška odlučivanju

U radu će biti dodat modul za statistiku i podršku odlučivanju koji korisniku pomaže da proceni da li je određeni oglas dobar izbor u odnosu na dostupne alternative. Statistički deo može da prikaže agregirane pokazatelje kao što su broj dostupnih oglasa po gradu ili delu grada, raspodela cena po kategoriji smeštaja, prosečna i medijalna cena za slične oglase, odnos cene i površine, najčešći sadržaji u ponudi, kao i broj prijava ili interesovanja za aktivne oglase. Ovi podaci ne moraju da otkrivaju privatne informacije pojedinačnih korisnika, već mogu da se prikazuju agregirano i filtrirano po lokaciji, tipu smeštaja, broju soba, kapacitetu i cenovnom rangu.

Deo za podršku odlučivanju treba da bude predstavljen kao praktičan korisnički alat, na primer kroz karticu „Pomoć pri odluci” na stranici oglasa ili u rezultatima pretrage. Korisniku bi se prikazali signali poput: cena oglasa u odnosu na prosečnu cenu sličnih oglasa, udaljenost ili map kontekst, dostupni sadržaji u odnosu na tražene kriterijume, ocene i pouzdanost izdavaoca, status verifikacije, kao i jasno izdvojene prednosti i potencijalni kompromisi. Na taj način sistem pomaže korisniku da donese informisanu odluku o iznajmljivanju, umesto da odluku zasniva samo na opisu i fotografijama oglasa.

Predloženi odgovor profesoru može da glasi: „Doprinos master rada je razvoj integrisane platforme koja korisniku ne nudi samo objavljivanje i pretragu oglasa, već podržava ceo proces odlučivanja o najmu. Sistem objedinjuje naprednu pretragu, geolokaciju, komunikaciju, verifikaciju, tok prijave i statističke pokazatelje tržišta, kako bi korisnik mogao da uporedi ponude i proceni odnos cene, lokacije, opremljenosti i pouzdanosti izdavaoca. U radu će posebno biti obrađen modul statistike i podrške odlučivanju, sa agregiranim pokazateljima i signalima koji pomažu korisniku da izabere smeštaj na osnovu podataka, a ne samo subjektivnog opisa oglasa.”

