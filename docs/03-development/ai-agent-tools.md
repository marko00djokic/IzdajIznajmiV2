# AI agent alati

Ovaj projekat koristi Serena MCP za semantički rad sa codebase-om i zvanični
OpenSpec CLI/Codex skill workflow za spec-driven planiranje većih promena.
OpenSpec trenutno nije MCP server; `openspec init` generiše Codex skill-ove u
`.agents/skills/`, dok je Serena registrovana kao project-scoped MCP u
`.codex/config.toml`.

## Preduslovi i instalacija

Repo pin-uje Node.js `20.19.5` kroz `.nvmrc`. Serena zahteva `uv`, a OpenSpec
Node.js `20.19.0+`.

```powershell
# Serena
uv tool install -p 3.13 serena-agent
serena init

# OpenSpec
npm install -g @fission-ai/openspec@latest
openspec update
```

Serena MCP konfiguracija i projektni LSP jezici (`php`, `vue`, `typescript`)
već su verzionisani. OpenSpec struktura, konfiguracija i core Codex skill-ovi
takođe su verzionisani, pa se `openspec init` ne ponavlja na svakom checkout-u.
Posle prve instalacije ili promene MCP/skill konfiguracije restartuj Codex da
učita nove alate.

## Kada koristiti Serenu

Serena je preporučena kada rezultat zavisi od semantike koda, naročito za:

- pronalaženje klase, metode, komponente ili store/service simbola;
- pronalaženje referenci, implementacija i call-site-ova pre izmene ugovora;
- praćenje toka kroz Vue stranicu/store/service i Laravel rutu/controller/model;
- rename, izdvajanje ili refactor simbola koji prelazi granice fajlova;
- dijagnostiku nepoznatog ili složenog ponašanja pre implementacije.

Ne koristi Serenu samo radi jednostavnog listanja fajlova, tačne tekstualne
pretrage, čitanja Markdown/config fajla ili male lokalne izmene bez odnosa među
simbolima; za to su `rg` i direktno čitanje brži i jasniji.

## Kada koristiti OpenSpec

OpenSpec je preporučen pre pisanja koda kada promena ima makar jednu od ovih
osobina:

- nova funkcionalnost ili značajna promena korisničkog ponašanja;
- zahtev je nejasan ili postoji više podjednako validnih rešenja;
- promena prelazi frontend/backend granicu ili menja API/data ugovor;
- utiče na auth/security/KYC, realtime, search ili deploy arhitekturu;
- predstavlja breaking promenu, migraciju ili veći refactor;
- zahteva jasan trag odluka i acceptance scenarije pre implementacije.

Koristi `$openspec-explore` za read-only istraživanje problema i opcija,
`$openspec-propose` za kompletan predlog, `$openspec-apply-change` tek nakon
pregleda predloga, a `$openspec-archive-change` kada je implementacija završena
i proverena. Za mali lokalizovani bug, mehaničku izmenu ili copy/docs-only
korekciju OpenSpec obično nije potreban.

Za veću brownfield promenu redosled je:

1. Serena potvrđuje postojeće simbole, reference i tok izvršavanja.
2. OpenSpec beleži cilj, scenarije, design odluke i zadatke.
3. Implementacija prati odobreni change i postojeća `AGENTS.md` pravila.
4. Testovi i ručni E2E scenario potvrđuju rezultat pre arhiviranja change-a.

## Provera setupa

```powershell
node --version
serena --version
serena project health-check .
codex mcp list
openspec --version
openspec context --json
openspec list --json
```

Očekivano je da Serena health-check prijavi uspešan rad simbolskih alata,
`codex mcp list` prikaže omogućen `serena` server, a OpenSpec komande razreše
root ovog repozitorijuma bez aktivnih change-ova na novom setupu.

Zvanična uputstva: [Serena Quick Start](https://github.com/oraios/serena#quick-start),
[OpenSpec Quick Start](https://github.com/Fission-AI/OpenSpec#quick-start) i
[Codex MCP konfiguracija](https://developers.openai.com/codex/mcp).
