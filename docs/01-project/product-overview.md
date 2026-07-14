# Pregled proizvoda

## Identitet i status

`IzdajIznajmiV2` je marketplace za kratkoročno i srednjoročno izdavanje
smeštaja. Funkcionalan MVP pokriva većinu tržišnog toka, a sledeći cilj je
stabilizacija i prelazak sa production-like demonstracije na pouzdanu javnu
produkciju.

Status: `implemented` uz ograničenja iz
[known limitations](known-limitations.md).

## Vrednost proizvoda

Tražilac smeštaja može da otkrije i uporedi oglase, pošalje prijavu, zakaže
obilazak, razgovara sa izdavaocem i prati transakciju. Izdavalac upravlja
oglasima i dolaznom potražnjom. Administrator upravlja verifikacijom,
moderacijom, bezbednosnim signalima i transakcijama.

## Glavne capabilities

- javno listanje, filteri, Search V2, geokodiranje i map prikaz;
- lifecycle oglasa, slike i asinhrona obrada medija;
- browser-local favoriti, sačuvane pretrage i obaveštenja;
- prijava za smeštaj (`Application`) sa periodom i statusnim tokom;
- termini i zahtevi za obilazak sa ICS izvozom;
- razgovori, poruke, attachment-i, typing/presence i realtime/polling podrška;
- ocene, odgovori, prijave sadržaja, preporuke i landlord badges;
- KYC, email verifikacija, MFA, sesije i fraud signali;
- rental transakcija, ugovor, potpisi, depozit i admin kontrole;
- moderacija, KPI, impersonation i strukturisani security logovi.

## Granice sistema

Frontend je jedan Vue SPA. Laravel je jedini API/backend i poseduje poslovna
pravila, autorizaciju i trajne podatke. Spoljni sistemi su Meilisearch,
OpenStreetMap tile servis, email provider, Stripe, Sentry, web push i
Cloudflare Tunnel; njihova aktivacija zavisi od okruženja.

Detalji su u [pregledu arhitekture](../02-architecture/overview.md) i
[feature indeksu](../04-features/README.md).
