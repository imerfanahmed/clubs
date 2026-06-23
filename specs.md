# Club Membership System — Implementation Spec

## Context

Greenfield build. We are building a **club membership system** in **Laravel**
where the public can apply for membership, an admin vets and approves them, and
approved members are billed a recurring monthly subscription via **Stripe**. The
admin gets a financial + membership dashboard, automated pre-renewal email
reminders, and a bulk SMS tool.

### Confirmed decisions
- **Member flow:** Register → Admin approves → auto-charge. The applicant submits
  their card during registration (Stripe **SetupIntent**, card saved but *not*
  charged). The subscription + first charge is created only when the admin approves.
- **Stripe:** Laravel **Cashier** (`laravel/cashier`).
- **Auth:** Laravel **Breeze** (Livewire stack) + Alpine + Tailwind.
- **Users:** Single `users` table with roles via **spatie/laravel-permission**.
- **Packages:** Student £15 / Basic £25 / Premium £50 — DB records synced to
  Stripe Prices (admin-editable).
- **Reminders:** Email only, 3 days before renewal (daily scheduled job).
- **ClickSend:** Bulk SMS only (transactional email via Laravel Mail).
- **Address:** UK postcode lookup API (Ideal Postcodes) to autocomplete + validate.
- **Local env:** DDEV.

---

## Tech stack & packages

| Concern | Choice |
|---|---|
| Framework | Laravel 13 |
| Frontend | Livewire 4 + Alpine.js + Tailwind (via Breeze Livewire) |
| Auth | laravel/breeze (livewire) |
| Roles | spatie/laravel-permission |
| Billing | laravel/cashier (Stripe) |
| Payment UI | Stripe.js / Stripe Elements (SetupIntent) |
| SMS | ClickSend REST API (via Http client wrapper) |
| Postcode | Ideal Postcodes (autocomplete + validation) |
| Local | DDEV (PHP 8.3, MySQL, Mailpit for mail testing) |
| Queue/Schedule | database queue + Laravel scheduler (cron in DDEV) |

---

## Database schema

> Money stored as **integer pence** (`unsignedInteger`) everywhere; format in the
> UI. Currency fixed to `gbp`.

### `users` (extend Breeze default + Cashier)
- Breeze: `id, name, email, password, remember_token, timestamps`
- Add membership columns:
  - `phone` (string, E.164 stored for ClickSend)
  - `package_id` (FK → packages, nullable until chosen)
  - `status` enum: `pending, active, suspended, rejected, cancelled` (default `pending`)
  - `approved_at`, `approved_by` (FK users), `deactivated_at`, `rejection_reason` (text, nullable)
- Cashier adds (its migration): `stripe_id, pm_type, pm_last_four, trial_ends_at`

### `addresses` (one-to-one with user)
`id, user_id (FK, unique), line_1, line_2 (nullable), city, county (nullable), postcode, country (default 'GB'), timestamps`

### `packages`
`id, name, slug (unique), description, price (pence), interval (default 'month'), stripe_product_id, stripe_price_id, is_active (bool), sort_order, timestamps`
Seeded with Student/Basic/Premium; `php artisan packages:sync-stripe` creates/links Stripe Product+Price.

### Cashier tables (from package migration)
`subscriptions`, `subscription_items` — untouched, used as-is.

### `payments` (local mirror of Stripe invoices for reporting)
`id, user_id (FK), package_id (FK, nullable), stripe_invoice_id (unique), stripe_payment_intent_id, amount (pence), currency, status enum(paid, failed, refunded), period_start, period_end, paid_at (nullable), timestamps`
Populated by webhooks (`invoice.payment_succeeded`, `invoice.payment_failed`, `charge.refunded`).

### `reminder_logs` (idempotency for renewal reminders)
`id, user_id (FK), subscription_id, type (default 'pre_renewal'), period_end, sent_at, timestamps` — unique on `(user_id, period_end, type)`.

### `sms_campaigns`
`id, admin_id (FK users), message (text), recipient_count, total_cost (pence, nullable), status enum(queued, sending, completed, failed), sent_at, timestamps`

### `sms_messages`
`id, sms_campaign_id (FK), user_id (FK), phone, clicksend_message_id (nullable), status enum(queued, sent, delivered, failed), cost (pence, nullable), error (nullable), timestamps`

### spatie permission tables
Standard `roles, permissions, model_has_roles, ...`. Roles: `admin`, `member`.

---

## Models & relationships
- `User` — `use Billable` (Cashier), `use HasRoles` (spatie). Relations:
  `address()` (hasOne), `package()` (belongsTo), `payments()` (hasMany),
  `smsMessages()` (hasMany). Scopes: `scopePending`, `scopeActive`,
  `scopeUnpaidThisMonth`. Helper: `isAdmin()`, `activeSubscription()`.
- `Package` — `hasMany(User)`, `priceFormatted()` accessor.
- `Address`, `Payment`, `SmsCampaign`, `SmsMessage`, `ReminderLog`.

---

## Application flow

### 1. Public registration (`/register-member`)
Multi-step Livewire component `RegisterMember`:
1. **Personal details** — name, email, phone, password.
2. **Address** — postcode field → Alpine-driven call to a backend route
   `/postcode/lookup` (proxies Ideal Postcodes, key server-side) → address dropdown
   autofills line_1/city/county. Validate UK postcode format server-side too.
3. **Package** — radio cards of active packages with prices.
4. **Payment method** — Stripe Elements card field; on submit confirm a
   **SetupIntent** (`$user->createSetupIntent()` pattern — but user not yet created,
   so: create user `pending` first, then create Stripe customer + SetupIntent,
   confirm card client-side, set as default payment method). Card is saved, **no charge**.
5. Persist user as `status=pending`, store address + package, fire
   `MemberRegistered` event → notify admins (mail) + show applicant a "pending review" page.

### 2. Admin approval (dashboard)
- `Admin/PendingMembers` Livewire list. Actions per applicant:
  - **Approve** → `ApproveMemberAction`: `$user->newSubscription('default', $package->stripe_price_id)->create($user->defaultPaymentMethod())`. This charges the first month immediately. Set `status=active, approved_at, approved_by`. Send welcome email.
  - **Reject** → set `status=rejected`, store reason, email applicant.
- `Admin/Members` list of all members with filters (status, package). Actions:
  - **Deactivate** → cancel Stripe subscription (`$user->subscription('default')->cancelNow()` or `cancel()` for grace), `status=suspended`, `deactivated_at`.
  - **Reactivate** → resume/new subscription.

### 3. Billing lifecycle (webhooks)
Cashier's webhook controller handles subscription state. Extend with a listener
on `invoice.payment_succeeded` / `invoice.payment_failed` / `charge.refunded`
to upsert into `payments` and (on repeated failure) flip user to `suspended`.

### 4. Renewal reminders
Scheduled command `php artisan reminders:send` (daily). Finds active subscriptions
whose `current_period_end` is in **3 days**, skips any already in `reminder_logs`,
sends `RenewalReminderMail`, records a `reminder_log`. (Period end pulled from
Stripe subscription via Cashier / Stripe API.)

### 5. Bulk SMS (ClickSend)
`Admin/SmsCampaigns` Livewire: compose message, select recipients (filter by
status/package or all), preview count. On send → create `sms_campaign`, dispatch
`SendSmsCampaignJob` which batches to ClickSend `/v3/sms/send`, records per-message
results + cost in `sms_messages`. `ClickSendService` wraps the HTTP API with
basic-auth (username + api key from config). Phones normalized to E.164 (+44).

---

## Admin dashboard metrics (`Admin/Dashboard`)
Queries against `payments`:
- **Collected this month** = `sum(amount) where status=paid and paid_at in current month`.
- **Cumulative total** = `sum(amount) where status=paid`.
- **Per-user contribution** = group payments by user (table, sortable).
- **Unpaid this month** = active members with **no** `paid` payment whose
  `period_start` falls in the current month (or with a `failed` payment this month).
- Counts: pending applications, active/suspended members, MRR (sum of active
  members' package prices).
Cards built with Livewire + simple Blade; optional chart via Alpine + chart lib.

---

## Routes (high level)
- Public: `GET /register-member` (Livewire), `POST /postcode/lookup`.
- Member (auth): `/dashboard` (own status, payments, manage card, cancel).
- Admin (auth + `role:admin`): `/admin/dashboard`, `/admin/members`,
  `/admin/members/pending`, `/admin/packages`, `/admin/sms`, `/admin/payments`.
- Stripe webhook: `POST /stripe/webhook` (Cashier).

---

## Services / supporting classes
- `App\Services\PostcodeService` — Ideal Postcodes lookup + UK postcode regex validate.
- `App\Services\ClickSendService` — SMS send + balance.
- `App\Actions\ApproveMemberAction`, `RejectMemberAction`, `DeactivateMemberAction`.
- `App\Console\Commands\SyncPackagesToStripe`, `SendRenewalReminders`.
- Mailables: `MemberRegistered` (to admin), `ApplicationApproved`,
  `ApplicationRejected`, `RenewalReminderMail`, `PaymentFailedMail`.

---

## Config / env keys
`STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET, CASHIER_CURRENCY=gbp,
CLICKSEND_USERNAME, CLICKSEND_API_KEY, IDEAL_POSTCODES_API_KEY, MAIL_* (Mailpit)`.

---

## Build order (for the coding agent)
1. `ddev config` (laravel, php 8.3, mysql) → `ddev composer create laravel/laravel` → `ddev start`.
2. Install Breeze (livewire), spatie/permission, cashier. Run base migrations.
3. Roles seeder (admin/member) + first admin user seeder.
4. Migrations + models for packages, addresses, payments, reminder_logs, sms_*.
5. Packages seeder + `SyncPackagesToStripe` command; run to create Stripe prices.
6. `PostcodeService` + lookup route + postcode field.
7. `RegisterMember` multi-step Livewire (incl. Stripe Elements SetupIntent).
8. Admin: Dashboard, PendingMembers, Members, approve/reject/deactivate actions.
9. Stripe webhook extension → `payments` mirror; payment-failed handling.
10. Renewal reminder command + scheduler + mailables.
11. `ClickSendService` + SmsCampaigns UI + `SendSmsCampaignJob`.
12. Member self-service dashboard.
13. Tests + manual verification.

---

## Verification
- **DDEV up:** `ddev launch` serves the app; Mailpit (`ddev launch -m`) catches mail.
- **Registration:** complete `/register-member` with Stripe test card `4242…`,
  confirm user is `pending`, Stripe customer + saved card exist, **no charge** yet.
- **Postcode:** enter a real UK postcode, confirm address autofill.
- **Approval:** as admin, approve → verify Stripe subscription created, first
  invoice paid, `payments` row written, user `active`, welcome email in Mailpit.
- **Webhooks:** run `stripe listen --forward-to <ddev-url>/stripe/webhook`
  (or Cashier's test helpers); trigger `invoice.payment_succeeded`/`failed`,
  confirm `payments` + status updates.
- **Dashboard:** confirm "collected this month", cumulative, per-user, and
  "unpaid this month" reconcile with seeded/test payments.
- **Reminders:** seed a subscription with period_end in 3 days, run
  `php artisan reminders:send`, confirm one email + a `reminder_log` (and that a
  second run sends nothing — idempotent).
- **SMS:** create a campaign against ClickSend (use their test mode / verify
  `sms_messages` records + returned message IDs/cost).
- **Tests:** feature tests for registration, approval action (Stripe mocked),
  dashboard metric queries, reminder idempotency, postcode validation.

---

## Open follow-ups (not blocking)
- Refund flow UI (data model already supports `refunded`).
- Configurable reminder lead time (currently fixed 3 days).
- Dunning emails on repeated payment failure before suspension.
