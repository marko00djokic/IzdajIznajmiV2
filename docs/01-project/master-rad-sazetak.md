# Razvoj integrisane platforme za izdavanje smeštaja

## IzdajIznajmiV2

**Predlog teme master rada**  
**Predmet:** Programiranje u integrisanim tehnologijama  
**Student:** ______________________________

Beograd, 2026.

<div style="page-break-after: always;"></div>

## Sažetak projekta

IzdajIznajmiV2 je integrisana web platforma namenjena kratkoročnom, srednjoročnom i dugoročnom izdavanju i iznajmljivanju smeštaja na tržištu Srbije. Svrha projekta je da na jednom mestu objedini objavljivanje oglasa, pronalaženje odgovarajućeg smeštaja i upravljanje procesom najma. Projekat rešava problem rasutih informacija, otežanog poređenja ponuda i nepovezane komunikacije između tražilaca smeštaja i izdavalaca. Sistem razlikuje četiri osnovne uloge: gosta, tražioca smeštaja, izdavaoca i administratora. Gost može da pregleda javno dostupne oglase, dok se registrovani tražilac nakon verifikacije naloga prijavljuje za smeštaj, zakazuje obilazak i prati dalji tok prijave. Izdavalac kreira i uređuje oglase, upravlja pristiglim prijavama i zahtevima za obilazak i komunicira sa zainteresovanim korisnicima. Administrator nadgleda rad sistema i pristupa funkcijama za upravljanje korisnicima, verifikaciju i moderaciju. Posebno važan deo aplikacije čine ugrađeni razgovori i obaveštenja, koji omogućavaju da komunikacija i informacije o promenama statusa ostanu unutar jedinstvenog sistema. Pretraga i filtriranje oglasa pomažu korisniku da efikasnije pronađe ponudu koja odgovara njegovim zahtevima. Korisnički interfejs razvijen je kao Vue 3 aplikacija uz TypeScript, Vite, Pinia i Vue Router, a sa serverskim delom komunicira preko verzionisanog REST API-ja. Serverski deo zasnovan je na Laravelu 12, koristi Sanctum za autentifikaciju i autorizaciju, a trajne podatke čuva u PostgreSQL bazi. Meilisearch podržava naprednu pretragu, Reverb komunikaciju u realnom vremenu, dok Leaflet i OpenStreetMap omogućavaju funkcionalnosti zasnovane na lokaciji. Povezivanje frontend, backend i tehnologija baze podataka sa navedenim servisima pokazuje izradu višeslojne aplikacije i praktičnu integraciju heterogenih tehnologija u jedinstven informacioni sistem. Docker Compose orkestrira aplikacione i infrastrukturne kontejnere na lokalnoj mašini, dok Cloudflare Tunnel omogućava bezbedno izlaganje produkciono nalik demonstracionog okruženja na javnom domenu [www.izdajiznajmi.com](https://www.izdajiznajmi.com). GitHub Actions tokovi za kontinuiranu integraciju i isporuku povezuju repozitorijum sa automatizovanom izgradnjom i kontrolisanim postavljanjem aplikacije, čime projekat obuhvata i savremene CI/CD procese.
