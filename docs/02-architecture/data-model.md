# Data model

Migracije u `backend/database/migrations/` su jedini source of truth za schemu.
Ovaj dokument grupiše tabele po domenu, bez ponavljanja svake kolone.

## Identitet i bezbednost

`users` je centralni entitet. Na njega se vezuju Spatie role/permissions,
framework `sessions`, aplikativne `user_sessions`, `trusted_devices`, MFA
recovery kodovi, verifikacioni kodovi, fraud signals/scores i KYC podaci.

## Marketplace

`listings` pripada izdavaocu (`owner_id`) i ima slike, facilities pivot,
location/lifecycle podatke, događaje i agregirane ocene. `applications` vezuje
listing, tražioca i izdavaoca. `booking_requests` je legacy tabela koja još
postoji, ali nema aktivne rute.

## Komunikacija i engagement

Razgovori i poruke su u `conversations` i `messages`, sa privatnim
`chat_attachments`. `viewing_slots` i `viewing_requests` čine odvojeni tok
obilaska. Notifications, preferences i push subscriptions pokrivaju delivery.
Saved searches imaju match zapise; ocene i report tabele hrane moderaciju.

## Transakcije

`rental_transactions` povezuje listing, tražioca i izdavaoca. `contracts`,
`signatures` i `payments` predstavljaju dokument, saglasnost i depozit/payout
korake.

## Search i preporuke

`listing_events`, `search_filter_snapshots`, `landlord_metrics` i
`listing_ratings` čuvaju signale. Meilisearch indeks je izvedena projekcija i
može se ponovo izgraditi iz baze.

## Pravilo izmene

Schema se menja novom Laravel migracijom, uz model/Request/Resource i testove.
Ne dokumentuj statičnu listu svih kolona; za preciznost čitaj najnovije
migracije i `php artisan schema:*` alate dostupne u okruženju.
