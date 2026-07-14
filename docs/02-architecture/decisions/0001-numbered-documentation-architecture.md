# ADR 0001: Numerisana informaciona arhitektura dokumentacije

- Status: `accepted`
- Datum: 2026-07-15
- Vlasnik: owner projekta

## Kontekst

Dokumentacija je mešala onboarding, aktivne reference, phase planove, release
beleške i operativne vodiče. Veliki `full-docs.md` i root `README.md` duplirali
su informacije i usporavali pronalaženje source of truth-a.

## Odluka

`docs/` koristi domene `01-project` do `09-archive`. Root `AGENTS.md` rutira AI
agente, root `README.md` ostaje portfolio ulaz, a `docs/README.md` je ljudski
onboarding i mapa. Kod/testovi ostaju source of truth; planirano je odvojeno od
implementiranog.

## Posledice

Stare često korišćene putanje dobijaju kratke migration stubove. Novi dokument
ne sme da duplira kanonsku temu. Interni Markdown linkovi proveravaju se lokalno
i u CI.
