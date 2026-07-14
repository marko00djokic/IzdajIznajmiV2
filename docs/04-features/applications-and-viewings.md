# Prijave za smeštaj i obilasci

> Status: `implemented`; `BookingRequest` je `partial/legacy`
> Poslednja ciljana provera: 2026-07-15
> Source of truth: API rute, Application/Viewing kontroleri, policies i testovi

## Prijava (`Application`)

Tražilac za aktivan oglas šalje period i opcionu poruku. Aktivna prijava za
preklapajući budući period blokira novu. Statusi su `submitted`, `accepted`,
`rejected` i `withdrawn`. Policy određuje koju tranziciju smeju tražilac,
izdavalac i admin. Prihvaćena prijava može biti polazna tačka za transaction
tok.

Kanonski naziv u dokumentaciji je „prijava za smeštaj”; API i kod koriste
`Application` i `/listings/{listing}/apply`.

## Obilasci

Izdavalac kreira termine (`ViewingSlot`); tražilac šalje `ViewingRequest`.
Izdavalac potvrđuje/odbija, tražilac može otkazati, a potvrđeni zahtev ima ICS
download. Tok je odvojen od prijave za smeštaj.

## Legacy booking

`BookingRequest` model, controller, policy, migration i seeder postoje, ali
controller nije povezan u `backend/routes/api.php`. Frontend real adapter
`getBookings()` vraća praznu listu. Ne razvijaj novi tok na tom modelu bez
eksplicitne odluke o migraciji ili uklanjanju.

## Code map i testovi

- Frontend: `Bookings.vue`, `stores/requests.ts`, `stores/viewings.ts`.
- Backend: `ApplicationController/Policy`, `ViewingSlotController/Policy`,
  `ViewingRequestController/Policy`.
- Testovi: `ApplicationsApiTest.php`, `ViewingsApiTest.php`.
