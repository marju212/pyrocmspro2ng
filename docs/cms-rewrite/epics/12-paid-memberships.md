# Epic 12 — Paid Memberships (optional, Phase 2)

**Goal:** Stripe/Paddle subscriptions wired into existing access model.

**Recommended sequencing:** Phase 1 ships without paid memberships. Architecture supports adding them later with no rework. Add Cashier in a feature branch when business case is validated.

If included in Phase 1, adds **~1 week** to the total estimate.

## Stack decision

- **Cashier + Stripe** — direct integration. You handle VAT (use Stripe Tax for automation, ~0.5% extra).
- **Cashier + Paddle** — Paddle is Merchant of Record, handles EU VAT for you. Slightly higher fees, zero VAT/invoicing burden. Often the right call for a small Swedish business serving mixed-EU customers.

## Architecture

`Member` model gets `Billable` trait. `Page->visibility` enum gains `subscription` option with `required_plan` reference. `EnforcePageAccess` middleware adds one extra check: `$member->subscribedToPrice($page->required_plan)`. `RestrictedFilePolicy::download()` adds the same check. No restructuring.

Member account → "Manage subscription" → Cashier's `redirectToBillingPortal()` = Stripe-hosted billing UI. No custom subscription/payment-method UI needed. Webhooks handled automatically by Cashier's built-in controller.

## Checklist

- [ ] Decide Stripe vs. Paddle (VAT handling drives this)
- [ ] Install Laravel Cashier
- [ ] Add `Billable` trait to `Member` model
- [ ] Define plans in payment provider dashboard
- [ ] Build pricing page (Livewire)
- [ ] Build checkout flow → Stripe Checkout / Paddle Checkout
- [ ] Configure webhook endpoint (Cashier handles automatically)
- [ ] Build account → "Manage billing" → `redirectToBillingPortal()`
- [ ] Extend `Page->visibility` with `subscription` option
- [ ] Extend `EnforcePageAccess` middleware for subscription checks
- [ ] Extend `RestrictedFilePolicy` for subscription checks
- [ ] Test trial flow
- [ ] Test cancellation + refund
- [ ] Test failed payment + dunning
- [ ] Configure Stripe Tax / Paddle VAT
- [ ] Update T&C + privacy policy
- [ ] Document admin support procedures

## Non-technical work (not in dev estimate)

Pricing model decisions, VAT/invoicing integration with Swedish accounting (Fortnox/Visma — ~1 wk separate project if needed), terms/refund/GDPR policies, support operations, dunning strategy.
