# Transactions – Contracts, Signatures, Deposits (Phase I)

> Status: `implemented`, Stripe tok je env-zavisan
> Poslednja ciljana provera: 2026-07-15
> Source of truth: transaction modeli/kontroleri/policies/config i testovi

## Lifecycle statuses
- `initiated`: landlord started a transaction for a listing + seeker.
- `contract_generated`: latest contract version rendered and ready to sign.
- `seeker_signed`: seeker signed the latest contract.
- `landlord_signed`: landlord signed (also used once both parties sign; use contract status for “fully signed”).
- `deposit_paid`: Stripe deposit payment succeeded (escrow-lite hold).
- `move_in_confirmed`: landlord confirmed move-in.
- `completed`: landlord confirmed completion after move-in.
- `cancelled`: transaction cancelled (legacy/admin tools disabled).
- `disputed`: transaction disputed (legacy/admin tools disabled).

Contract status:
- `draft`: one or zero signatures.
- `final`: both signatures recorded.

## Stripe setup
Environment variables (see `backend/.env.example`):
- `STRIPE_PUBLIC_KEY`, `STRIPE_SECRET_KEY`
- `STRIPE_WEBHOOK_SECRET`
- `TRANSACTIONS_CURRENCY` (default `EUR`)

Webhook:
- Endpoint: `POST /api/v1/webhooks/stripe`
- Required events: `checkout.session.completed`, `charge.succeeded`, `charge.refunded`.

## Local testing (Stripe CLI)
1. Run backend and frontend with valid Stripe test keys.
2. Start webhook forwarding:
   ```bash
   stripe listen --forward-to http://localhost:8000/api/v1/webhooks/stripe
   ```
3. Create a transaction, generate a contract, and sign as both parties.
4. Click “Pay deposit” to open Checkout in test mode.
5. Verify status updates to `deposit_paid` and receipt link appears when `charge.succeeded` arrives.
6. Landlord confirms move-in, then marks transaction completed.

## Security notes
- Contracts PDFs are stored on the private disk: `storage/app/private/contracts/{transaction_id}/contract_v{version}.pdf`.
- Only landlord, seeker, or admin can access contracts and transactions.
- Payment data is handled by Stripe Checkout; only provider IDs and receipt URLs are stored.
