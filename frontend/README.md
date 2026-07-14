# IzdajIznajmiV2 frontend

Vue 3 + TypeScript + Vite SPA sa Pinia store-ovima, Router guard-ovima,
Tailwind CSS-om i mock/real API adapterima. Potreban je Node.js 20+.

```bash
cd frontend
npm ci
cp .env.example .env
npm run dev -- --host --port=5173
```

`VITE_USE_MOCK_API=true` koristi `src/services/mockApi.ts`; `false` koristi
Laravel preko `realApi.ts` i Sanctum cookie auth. Za real režim backend je
tipično na `http://localhost:8000`, a Vite proxy obrađuje `/api` i `/sanctum`
kada je `VITE_API_BASE_URL` prazan.

```bash
npm run build     # vue-tsc + Vite production build
npm run test      # Vitest
npm run test:e2e  # mock build + Playwright smoke
```

Lokalna pravila su u [AGENTS.md](AGENTS.md). Projekat se podrazumevano pokreće
kao kompletan [Docker stack](../docs/03-development/quick-start.md), a frontend
arhitektura i rute su u [UI mapi](../docs/02-architecture/frontend-ui.md).
