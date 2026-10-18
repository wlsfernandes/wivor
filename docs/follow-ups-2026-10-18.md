# Follow-ups found on 2026-10-18

Items found while building the marketing site (plan 1) and while reviewing how photographers are paid. Nothing here is fixed yet.

## Photographer payouts (business-critical)

How it works today: the customer pays WiVor through Stripe Checkout (plain platform charge). Each order item freezes the photographer share at checkout (price after promo discount, minus `WIVOR_COMMISSION_PERCENTAGE`, default 20%). When `checkout.session.completed` arrives, `PhotographerTransferService` creates one `photographer_transfers` row per (order, photographer) and queues `ProcessPhotographerTransfer`. The job sends a Stripe transfer (`source_transaction` = the charge) once the photographer's Connect account is ready. Failed "not ready" transfers are retried when the Connect webhook reports the account became ready.

### Risks

1. **Refunds and chargebacks never reverse the photographer transfer.** `StripeWebhookController` only revokes downloads on `charge.refunded` and `charge.dispute.created`. No `transfers->createReversal` call exists, so WiVor absorbs the loss.
2. **Partial refunds are ignored.** The refund handler returns early unless the charge is fully refunded. If a partial refund happens before the transfer, the full share is still transferred.
3. **Possible double payment after about 24 hours.** If `transfers->create` succeeds but saving the result fails (crash or timeout), the row stays failed without a transfer id. A retry after Stripe's idempotency key expires (about 24 hours) sends a second transfer. There is no lookup by `transfer_group` before retrying.
4. **Failed transfers can stay stuck, and nobody can see them.** After 3 tries the job gives up. There is no scheduled retry, no admin retry button, and no admin screen that shows `photographer_transfers` or `last_error`.
5. **No transfers without a queue worker.** `QUEUE_CONNECTION=database`, so a `queue:work` process must be running in production. Nothing checks this.
6. **WiVor pays all Stripe fees.** Transfers are the full photographer share. `orders.stripe_fee_cents` is never filled in.
7. **Commission rounding happens per photo**, so the effective percentage can drift slightly on multi-photo orders.
8. **Outdated text says payouts are manual:**
   - photographer dashboard: "do not automatically send a transfer after each sale" (`resources/views/portal/photographers/dashboard.blade.php`)
   - `CheckoutService` docblock: "manual payout process"
   - `CheckoutTest` test name mentioning manual payout
   - admin photographer page labels payout readiness as "Publication and sales allowed", but checkout does not check it
   - `StripeConnectService` comment says "Express", but the code creates a v2 account with the full dashboard
   - `dashboardUrl` returns a generic `https://dashboard.stripe.com` link
9. **The Connect webhook decides live mode by checking whether the secret contains `_live_`.** This breaks with restricted keys.
10. **Currency is hard-coded to USD.**

## Marketing site (plan 1, deferred review items)

- The sticky header covers anchor targets such as `/photographers#register_section`. Fix: `html { scroll-padding-top: 80px; }` in `wivor-brand.css`.
- FAQ 8 sends people to an Organizers form that only arrives in plan 3.
- The mobile menu does not close with Escape or a click outside, the current link has no `aria-current`, the menu has no fallback without JavaScript, and the nav comes before the toggle in the page order.
- The Organizers page jumps from h1 to h3. The layout has no `<main>` landmark and no skip link (both missing before this work).
- The language switcher was removed. Visitors with an es/pt-BR session keep that language until the session ends.
- How It Works "Get paid" is live before Victor confirms the revenue split.
- The Biometric Notice fallback says "WivorPhotos" while the other new pages say "WiVor".
- `.wv-footer__social` uses `!important`. A more specific selector would avoid it.
- The header's form-error branch (`$errors`) has no test.
- Bootstrap `row g-5` is wider than `.wv-container` on narrow screens. It does not scroll only because `.boxed_wrapper` hides the overflow.
- No longer used, not deleted:
  - views `components/event`, `components/customer-how-it-works`
  - old About texts: `messages.core_values`, `our_mission`, `our_mission_p1`, `our_vision`, `our_vision_p1`, `values`, `values_p1`–`values_p5`
  - old header texts: `header.home`, `find_events`, `purchases_downloads`, `for_photographers`, `login`, `search_events`
- Not checked in a real browser at phone and desktop width yet.

## Waiting on Victor

- Revenue split wording for "Get paid".
- FAQ details: delivery, retention and deletion policy, payout schedule.
- Testimonials and permission to use the Georgia Multiports logo.
- Lawyer review of the Biometric Notice.
- Cookie wording for the Privacy Policy. The cookie banner links there, but the policy does not mention cookies yet (essential session cookies today, analytics cookies after plan 4).
- Hero and audience photos (athlete, photographer, start line).

## SEO foundation (deferred review items, 2026-10-18)

- Production must run with `APP_URL=https://www.wivorphotos.com` and the server must redirect `wivorphotos.com` to `www`. Canonical addresses now follow the request host, so without the redirect both hosts can be indexed.
- Events with a date but no venue, city or state still get SportsEvent data with an empty address ("Location to be announced"); Google flags it as invalid. Skip the data or the location when all three are empty.
- SportsEvent `startDate` from `starts_at` is printed in UTC; use the event's timezone. That branch has no test.
- `og:image:alt` default is double-escaped for titles with `&` or `'` (for example "Tom &amp;amp; Jerry").
- Pages without a meta description (cart, checkout, orders, removal request) print empty `og:description` and `twitter:description`.
- `/list-events?page=2` and later point their canonical at page 1.
- Team pages still use "Wivor | ..." titles and have double blank lines where keywords were removed.
- Portal layout and customer dashboard still print or set the keywords meta (old code, not deleted).
- Schema.org validator and Facebook sharing debugger not run yet (needs deploy).
