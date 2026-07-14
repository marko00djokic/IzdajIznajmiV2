# Ključni korisnički tokovi

## Gost → tražilac smeštaja

1. Gost otvara home, search ili detalj aktivnog oglasa.
2. Filteri mogu koristiti legacy listing search ili Search V2; map mode koristi
   geografske parametre i Leaflet prikaz.
3. Za zaštićenu radnju real API režim usmerava korisnika na login/register.
4. Tražilac bira period i šalje `Application` za aktivan oglas.
5. Prijavu prati u `/bookings`/`/applications`, a razgovor vodi u listing ili
   application kontekstu.
6. Može odvojeno da zatraži obilazak, primi potvrdu i preuzme ICS.

## Izdavalac

1. Izdavalac kreira draft oglas, podatke i slike.
2. Queue obrađuje slike; policy i lifecycle servis kontrolišu izmene/status.
3. Objavljuje oglas i prima prijave, zahteve za obilazak i poruke.
4. Prihvata ili odbija prijavu. Prihvaćeni tok može preći u rental transakciju.
5. Generiše ugovor, prati potpise/depozit i završava transakciju.

## Administrator

1. Admin se prijavljuje i prolazi obavezni MFA gate kada je uključen.
2. Pregleda KPI, moderaciju, korisnike, ocene, KYC i strukturisane logove.
3. Može upravljati security sesijama/fraud statusom i privremeno impersonirati
   korisnika; sve osetljive radnje moraju ostati autorizovane i auditovane.
4. Admin kontroliše sporne/otkazane transakcije i payout korake.

## Sistemski tokovi

- scheduler ističe oglase i podatke prema retention pravilima, pokreće saved
  search matcher, digeste, badge recompute i backup verifikaciju;
- queue obrađuje slike, attachment-e, push/notification i search poslove;
- Reverb emituje događaje, dok UI zadržava polling fallback za chat i
  obaveštenja.

Precizna pravila i statusi su u odgovarajućim
[feature dokumentima](../04-features/README.md).
