# WiVor CRM — Implementation Notes

Companion to `implementation_plan.md`. Records what Phase 1 inspection found in the real codebase (2026-10-29) and what must be decided before any schema work starts.

## 1. Discovered architecture

| Area | Finding |
|---|---|
| Framework | Laravel 13.30.1, PHP 8.3, PHPUnit 12 (no Pest). Blade + Bootstrap admin theme (`resources/views/layouts/sidebar.blade.php`). |
| Database | PostgreSQL in production (`DB_CONNECTION` default `pgsql`). Tests run on SQLite `:memory:` (`phpunit.xml`). Primary keys are `bigint` `id()`. |
| Admin auth | `roles` + `role_user` tables. Gate `access-admin` (`AuthServiceProvider`) checks role name `admin`. Admin routes in `routes/admin.php` use `['auth', 'can:access-admin']`. Policies exist only for `Event` (`EventPolicy::before` grants admins everything). |
| Events | `events` table (title, slug, status, `date_of_event`, `starts_at`, venue/city/state, timezone, …). Event editing lives at `events.edit` in `routes/frontend.php` and is shared by photographers and admins through `EventPolicy`. Admin-only per-event page: `admin.media.show` (`/admin/media/{event}`). |
| Participants | **None.** No participant model, table, import or roster exists. The only per-event people data is bib numbers detected on photos (`photo_bib_numbers`) and buyers (`orders.customer_email`, `customers`). |
| Mail | Default mailer `smtp` (`config/mail.php`). `MAIL_*` variables are **not** listed in `.env.example`; the real provider is unknown (`.env` not read, per project rules). Existing mail is sent synchronously with `Mail::send` (contact form, receipts, photographer status), except `TemporaryPasswordMail` and `EventMediaExpirationWarning`, which are queued. |
| Queue | `config/queue.php` default `sync`; `jobs` and `failed_jobs` tables exist. `infrastructure/README.md` states a worker runs `queue:work --timeout=180` in production. |
| Scheduler | `Console\Kernel` schedules `media:enforce-retention` daily. Scheduler assumed to run every minute in production. |
| Cache / locks | Default cache `file`. No `phpredis` extension and no `predis` package installed. Atomic limiter must use database locking (PostgreSQL row locks) unless Redis is added. |
| Libraries | No HTML sanitizer (`symfony/html-sanitizer`, `ezyang/htmlpurifier`) installed. No CSV library beyond PHP built-ins. `symfony/mailer` and `symfony/mime` are present (needed for `List-Unsubscribe` headers). |
| Bot protection | Cloudflare Turnstile (`App\Rules\TurnstileToken`) — reusable for the public preference page if needed. |
| Tests baseline | `php artisan test` on clean `main`: 329 passed, **2 failed before any CRM work**: `FrontendTruthAlignmentTest` › authenticated legacy view does not advertise video services; `WebsitePolicyTest` › admin can open all website policy editors from the admin menu. |

## 2. Discrepancies with the plan

1. **Plan says each event "already has its own participants".** It does not. `crm_event_contacts` would become the first and only event participant list. Nothing existing needs to be preserved or reconciled, but the "Import Participants" shortcut has no existing participant screen to sit next to.
2. **Plan says event admin panel exists per event.** The closest admin-only page is `/admin/media/{event}`. `events.edit` is shared with photographers, so CRM links there must be wrapped in `@can('access-admin')`.
3. **SMTP provider cannot be verified from the code.** Per plan §G, sending must default to a paused state until provider limits are configured in code/config.
4. **No Redis.** Global limiter will use a `crm_delivery_state` row locked with `lockForUpdate()` in a transaction. SQLite tests do not enforce row locks, so concurrency proof needs a PostgreSQL test run or a documented manual check.
5. **`AGENTS.md` says "Do not change database schema unless explicitly required."** This feature explicitly requires about 11 new tables; flagged so the approval is explicit.
6. **No sanitizer library.** Plan §E requires sanitized HTML. That needs a new Composer dependency (rule: ask first).
7. **No verified postal address in config.** Promotional sending must stay blocked until one is configured (plan §E).

## 3. Proposed Phase 1 migrations (all new tables, no changes to existing tables)

| Table | Key columns / constraints |
|---|---|
| `crm_contacts` | `email_normalized` unique, `email_original`, `first_name`, `last_name`, `full_name`, `phone`, timestamps |
| `crm_event_contacts` | FK `event_id` → `events` (cascade), FK `contact_id` → `crm_contacts` (cascade), unique(`event_id`, `contact_id`), `bib_number`, `division`, `extra_fields` json, FK `import_id` nullable |
| `crm_imports` | FK `event_id`, FK `user_id` (operator), `disk_path`, `file_sha256`, `mapping` json, `data_source`, `organizer_name`, `consent_status`, `consent_evidence`, `status` indexed, row counters, `error_report_path` |
| `crm_contact_consents` | FK `contact_id`, `category` (operational/marketing), `scope_type` + `scope_event_id`, `status` (granted/unknown/revoked), `source`, `evidence`, `policy_version`, `recorded_at`, `revoked_at` |
| `crm_suppressions` | FK `contact_id` nullable, `email_normalized` indexed, `reason`, `category`, `scope`, `created_at`; never deleted by reimport |
| `crm_templates`, `crm_campaigns`, `crm_campaign_events`, `crm_campaign_recipients` (unique `campaign_id`,`contact_id`), `crm_email_events`, `crm_delivery_state` | As in plan §3; created in Phase 3/4, not Phase 1 |

All `down()` methods drop the tables, so the migrations are reversible. Money is not involved. Times use `timestampTz`.

## 4. Decisions

- SMTP provider: Resend (`smtp.resend.com`, port 2587). Plan tier, quotas and webhook use still to confirm.
- Buyer emails from orders and customers join the CRM. A purchase does not count as marketing consent.
- Event shortcuts (Import / Email Participants): admin only.

## 5. Phase 2 behavior worth knowing

- Import never overwrites a contact's existing name or phone; it only fills empty values. Event-specific bib, division and extra fields take the newest non-blank value.
- "Granted" consent requires written evidence, a category and a scope (this event or WiVor). An import never re-grants a category the person revoked or is suppressed from.
- `crm:import-buyers` takes customer accounts and **paid** order emails only. It records no consent and no event participation. It is a manual command, not scheduled.
- The "Email Participants" shortcut is left out until Phase 3 builds campaigns, instead of linking to a placeholder.
- Upload files are deleted 30 days after an import finishes; imports never started within 30 days are marked failed and their file deleted.

## 6. Open risks

- Marketing consent for imported participants is unknown; promotional/newsletter sending will be blocked for every imported contact until real evidence exists.
- Queue default `sync` in config: if production `.env` also says `sync`, imports and sends would run inside web requests. Must confirm production uses `database`.
- Running CRM sends through the same SMTP account as receipts and password resets: quotas must reserve capacity for transactional mail.
- SMTP acceptance is the only delivery signal available unless the provider offers webhooks.

## 7. Task checklist

- [x] Inspect framework, events, roles, mail, queue, scheduler, UI, tests
- [x] Document architecture and collisions (this file)
- [ ] Confirm SMTP provider, quotas, SPF/DKIM/DMARC, feedback options (needs owner input)
- [x] Approve schema and new dependencies (2026-10-29: tables approved; `symfony/html-sanitizer` approved, installed in Phase 3 when first used)
- [x] Phase 1: migration `2026_10_29_000000_create_crm_directory_tables`, models, `access-admin` gate reuse, CRM dashboard, tests
- [x] Phase 2: CSV upload (private `local` disk, 5 MB, encoding and delimiter detection), column mapping with presets, preview, queued idempotent import (`ProcessCrmImport`), status/retry, spreadsheet-safe error report, contacts and event participant pages, admin-only event shortcuts, `crm:import-buyers`, daily `crm:purge-import-files` (30-day retention)
- [ ] Phase 3: consent, audiences, templates, campaigns
- [ ] Phase 4: delivery policy, dispatcher, unsubscribe
- [ ] Phase 5: reporting, QA, rollout docs
