WiVor CRM — Laravel Implementation Plan for Local AI
Status: Approved requirements / development specification
Date: October 29, 2026
Project: Existing WiVor Photos Laravel application
Goal: Build a centralized contact CRM and safe HTML email campaign manager integrated with existing event administration.

Golden rule is /home/junior/projects/CLAUDE.md

0. Instructions to the implementing AI
   You are implementing this feature inside the existing WiVor Laravel application. First inspect the real codebase, migrations, models, roles, events, participants, mail configuration, queues, frontend conventions and tests. Do not assume any existing model, table, framework version, authorization mechanism, SMTP provider or infrastructure. Reuse established conventions and avoid disrupting event administration, participant management, photo searches, purchases, or photographer workflows.

Before coding, create a short docs/crm/IMPLEMENTATION_NOTES.md recording discovered architecture, relevant existing components, proposed migration changes, open risks and a task checklist. Implement incrementally by phase. Run relevant tests and report results. Do not silently alter unrelated functionality. Never expose or log SMTP credentials, private participant CSV files or full message recipient data.

1. Product requirements — confirmed

- Access: Only the application's existing administrator role may access CRM routes, data, CSV uploads, campaigns and analytics. No new super-admin role. Enforce authorization server-side, not only in UI.
- Existing event model: Each event already has an administration panel and its own participants. CRM links to those existing events and should not replace their current participant logic.
- CSV imports: An administrator uploads event participants from CSV files. Files differ in column names and layouts. Provide mapping, preview, validation, results and repeatable mapping presets.
- Master contact directory: The same email may participate in many events, but has one canonical CRM contact. Keep event-specific data (bib, division, etc.) separate.
- Audiences: Send to one event, several selected events, or eligible contacts in the master directory. Deduplicate within every campaign.
- Types of messages: Event/photo notifications, promotional campaigns and newsletters, classified separately for permission and compliance.
- Email service: Reuse existing SMTP transport, after checking its actual bulk-mail permissions and provider limits.
- Content: Rich HTML email editor with reusable templates and optional HTML source, safe personalization, mobile preview, test send and mandatory plain-text alternative.
- Delivery: The application—not administrators—sets sending intervals and provider-aware hourly/daily quotas. Admins may schedule, pause, resume or cancel, but cannot bypass delivery safety policies.
- Tracking: Campaign progress, SMTP acceptance, failures, opens, clicks, unsubscribes, and delivery/bounce/complaint data only when genuinely available.
- Expected scale: Typically 500–5,000 participants per event. Imports and sends must run asynchronously.
- Consent: Whether participants have agreed to WiVor marketing remains unverified. Import alone does not grant marketing consent. Unknown marketing consent is ineligible for promotional/newsletter sending.

2. Functional modules
   A. CRM dashboard
   Show unique contacts, events linked, recent imports, eligible marketing contacts, scheduled/running campaigns, queue and SMTP health, and recent errors. Clearly distinguish hypothetical/unknown vs measured delivery counts.
   B. Contact management

- Search and filter by name, normalized email, event, import, consent, suppression, and engagement.
- Contact details: identity, linked events, per-event bib and metadata, source/import provenance, consent timeline, campaign activity and suppression records.
- Store original/canonical names carefully; do not overwrite good existing values with empty or lower-quality import fields.
- Support contact correction, opt-out, reasonable data retention and deletion workflows according to existing policies.
  C. Event participant CSV import

1. On an existing event, show Import Participants and Email Participants links visible only to administrators.
2. Accept CSV upload to private storage, verify size/type/encoding, delimiter and headers, and defend against malformed or excessive input.
3. Allow column mapping for email, first/last/full name, bib number, phone, division/category and optionally approved extra fields. Ignore unmapped columns; support reusable mapping presets.
4. Preview e.g. first 20 rows and show estimated counts for valid addresses, duplicates, missing/invalid fields and current CRM matches.
5. Explicitly record data source, organizer, import timestamp and consent evidence/status; do not permit a generic checkbox to manufacture consent evidence.
6. Import via queued chunks with idempotent upserts. Match by normalized email; link to selected event. An existing global unsubscribe/complaint remains in force.
7. Show import state (queued/running/completed/failed), counts, error CSV, and safe retry. Re-running an import must not duplicate event associations.
8. CSV files remain private, are never directly served publicly, and have a defined retention/deletion policy. Prevent spreadsheet formula injection in exported error CSVs.
   Edge cases: missing headers, duplicate email rows, blank email, different delimiters/encodings, invalid dates/bibs, collisions with existing participants, same athlete in many events, import interruption and retry.
   D. Campaigns / audience builder

- Campaign CRUD: internal title, purpose/type, subject, preview text, sender identity, HTML and text bodies, selected template, chosen audience, scheduling, status.
- Audiences: one event, multiple events, master CRM; show deduplicated and eligible recipient count before confirmation.
- On scheduling, create a reproducible recipient snapshot with one (campaign_id, contact_id) unique record. Re-check current consent and suppression immediately before each send.
- Personalization allowlist: {{first_name}}, {{last_name}}, {{event_name}}, {{event_date}}, {{event_url}}, {{photo_gallery_url}}, {{bib_number}}, {{unsubscribe_url}}. Use safe escaping, legitimate link generation and sensible fallbacks. When targeting multiple events, ambiguous event-specific variables must be resolved explicitly or disallowed.
- Lifecycle: draft → scheduled → preparing → sending → completed, with paused/cancelled/failed paths. Include test-send workflow that cannot accidentally trigger a full campaign.
- Avoid exposing participant email addresses to unauthenticated recipients or in analytics URLs.
  E. Email composer and templates
- Provide a usable HTML WYSIWYG/block editor consistent with current UI; optional sanitized HTML source mode.
- Templates support WiVor branding, responsive layout, images/links/CTA, text fallback, preview and test email.
- Sanitize unsafe HTML and URLs. Do not execute user-authored HTML using Blade/PHP or unrestricted server template engines. Avoid arbitrary template expressions.
- Include accurate sender identification, postal address where required, preferences/unsubscribe mechanisms for promotional emails, and appropriate links. Do not invent a postal address: pull it from verified configuration and block activation until required information is provided.
- Do not include tracking pixels/redirects without a privacy and compliance review.
  F. Deliverability, suppression and preferences
- Separate operational event notices from marketing newsletters/promotions. Permission rules must be designed and reviewed for each category; event participation by itself is not blanket authorization for promotions.
- Record permission source, scope (WiVor vs specific organizer/event), purpose, evidence, timestamp, policy/version if available, and revocation history.
- Unknown marketing consent means do not send marketing. Consent scope cannot be silently expanded by another import.
- Use properly authenticated domain and outbound configuration (SPF, DKIM, DMARC; verify with provider). Maintain accurate sender identities and anti-spoofing practices.
- Implement working, signed, unguessable unsubscribe/preference links; honor opt-outs before any further queued sends. Support one-click unsubscribe headers for qualifying marketing messages (RFC 8058) where applicable.
- Global suppression for hard bounces and spam complaints when events are provided. Support manual administrative suppression. Distinguish event-specific preferences from global marketing suppression.
- Confirm the legal/compliance requirements of the jurisdictions served (e.g. U.S. CAN-SPAM). Never imply that rate-limiting alone guarantees inbox placement.
  G. Central SMTP delivery engine
- Reuse Laravel mail configuration and SMTP credentials from environment. Do not commit or print secrets.
- Inspect actual provider's terms, per-message recipient limits, daily/hourly quotas, complaint/bounce monitoring, TLS authentication and outbound sender restrictions. If these cannot be verified, default to a conservative paused/limited state and flag for administrator review, rather than inventing a safe provider limit.
- Central service (CrmMailDispatcher / DeliveryPolicyService) controls all CRM campaigns using one global limiter, even when multiple workers/campaigns run concurrently. Prefer Redis-backed atomic rate limiter or robust database locking depending on installed infrastructure.
- Admins cannot configure intervals or bypass caps. Code/configured delivery policy enforces provider limits, gradual warm-up, approved sending hours if applicable, and reserved capacity for other application emails sharing the SMTP account.
- Queue eligible recipients and dispatch at system-chosen intervals. Use supervisor/systemd and existing Laravel scheduler; don't block web requests.
- Retry temporary 4xx/provider errors with exponential backoff + jitter and caps; do not retry permanent invalid recipient errors. Pause on authentication failures, quota exhaustion, unusual error rates or complaint alarms. Make resumption safe.
- Store attempted/SMTP-accepted/temporarily failed/permanently failed/unknown statuses separately. SMTP acceptance is not proof of inbox delivery. If the transport disconnects ambiguously, account for possible duplicate sends; don't blindly retry without a documented policy.
- Handle provider webhooks or mailbox delivery-status notifications only if actually supported by the current SMTP service. Label missing final-delivery visibility accurately.
  H. Reporting / engagement analytics
- Campaign summary: total intended, skipped ineligible, queued, attempted, accepted by SMTP, errors, opted out, unique and total opens/clicks where instrumented.
- Track opens via optional privacy-reviewed pixel and clicks via signed redirect URLs when enabled. Warn in UI that opens can be inaccurate (privacy proxies/image blocking) and clicks can be security bot-generated.
- Use strong random tokens, reasonable event retention, and data minimization; never expose arbitrary redirects.
- A later phase may correlate actual photo purchases via campaign attribution, but do not claim conversions based merely on clicks.

3. Suggested schema (adjust to actual existing structures)
   Use crm\_ prefixed tables where they avoid collisions; inspect migrations before choosing exact columns/types/keys.
   Table Key responsibilities / recommended constraints
   crm_contacts id, normalized unique email, original email, names, phone, timestamps, contact attributes
   crm_event_contacts FK event/contact; unique(event_id, contact_id); bib, division, source and event-specific metadata
   crm_imports Event, operator, private file path, file hash, mapping JSON, status, row counts, error manifest
   crm_import_rows (optional) Staging/errors for large imports and reproducible retries; avoid indefinite PII retention
   crm_contact_consents Contact, permission category/scope, status, source, evidence, timestamp, version, revocation
   crm_suppressions Contact/email, reason (unsubscribe/bounce/complaint/manual), category/scope, timestamp
   crm_templates Name, subject/preview, HTML/text, editor metadata, version
   crm_campaigns Type, title, content, schedule, state, operator, delivery/recipient counters
   crm_campaign_events Selected event FK per campaign (for provenance/filtering)
   crm_campaign_recipients Campaign/contact FK, unique(campaign_id, contact_id), chosen event context, status, timestamps, attempt count
   crm_email_events Recipient/message event type, timestamp, metadata, provider ID when available
   crm_delivery_state Global limiter/quota state and pause/circuit-breaker metadata (or Redis-backed state)

Use indexes on normalized email, event link FKs, import status, campaign status/due time, and campaign recipient status/next eligible time. Follow the existing primary key conventions. Never hard-delete compliance suppression history simply because a CSV is reimported. 4. Code organization (illustrative, not mandatory)
app/
Models/CRM/
Http/Controllers/Admin/CRM/
Http/Requests/Admin/CRM/
Policies/
Services/CRM/
ColumnMappingService.php
ContactImportService.php
ContactMergeService.php
ConsentService.php
AudienceBuilder.php
CampaignService.php
DeliveryPolicyService.php
CrmMailDispatcher.php
TrackingService.php
Jobs/CRM/
ProcessImportChunk.php
PrepareCampaign.php
SendCampaignRecipient.php
ProcessDeliveryFeedback.php
Mail/CRM/
CampaignMail.php
Use existing frontend/component, route, logging and testing conventions instead of force-fitting this layout. 5. Navigation and UX
Under existing administrator navigation, add CRM with Dashboard, All Contacts, Event Lists, CSV Imports, Email Campaigns, Email Templates, Analytics, Suppression List, Delivery Logs. Under existing event admin pages add Import Participants and Email Participants shortcuts prefiltered to that event.

Every destructive or large-volume action should require clear confirmation and explain how many eligible recipients are affected. Present progress for long-running imports and campaigns. Do not expose controls to adjust the system's SMTP interval or override deliverability restrictions. 6. Delivery phases / task order
Phase 1 — Inspect and establish foundation

- [ ] Inspect actual Laravel version, event/participant tables, existing roles/middleware, database, mail, queues, cron, workers, and application UI.
- [ ] Document existing architecture and potential collisions.
- [ ] Confirm actual SMTP provider, permissions, quotas, domain authentication, and feedback options; identify any blockers.
- [ ] Implement migrations, models, associations and admin-only access policy.
- [ ] Add migration/unit/authorization tests.
      Phase 2 — CRM directory and CSV imports
- [ ] Implement private CSV storage, upload checks, dialect/encoding detection.
- [ ] Build mapping UI, presets, preview, validations, dry-run report.
- [ ] Implement idempotent queued chunks and contact/event deduplication.
- [ ] Add import progress, retries, summaries and downloadable safe error reports.
- [ ] Add master contacts list/detail and event-specific participant view.
- [ ] Test 5,000-row import, reimport, malformed files, duplicates and interrupted jobs.
      Phase 3 — Consent, audience and campaign composer
- [ ] Implement permission/consent evidence records, preference and suppression policies.
- [ ] Implement audience selection with correct deduplication and eligibility preview.
- [ ] Build templates, safe HTML editor, preview, text fallback and test-send.
- [ ] Implement campaign CRUD, schedules, statuses and recipient snapshot.
- [ ] Add tests for missing consent, revocation, cross-event duplicates and ambiguous event variables.
      Phase 4 — SMTP-safe dispatch
- [ ] Implement global, atomic system-controlled pacing and caps without admin tuning.
- [ ] Configure queued workers/scheduler; provide safe production deployment instructions.
- [ ] Integrate existing SMTP, retries, transient/permanent error handling and auto-pauses.
- [ ] Implement real unsubscribe/preference endpoints; attach appropriate headers and footer.
- [ ] Verify no campaign can exceed global quotas even with concurrent workers.
- [ ] Test SMTP temporary errors, permanent rejections, ambiguous disconnects and cancellation mid-send.
      Phase 5 — Analytics, QA and rollout
- [ ] Build admin campaign reporting and recipient logs with honest delivery statuses.
- [ ] Implement privacy-reviewed click/open tracking (if approved) and any actual provider feedback integration.
- [ ] Test authorization, CSV security, XSS, injection, unsubscribe, opt-out timing and PII access.
- [ ] Run a small permission-verified internal campaign before increasing production volume.
- [ ] Document deployment, rollback, operating limits and troubleshooting; verify queue workers and monitoring.

7. Acceptance criteria

- [ ] Only existing administrator users can reach any CRM UI, endpoint, job action or data export.
- [ ] An event CSV of 5,000 rows imports asynchronously without HTTP timeouts.
- [ ] Column mappings support organizer-dependent CSVs and produce a useful validation preview.
- [ ] Reimporting does not duplicate a master contact or its event association.
- [ ] One athlete in multiple events has one contact and retains event-specific bib metadata.
- [ ] Multi-event campaigns do not send duplicate copies to one address.
- [ ] Unknown marketing consent blocks newsletters/promotions; proof is preserved when consent exists.
- [ ] Revocation/suppression is checked at send time; queued marketing messages do not go out after opting out.
- [ ] HTML messages are sanitized, have text equivalents, safe personalization and functioning opt-out.
- [ ] Admin can create, test, schedule, pause, resume and cancel campaigns.
- [ ] Admin cannot override system intervals; all campaigns obey a central, concurrency-safe delivery policy.
- [ ] SMTP acceptance is not labeled as confirmed inbox delivery.
- [ ] Failure, retry, quota, unsubscribe and cancellation cases have automated tests.
- [ ] No regression to existing events, photos, participant workflows, payments or photographer functions.

8. Security and operational standards

- Authorize every controller/action/resource against existing roles, including import exports and tracking analytics.
- CSRF protection, file validation, upload caps, server-side pagination and rate-limited public unsubscribe endpoints.
- Validate and normalize emails; avoid storing unnecessary PII. Private participant files, restricted analytics, encryption/retention rules as appropriate.
- Escape rendered personalization and sanitize template HTML. Prevent server-side template injection and malicious URLs.
- Queue jobs are idempotent; key tables have uniqueness constraints and indexes; failures are observable.
- Document environment variables by name only; do not output their current secret values.
- Do not overwrite existing participant, photo, or purchase information without documented migrations and tests.

9. Deployment documentation required from implementing AI
   Produce, at minimum:
1. A list of changed/created files and migrations.
1. Required environment variable names, queue worker setup, cron/scheduler changes and SMTP provider requirements.
1. The exact commands to migrate, restart workers, clear caches where needed, run tests and roll back.
1. A short admin guide: import a CSV, choose an event audience, send a test, schedule and monitor a campaign, process unsubscribes.
1. Known limitations (especially missing SMTP delivery feedback or unresolved marketing consent), and any features gated pending verification.
1. Non-goals for this first release

- No replacement of the existing events or participant subsystem.
- No new super_admin role or permission model unless existing project architecture genuinely demands adapter changes.
- No administrator setting for SMTP delays or batch size.
- No claim that paced SMTP sending guarantees non-spam delivery.
- No assumption that event participation gives WiVor consent for general marketing.
- No CRM-wide automatic promotional blast until eligibility and sending policies are verified.
- No advanced marketing automation funnels or photo-purchase revenue attribution in v1.

11. First action to take now
    Inspect the project and start with Phase 1. Summarize the actual existing event/participant models, administrator authorization mechanism, mail provider and queue infrastructure. Highlight discrepancies with this design, then proceed with migrations, tests and the first CSV import iteration using existing architectural conventions. If verified SMTP limits or marketing permission are unavailable, build the feature with sending appropriately restricted rather than guessing or ignoring the constraint.
