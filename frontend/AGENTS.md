# Frontend lokalna pravila

Važe i pravila iz root `AGENTS.md`.

- Stack: Vue 3 Composition API, TypeScript, Vite, Pinia, Vue Router i Tailwind.
- Koristi 2 razmaka, PascalCase za `.vue` komponente i camelCase za store/service
  module. Rute i dokumenti su kebab-case.
- API pozivi idu kroz `src/services/index.ts`; mock i real adapter moraju
  zadržati kompatibilan javni interfejs.
- Ne zaobilazi router role guard niti Axios Sanctum/419 retry sloj.
- i18n tekst dodaj kroz postojeći language store; ne uvodi nepovezan novi
  localization sistem.
- Za logiku dodaj Vitest spec u `tests/`; za kritičan korisnički tok ažuriraj
  Playwright smoke i UAT dokument kada je relevantno.

Minimalna provera:

```bash
npm run build
npm run test
```

Za browser tok pokreni `npm run test:e2e`. Feature kontekst pronađi kroz
`docs/04-features/README.md` i `docs/02-architecture/code-map.md`.
