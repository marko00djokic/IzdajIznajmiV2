# Pravila za PIR i povezani master rad

Važe i root `AGENTS.md` i `docs/AGENTS.md`. Ovaj fajl važi za ceo
`docs/01-project/pir/`, uključujući `research/` i buduće izvore dijagrama.
Za sadržaj i redosled rada prvo pročitaj `README.md`, aktuelni `pir-plan.md`,
odgovarajući rukopis i ciljane priloge. `pir-plan.md` je kanonski za status i
metodološke odluke; istorijski `pir-ai-prompt.md` nije aktivan nalog. Za
jezik rukopisa primeni odluku iz plana: srpska ćirilica uz izvorne tehničke
identifikatore. Ove agentske instrukcije ostaju na latinici prema
`docs/AGENTS.md`.

## Akademsko istraživanje i izvori

- Kada zadatak zahteva pronalaženje, proveru ili poređenje naučne literature
  za PIR ili master rad, koristi OpenAlex MCP kao primarni katalog radova,
  metapodataka, DOI identifikatora i citatnih veza. Semantic Scholar MCP
  koristi dopunski kada je potrebna nezavisna provera ili dodatni relevantni
  radovi; vodi računa o ograničenju broja zahteva javnog API-ja. Ne pozivaj
  akademske MCP servere za obične razvojne zadatke, tehničko čitanje koda
  ili uređivanje instrukcija bez istraživanja literature. Ne moraš pozvati
  oba servera za svaki izvor.
- Prednost daj relevantnim recenziranim radovima i primarnim izvorima;
  uključi doktorske disertacije kada su relevantne i dostupne. Broj citata
  služi za otkrivanje radova, ne kao automatski dokaz kvaliteta ili
  relevantnosti.
- Pre upotrebe izvora proveri autora, naslov, godinu, publikaciju ili
  izdavača, DOI kada postoji i vezu između sadržaja izvora i konkretne
  tvrdnje. U `research/literature-matrix.md` zabeleži da li je analiziran puni
  tekst, samo apstrakt ili samo metapodaci; iz samih metapodataka ne izvodi
  detaljne nalaze. Tu ili uz tvrdnju u rukopisu zabeleži ograničenja pristupa,
  neslaganja u metapodacima i praznine u dokazima.
- Ne izmišljaj reference, rezultate, direktne citate, eksperimente ili
  zaključke. Različit redosled preporučenih oglasa nije dokaz bolje
  relevantnosti bez odgovarajuće nezavisne evaluacije. Za tehničke tvrdnje
  o aplikaciji merodavni su kod, testovi i konfiguracija, a za akademske
  tvrdnje provereni naučni izvori.

## Metodološke granice

- U tekstu, tabelama i dijagramima odvoj: (1) postojeće implementirano
  rešenje, (2) lokalna tehnička ispitivanja i offline evaluaciju i (3)
  predloge za buduće istraživanje i završni master rad. Uz rezultat navedi
  stvarni artefakt, uslove i ograničenja provere.
- Poštuj aktuelne odluke `pir-plan.md`, posebno O1 (obim i mentorska odluka
  čekaju potvrdu) i O2 (nezavisne ocene i test korisnika su odloženi). Ne
  pokreći istraživanje sa korisnicima ili A/B eksperimente i ne pretpostavljaj
  odluku mentora bez eksplicitnog zahteva i potrebne potvrde obima.

## Tehnički dijagrami

- Pravi dijagram samo ako objašnjava konkretan deo rukopisa. Pre crtanja
  proveri stvarnu implementaciju u kodu, testovima i konfiguraciji, pa
  postojeću dokumentaciju. Za veze među simbolima, tok izvršavanja i
  frontend/backend arhitekturu koristi Serena MCP prema root pravilima; za
  jednostavno nalaženje fajlova i teksta dovoljan je `rg`.
- Mermaid CLI (`mmdc`) je podrazumevan za tehničke dijagrame. PlantUML
  (`plantuml`) izaberi kada je formalni UML pogodniji; Graphviz (`dot`)
  kada je potreban poseban raspored ili prikaz grafovske strukture. Izbor
  zavisi od dijagrama; ne pokreći sva tri alata po navici.
- Sačuvaj uređiv izvor (`.mmd`, `.puml` ili `.dot`) uz izvezeni `.svg` u istom
  direktorijumu. Proveri da izvoz uspeva i da su tekst i veze čitljivi.
  Dosledno označi postojeće, lokalno ispitano i predloženo; ne prikazuj
  nepostojeće komponente kao implementirane. Za sliku u rukopisu prati
  `pir-plan.md`: broj, naziv, pun izvor i poziv iz teksta.
