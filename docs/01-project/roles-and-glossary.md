# Uloge i glossary

## Kanonske uloge

| Kanonski termin u dokumentaciji | Kod/API vrednost | Postojeći sinonimi | Značenje |
| --- | --- | --- | --- |
| gost | nema autentifikovane uloge | `guest`, visitor | pregleda javni sadržaj |
| tražilac smeštaja | `seeker` | tenant, renter, applicant | traži smeštaj i podnosi prijavu |
| izdavalac | `landlord` | owner, host, stanodavac | objavljuje oglas i obrađuje potražnju |
| administrator | `admin` | admin | moderira i upravlja sistemom |

U kodu i payload-ima uvek koristi postojeće identifikatore (`seeker`,
`landlord`, `admin`). `tenant` je legacy sinonim i ne uvodi se u nove ugovore.

## Kanonski termini toka

| Termin | Kod/API | Napomena |
| --- | --- | --- |
| oglas | `Listing` | smeštaj ponuđen na marketplace-u |
| prijava za smeštaj | `Application` | kanonski inquiry/application tok preko `/apply` |
| zahtev za rezervaciju | `BookingRequest` | legacy model bez aktivnih API ruta |
| termin obilaska | `ViewingSlot` | vreme koje izdavalac nudi |
| zahtev za obilazak | `ViewingRequest` | tražilac rezerviše ponuđeni termin |
| razgovor | `Conversation` | listing/application-scoped chat kanal |
| transakcija najma | `RentalTransaction` | ugovor, potpisi, depozit i završetak |
| sačuvana pretraga | `SavedSearch` | filteri i frekvencija obaveštenja |

## Statusne oznake dokumentacije

`implemented`, `partial`, `planned`, `historical` i `stale` imaju značenja
definisana u [docs indeksu](../README.md#status-oznake).
