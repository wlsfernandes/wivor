# WivorPhotos Frontend MVP Launch Audit

Audit date: 2026-09-12  
Scope: analysis only; current repository code is the primary source of truth, supplemented by a limited live-site crawl. No live charges were made and no production data was changed.

Status vocabulary used in feature tables:

- **ACTUAL** — implemented in the current code.
- **PARTIAL** — some implementation exists, but the experience or claim is broader than the implementation.
- **NOT IMPLEMENTED** — no supporting implementation was found.
- **INTERNAL / DISABLED** — technically present but not available as a normal public feature.
- **UNCLEAR** — the repository alone cannot establish production behavior.

## A. Executive Summary

WivorPhotos is currently a photo-only event marketplace. A guest customer can find a published event, search its published gallery by bib number, view watermarked derivatives, add photos to a single-event cart, pay through Stripe Checkout, and receive time-limited access to purchased originals. Photographers can apply, be approved and assigned, upload qualifying JPEGs, publish processed photos, review sales allocations, and complete Stripe Connect onboarding. Admins can manage the event, photographer, media, policy, removal-request, and order-reconciliation parts of that lifecycle.

The MVP does **not** sell or process video. It also does not provide customer accounts/order history, “notify me” alerts, an in-app refund action, automated payout transfers, or robust order-link recovery/resending. Facial search is implemented but disabled by default for customers; importantly, publication still queues face indexing even when that public feature flag is off.

The active homepage is substantially more accurate than the older priority list implies. Its browsing, protected-preview, card-payment, receipt, and download story matches the code. The material overstatement is photographer “earnings/payout” language: sales allocations and status exist, but no code creates a photographer transfer. Public login/signup content is a larger truth problem than the homepage: it promises customer-account features that are absent and links into a broken/inconsistent customer registration/dashboard path. Legal routes exist and are in the footer, but may render placeholder content; no distinct photographer terms document exists.

The purchase and photo-security architecture appears complete in code and has meaningful automated coverage. It is not equivalent to a verified production transaction or verified production S3 policy. Those two live checks remain launch gates.

## B. Current Customer Functionality

| Feature | Status | Main files/routes | Notes |
|---|---|---|---|
| Homepage and event discovery | ACTUAL | `GET /`; `HomeController::welcome`; `resources/views/site/welcome.blade.php` | Shows recent published events and working title/city/sport plus state, city, sport, and date filters. |
| Event directory | ACTUAL | `GET /list-events`; `EventController::listEvents` | Lists/searches public events; visibility is constrained by event publication state. |
| Event detail/gallery | ACTUAL | `GET /events/{event:slug}`; `EventController::show`; `resources/views/events/post-show.blade.php` | Only published events and published photos are exposed. Sales-close state suppresses purchase/gallery behavior. |
| Bib-number search | ACTUAL | `GET /events/{event:slug}?bib_number=...`; `DetectPhotoBibNumbers`; `PhotoBibNumber` | Exact, event-scoped matches against detected 1–5 digit bib values. Customer UI is visible without a feature flag. Production AWS/queue operation still needs a smoke test. |
| Selfie/face search | INTERNAL / DISABLED | `POST /events/{event:slug}/face-search`; `EventFaceSearchController`; `config/face_recognition.php` | Implemented and consent-gated, but `FACE_RECOGNITION_ENABLED` defaults to false. See Section D for the indexing lifecycle concern. |
| Photo detail and protected preview | ACTUAL | `GET /events/{event:slug}/photos/{photo}`; nested `.../image.jpg`; `PhotoDeliveryController` | Public delivery uses a separate watermarked preview object, not the original key. |
| Guest cart | ACTUAL | `GET /cart`; cart add/remove/clear routes; `CartController` | Session cart; no account required. The cart is limited to one event, while items may belong to multiple assigned photographers. |
| Event-level photo pricing | ACTUAL | event form/model; `CheckoutService` | Current event price is displayed in the cart and frozen into order items at checkout. |
| Stripe Checkout | ACTUAL | `POST /checkout`; `CheckoutController`; `CheckoutService` | Hosted card checkout, server-calculated totals, idempotency token/key, platform-account collection. No live charge was run in this audit. |
| Order success/status | ACTUAL | `GET /checkout/{order}/success`; `CheckoutController::success` | Confirms the Checkout Session and waits briefly for webhook fulfillment; a delayed webhook may require a manual refresh/email arrival. |
| Webhook fulfillment | ACTUAL | `POST /api/stripe/webhook`; `StripeWebhookController` | Signature-verified and idempotent. Validates immutable metadata, amount, and currency before marking paid and granting entitlement. Handles expiration, full refunds, and disputes. |
| Receipt email | ACTUAL | `OrderReceiptMail`; `resources/views/emails/order-receipt.blade.php` | Includes the protected order link. Send failures are caught and logged; there is no retry queue or customer/admin resend function. |
| Secure original download | ACTUAL | `GET /orders/{accessToken}`; item download route; `OrderController` | Requires a random 64-character access token, paid/ready item, and unexpired entitlement, then redirects to a short-lived signed S3 URL. Default entitlement is 90 days; signed delivery URL is 5 minutes. |
| Order recovery | PARTIAL | receipt email and order-access route | The emailed/success-page token link works, but there is no email/order-number lookup, account order history, lost-link recovery, or resend workflow. |
| Customer account/signup | PARTIAL | `GET /signup`; `POST /registerUser`; `LoginController`; `CustomerController` | Public copy promises customer-account benefits, but the registration method is protected, dashboard redirect/path names disagree, and the dashboard view expects unrelated/missing post data/routes. The MVP checkout itself correctly remains guest-based. |
| Photo-removal request | ACTUAL | `GET/POST /photo-removal`; removal request controller/admin review | Public request and admin review flow exist. |
| FAQ/help/contact | ACTUAL | `/faq`, `/contact`; public footer/header | FAQ describes guest checkout; contact form and `contact@wivorphotos.com` default are present. |
| Privacy, terms, refund pages | PARTIAL | `/privacy`, `/terms`, `/refund-policy`; `PolicyController` | Routes and footer links exist. Database-managed policy content may fall back to explicit “preparing” placeholders; production content could not be established locally. |
| “Notify me when photos are available” | NOT IMPLEMENTED | No customer notification route/model/form found | Event availability dates and empty states exist, but no signup/subscription or notification delivery flow was found. |
| Video browsing/purchase/download | NOT IMPLEMENTED | No active customer commerce implementation | WivorPhotos is photo-only in the current code. |

## C. Current Photographer Functionality

| Feature | Status | Main files/routes | Notes |
|---|---|---|---|
| Public photographer application | ACTUAL | `GET /photographers`; application POST route/controller/view | Collects identity/contact/business fields, age confirmation, acceptance, and a password. Email verification and admin review follow. |
| Login, verification, approval gate | ACTUAL | auth routes/controllers; approved/verified route middleware | Dashboard and operational routes require verified and approved status. Admin can approve, decline, suspend, and restore. |
| Photographer dashboard | ACTUAL | `/photographers/dashboard`; `PhotographerController` | Summarizes assigned events, upload state, sales, and payout setup/status. |
| Event creation/management | ACTUAL | photographer event routes/controllers/views | Approved photographers can create/manage their own events; admin can assign approved photographers to events. Publication/archival is implemented. |
| Event participation/assignment | ACTUAL | admin assignment routes; event assignment relations | Upload authorization is limited to owned/assigned events. There is no open self-join marketplace flow; assignment/ownership is the actual participation model. |
| Direct JPEG upload | ACTUAL | assigned upload routes; `PhotographerUploadController`; upload views | Signed direct upload with rights confirmation. JPEG only, up to 40 MB, RGB, minimum 2400 px long side, maximum 12000 px side; batch/event limits are enforced. RAW and video are excluded. |
| Processing/status | ACTUAL | `ProcessPhoto`; processing jobs/status UI | Creates thumbnail/watermarked preview, records failures/duplicates/checksum state, and supports incremental publication. Queue and AWS operation require production monitoring. |
| Bib detection | ACTUAL | `DetectPhotoBibNumbers`; `BibRecognitionService` | Runs after processing, stores detected bib values/confidence, and does not block photo usability on recognition failure. |
| Publishing | ACTUAL | assigned upload publish actions; `PhotographerUploadController` | Ready photos can be published into a published event/gallery. Publication currently dispatches face indexing unconditionally. |
| Photographer photo removal | PARTIAL | photographer upload removal action; admin media actions | Photographer can remove unsold, unpublished queued/uploading/ready/rejected items. Published-photo unpublish/removal requires admin action. |
| Sales reporting | ACTUAL | `/photographers/sales`; `PhotographerController` | Filters and reports gross, commission, photographer allocation, and a transfer-derived payout status. |
| Stripe Connect onboarding/status | ACTUAL | payout setup/status/dashboard routes; `StripeConnectService` | Connect account creation, onboarding links, capability state, and Express Dashboard link are implemented. |
| Payout execution | PARTIAL | `CheckoutService`; `OrderItem::stripe_transfer_id`; sales view | Funds are collected on the platform account and allocations are retained for a manual payout process. No application code creates Stripe transfers; tests only set transfer IDs as fixtures. “Receive earnings” must not imply automated payout. |
| Photographer terms | PARTIAL | application view links `/terms` | The form labels the general terms route as photographer terms. No distinct photographer agreement/document/route was found. |
| Profile/settings management | NOT IMPLEMENTED | No dedicated photographer profile/settings routes found | Existing account/onboarding data is displayed, but a normal photographer profile/settings editing flow was not found. |
| Video upload/sale | NOT IMPLEMENTED | upload validator/UI | The upload page explicitly rejects video/RAW; the processing and commerce pipeline is JPEG-photo-only. |

Admin capabilities relevant to claims: event/assignment/pricing/publication management, photographer review, media retry/unpublish/remove/retention holds, removal-request review, Stripe order reconciliation, and policy editing are implemented. Admin cannot initiate an in-app refund, execute a photographer payout transfer, resend a receipt/recovery link, or use a full customer/order-support detail workflow. External full refunds/disputes are reflected through Stripe webhooks. Media retention is scheduled daily at 02:15 by `media:enforce-retention`.

## D. Search / AI Functionality

### Bib Recognition

**Classification: ACTUAL** (implemented and customer-visible; production AWS/queue operation still needs verification).

- `ProcessPhoto` dispatches `DetectPhotoBibNumbers`, which calls Amazon Rekognition DetectText against the original S3 object.
- Detection accepts exact 1–5 digit, non-zero values above the configured confidence threshold (default 80) and stores them in `photo_bib_numbers`.
- The event page always exposes bib search. It is event-scoped and exact-match; it only returns published photos from the requested published event.
- A no-match result is friendly: “No photos were found for bib #… yet.” The UI does not distinguish a genuine no-match from a photo whose recognition job failed or has not run.
- Recognition failure does not make a processed photo unusable. That is a sound fallback, but operations should monitor scan failures before calling the feature universally reliable.

### Facial Recognition

**Classification: INTERNAL / DISABLED for public launch by default; PARTIAL as a launch-ready lifecycle.**

- The customer UI and POST route exist, but both require `FACE_RECOGNITION_ENABLED`; the default is false.
- The form requires explicit consent, accepts JPEG/PNG selfies up to 5 MB, states that Amazon Rekognition processes the image, and says WivorPhotos does not save it.
- The controller reads the request-temporary bytes and sends them directly to Rekognition. No selfie database record, application storage object, session payload, or queued selfie was found; automated coverage verifies that search results are not stored in session.
- Search is restricted to one published event collection and published photo matches. Defaults are 90 similarity and 50 results.
- **Launch-blocking lifecycle issue:** publishing a photo dispatches `IndexPhotoFaces` regardless of the feature flag. Turning off public face search therefore does not turn off backend biometric indexing, AWS collection creation, or cost/privacy exposure.
- Admin photo unpublish/removal and retention deletion dispatch face cleanup. Changing an event to draft/archive does not appear to clean its indexed faces, and no event-collection deletion lifecycle was found.
- Customer selfie consent does not address consent/legal basis for indexing people depicted in uploaded event photos. This needs a deliberate product/privacy decision and aligned policy before public enablement.

Recommendation: keep customer face search hidden for the initial launch; separately stop or explicitly authorize indexing when the feature is disabled, define event/photo deletion behavior, confirm AWS data handling, and complete legal review before advertising it.

## E. Homepage Claim Audit

| Section | Current claim | Reality | Action | Priority |
|---|---|---|---|---|
| Hero | “Find Your Event Photos” for sports/fitness events | Published event search and gallery browsing exist; event validation is US-oriented. | KEEP | P0 validation only |
| Hero CTA/navigation | Browse events / find photos | Routes and published-event listings work. | KEEP | — |
| Filters | Search by event/title/city/sport and filter state/city/sport/date | Backed by `EventController` queries. | KEEP | — |
| Recent events | Current published events | Controller supplies three recent published events. Limited live crawl showed one plausible real event, not the checkout test fixture. | VERIFY production dataset | P0 |
| How it works — find event | Locate the event and photos | Actual. | KEEP | — |
| How it works — protected previews | Choose from protected previews | Separate watermarked previews are used in code. Production bucket policy still needs verification. | KEEP, then VERIFY infrastructure | P0 |
| How it works — pay | Secure Stripe payment | Hosted Stripe Checkout is implemented and signature-verified fulfillment is tested. | KEEP, then VERIFY live transaction | P0 |
| How it works — download | Use success/email protected link to download originals | Actual token/entitlement flow in code. Email delivery and S3 redirect need end-to-end production verification. | KEEP, then VERIFY live transaction | P0 |
| Who we are | Event photography marketplace with approved photographers | Registration, verification, admin approval, event assignment, uploads, and photo sales exist. | KEEP | — |
| Photographer workflow | Create/manage assigned events, upload JPEGs, publish photos | Actual within ownership/assignment and approval rules. | KEEP | — |
| Photographer value | Track sales/earnings and receive earnings/payout status | Sales allocations and payout status are shown; no code creates a transfer, so “receive earnings” is broader than the automated product. | REWORD to distinguish tracked allocation/manual payout from Connect onboarding | P0 |
| Pricing/commission | Per-photo event price and clear commission | Event-level price and stored commission/allocation exist. | KEEP; avoid implying per-photo custom prices | P0 copy precision |
| AI/search | No broad “find every photo instantly” or active face-search promise found on the current homepage | Bib search is public. Face search is disabled by default and must remain unadvertised while disabled. | KEEP current restraint | P1/P2 |
| Video | No active homepage claim that Wivor sells video | No video implementation. | KEEP current photo-only language | P0 |
| Statistics/testimonials/logos | No active stats/testimonial/logo proof section found on the current homepage | No unsupported proof claims on the active page. | KEEP absent | — |
| Contact | Public contact form/details | Implemented. | KEEP | — |
| Footer | WivorPhotos copyright and legal/support links | Current active public footer source is correct, but a live `/login` crawl returned old `devpromaster` branding. | VERIFY deployment/cache and remove stale sources | P0 |

## F. Video References

No meaningful active customer-facing page claims that WivorPhotos supports video commerce.

| Location | Reference and exposure | Finding | Future disposition |
|---|---|---|---|
| `resources/views/photographers/upload.blade.php:71` | “No RAW files or video.” Visible to authorized photographers. | Accurate negative scope boundary, not a capability claim. | **REWORD** only if desired to say “JPEG photos only”; otherwise retaining it is safe. |
| `resources/views/apagar.blade.php:1025` | “Video Editing.” Stale template reachable, at most, through the authenticated generic view catch-all. | No backing video workflow; dead/template content. | **HIDE**, then **REMOVE** when legacy templates are safely cleaned. |
| `resources/lang/en/translation.php:68`, `de/translation.php:68`, `it/translation.php:68` | Generic unused `Video` translation key. | No active route or product behavior found using it. | **REMOVE** in a later dead-content cleanup; no launch copy change required. |
| `resources/views/partials/cards.blade.php:9,30,54,77` | CSS class name `video` on `<figure>` elements. | Styling identifier only; it makes no user-visible video promise. | Optional **REMOVE/rename** during cleanup, not a launch task. |
| `resources/views/layouts/app.blade.php:17` | `max-video-preview:-1` inside the robots meta directive. | Search-engine preview directive, not a marketplace feature. | **REWORD/REMOVE** is not required; do not treat as product copy. |
| `resources/views/template/ui-video.blade.php` and template assets | Admin-theme demonstration page. | Scaffold/template content, not part of the customer marketplace. | **HIDE** from all routable surfaces and **REMOVE** with other unused template material later. |

## G. Production Test / Placeholder Content

| Route/page | Source | Evidence and exposure | Required action |
|---|---|---|---|
| Live homepage `/` | [https://wivorphotos.com/](https://wivorphotos.com/) | Limited crawl on 2026-09-12 showed “Tugaloo Olympic, Sprint and Aquabike,” not an obvious demo/test event. This is not proof that the production database has no other fixtures. | Query production events/photos by known fixture identifiers before launch; do not delete anything until ownership is confirmed. |
| Potential public event `/events/wivor-checkout-test-event` (slug created by seeder) | `database/seeders/CheckoutTestSeeder.php:21-222` | Seeder deletes existing orders/events/photographers and creates a published “Wivor Checkout Test Event,” fake photographer/account, venue, and five gallery assets. It is not called by `DatabaseSeeder`, so it is not automatically exposed; if ever run in production it is both destructive and public. | Confirm the slug/account are absent in production; later add an environment guard and move destructive fixtures to test-only infrastructure. |
| Privacy/terms/refund routes | `app/Http/Controllers/Frontend/PolicyController.php`; `resources/views/policies/show.blade.php` | When a database policy is missing/blank, a public “preparing its full … Policy” placeholder is rendered. Local configured PostgreSQL policy rows were unavailable, so production content is **UNCLEAR**. | Verify all three production policy records before launch. |
| Public login/signup | `resources/lang/en/header.php:17`; `resources/views/auth/login.blade.php:63-75`; `/signup` | Claims account benefits such as faster checkout, multiple shipping addresses, and order tracking that do not exist in the current guest/photo MVP. This is stale product copy, not legitimate example content. | Hide customer registration/claims for MVP or correct the entire path in a separate, surgical task. Preserve photographer login. |
| `/testimonials` | `routes/web.php`; `HomeController` | Public route points to a controller method that does not exist. It is not linked by the current header/footer but can be requested directly. Older locale files also contain stale organization/testimonial copy. | Remove/disable the dead public route before launch; do not surface stale locale content. |
| `/photobook` | duplicate route definitions; `PhotographerController` | The effective public route targets a missing `PhotographerController::photobook` method. Not part of the MVP navigation. | Remove/disable dead public route; do not advertise photobooks. |
| Auth-only legacy `/test/paypal` | `routes/web.php` and legacy payment controllers | Test-named PayPal route remains behind authentication; actual customer MVP commerce is Stripe. | Hide/remove legacy test and PayPal routes in a later bounded cleanup. |

No fake statistics, sample testimonial block, placeholder customer names/orders, or obvious test event was found on the current active homepage. A code fixture is not classified as production-visible without evidence; the `CheckoutTestSeeder` is reported because its effect would be public and destructive if invoked.

## H. Footer / Branding Audit

All current repository occurrences of `devpromaster`:

| Exact source | Where it renders | Public visibility | Recommended replacement/action |
|---|---|---|---|
| `resources/views/layouts/footer.blade.php:5` | Footer included by `resources/views/layouts/master.blade.php` on authenticated/admin-theme pages | Visible to signed-in admin/internal users; not the active public footer | Replace with `© {year} WivorPhotos. All rights reserved.` or reuse the existing public footer brand component. |
| `resources/views/layouts/site-footer.php:94` | Legacy footer used by `layouts.site`; only the old `site/welcome.old.php` was found extending it | Not expected on the current `/` route, but stale routable/deployable code | Remove the developer credit and retire the unused legacy layout/page after route verification. |
| `resources/views/auth/register.blade.php:134` | Embedded old auth registration footer | Laravel registration route is disabled in `Auth::routes(['register' => false])`; source is stale | Replace if retained, or remove the unreachable legacy registration view in a later cleanup. |

The active source `resources/views/partials/footer.blade.php` already uses WivorPhotos branding and links Privacy, Terms, Refund Policy, Photo Removal, and Contact. However, a limited live crawl of [https://wivorphotos.com/login](https://wivorphotos.com/login) on 2026-09-12 returned an older public layout with “Copyright by devpromaster” and stale customer-registration promises. Current `resources/views/auth/login.blade.php` extends `layouts.app`, so that output does not match the audited repository. Treat this as a deployment/cache/version-drift warning: inspect the page in production after the next deploy, purge relevant caches, and verify the deployed commit before declaring the footer task done.

No other developer/server brand was found in the active public footer. `APP_NAME`, email components, and the current header/footer consistently use WivorPhotos.

## I. Privacy / Terms / Trust Audit

| Document | Route | Exists | Footer linked | Customer flow linked | Photographer flow linked | Recommended action |
|---|---|---:|---:|---:|---:|---|
| Privacy Policy | `/privacy` | Yes, DB-managed with placeholder fallback | Yes | Global footer only; customer signup text mentions it but does not link it | Global footer only | Verify non-placeholder production content; add direct contextual links where personal data/face search is collected. |
| Customer/general Terms | `/terms` | Yes, DB-managed with placeholder fallback | Yes | Global footer only; signup acceptance sentence is plain text, not a link; cart/checkout has no inline link | Application links it but labels it “photographer terms” | Verify production content and link it contextually from relevant customer acceptance/purchase surfaces. |
| Photographer Terms | `/terms` is currently reused | No distinct document | General Terms is linked | Not applicable | Yes, but mislabeled as a specific photographer agreement | Decide whether general Terms genuinely cover photographer rights, commissions, payout timing, licensing, removal, and conduct. Create/link a distinct agreement only if legal/product decides it is required. |
| Refund Policy | `/refund-policy` | Yes, DB-managed with placeholder fallback | Yes | Global footer only; not inline at cart/checkout/order | Global footer only | Publish actual eligibility/process language and link it contextually at purchase/order surfaces. |
| Photo Removal | `/photo-removal` | Yes | Yes | Yes via global navigation/footer | Global footer | Keep; confirm support/admin response ownership and retention SLA. |
| Contact/support | `/contact` | Yes | Yes | Yes via header/footer | Yes via shared layout | Keep; verify production mailbox delivery and ownership. |

“Exists” above means a route/view/controller path exists. It does **not** prove that production has final policy text. `PolicyController` intentionally renders explicit placeholder text when a policy record is missing. Production database content was not available during this audit.

Trust-specific face-search concern: selfie consent is present, but the policies and photographer/event terms must also address biometric indexing of people in uploaded event photos, retention/deletion, Amazon Rekognition processing, and whether indexing is performed while public search is disabled.

## J. Commerce Flow

### Implemented customer path

`Published event/photo` → `session cart` → `server-created Stripe Checkout Session` → `signed Stripe webhook` → `paid order + entitlements` → `receipt email/success link` → `token-authorized order` → `short-lived signed original URL`

| Stage | Code/test evidence | Audit conclusion |
|---|---|---|
| Browse | Published-event/photo scopes in `EventController`, photo routes, and feature tests | Implemented in code/tests. |
| Cart | `CartController`, session cart, event constraint, price/availability validation | Implemented in code/tests. |
| Stripe | `CheckoutController` + `CheckoutService`; hosted card session, server totals, idempotency | Implemented in code/tests; credentials, domain, webhooks, and a real payment were not verified. |
| Webhook | `StripeWebhookController`; signature check, event idempotency, amount/currency/metadata checks | Implemented in code/tests. Production endpoint registration/signing secret and delivery health require verification. |
| Fulfillment | Paid transition, frozen order items, entitlement readiness/expiry, sale counters | Implemented in code/tests and guarded against duplicate webhook fulfillment. |
| Email | `OrderReceiptMail` contains protected order link | Implemented in code/tests. Production mail delivery was not tested. A caught SMTP error is logged but not queued/retried or exposed for resend. |
| Secure download | Access token + paid/ready/unexpired checks; 5-minute S3 temporary URL | Implemented in code/tests. Production bucket policy/CORS/KMS and real browser download require verification. |
| Refund/dispute revocation | Webhook marks full refund/dispute and revokes future downloads | Implemented in code/tests for full external refunds/disputes. No in-app refund action or partial-refund workflow. |
| Photographer allocation/payout | Commission and allocation are frozen in order items; payout label uses transfer ID | Allocation/reporting implemented. Transfer creation is **not implemented**; payout is manual outside the application. |

### Verification result

The repository test run completed **150 tests / 734 assertions with 1 failure and 7 PHPUnit notices**. The single failure is `Tests\Feature\PhotoSeoTest::test_published_photo_has_an_indexable_page_with_credit_people_and_social_metadata`: the page currently emits a `keywords` meta tag while the test asserts that it should not. This appears to be a code/test expectation mismatch rather than a commerce failure, but the suite is not green and must not be reported as fully passing.

No live charge was performed, by instruction. “Implemented in code/tests” must remain distinct from “verified with a live production transaction.” Before launch, run one controlled Stripe test-mode (or explicitly authorized minimal live-mode) purchase using a real production-like event/photo, then verify webhook receipt, single fulfillment, receipt delivery, success link, original download, expiry/revocation behavior, logs, and Stripe dashboard reconciliation.

## K. Photo Security

| Area | Current architecture | Conclusion |
|---|---|---|
| Original storage | Originals use the configured media disk (default `s3`) and an `original_key` stored in the database. Direct signed upload does not intentionally expose the key in customer views. | Sound code path; actual bucket Block Public Access, ACL, IAM, encryption, and logging are **NEEDS PRODUCTION VERIFICATION**. |
| Preview storage | Processing creates distinct preview and thumbnail keys/objects. Both receive a WivorPhotos watermark and are written as private objects. | ACTUAL in code/tests. |
| Watermark behavior | `ProcessPhoto` applies the watermark to customer-facing derivatives; original remains separate. | ACTUAL. Visual legibility/placement on varied photos should be sampled in production. |
| Public preview delivery | `/image.jpg` resolves the authorized photo and streams only `preview_key`, requiring published event/photo and open gallery state. | No path returning `original_key` was found in the active public preview controller. |
| Purchased delivery | Random 64-character order access token plus paid/ready/unexpired item checks; a temporary signed S3 URL is returned for the original. | ACTUAL in code/tests; default signed URL lifetime is 5 minutes and order download eligibility is 90 days. |
| Purchased thumbnail | Order page uses a signed thumbnail URL rather than exposing a public object. | ACTUAL. |
| Legacy storage helpers | `app/Helpers/StorageS3.php` and `FileUploader.php` contain public-URL-style helpers. | No reference from the active photo processing/delivery flow was found. Keep them out of this flow and remove only in a separate dead-code task. |

No direct original-photo exposure was found in the audited customer route/controller/view path. The remaining risk is infrastructure, not evidence of an application leak: the direct-upload request does not force an object ACL, so privacy ultimately depends on production bucket/IAM policy. Confirm that anonymous `GET` of a known original key is denied, that anonymous object listing is denied, and that only application-issued short-lived URLs can retrieve purchased originals. Also confirm CDN/origin caching cannot retain signed original responses beyond authorization.

## L. Updated Priority Matrix

| Priority | Item | Current Status | Required Action | Files Likely Involved | Risk |
|---|---|---|---|---|---|
| P0-1 | Correct homepage MVP claims | PARTIALLY DONE | Keep accurate photo-commerce language; narrowly reword photographer earnings/payout wording and verify production page matches source. | `resources/views/site/welcome.blade.php`, `resources/lang/en/messages.php`, homepage partials | Medium — overstated payouts undermine trust. |
| P0-2 | Remove production test event/content | NEEDS PRODUCTION VERIFICATION | Confirm known checkout-test slug/account/assets are absent; do not run the destructive seeder; later environment-guard it. | Production DB; `database/seeders/CheckoutTestSeeder.php` | High if present/run; limited live home showed no obvious test event. |
| P0-3 | Replace `devpromaster` footer | PARTIALLY DONE | Active public source is Wivor-branded; replace three stale references and resolve live `/login` deployment/cache drift one surface at a time. | `layouts/footer.blade.php:5`, `layouts/site-footer.php:94`, `auth/register.blade.php:134`, deployment caches | High — developer branding is visibly unprofessional if live. |
| P0-4 | Make Privacy / Terms / Photographer Terms clearly accessible | PARTIALLY DONE | Verify final DB policy content, add contextual customer links, and resolve the missing/mislabeled photographer agreement. | `PolicyController`, `WebsitePolicy`, `policies/show.blade.php`, footer, signup/application/cart/checkout views | High — placeholder/missing agreements create legal and trust exposure. |
| P0-5 | Confirm live Stripe purchase → webhook → email → download | NEEDS PRODUCTION VERIFICATION | Run one controlled end-to-end transaction and capture Stripe webhook, mail, order, and download evidence; include failure/retry observations. | Stripe config/dashboard, checkout/webhook controllers, mail, order routes, S3 | Critical — code completeness does not prove production integration. |
| P0-6 | Confirm watermarked preview cannot expose original S3 object | NEEDS PRODUCTION VERIFICATION | Test anonymous original/preview access, bucket listing, signed URL expiry, CDN caching, and object ACL/IAM in production. | S3/IAM/CDN config, `ProcessPhoto`, `PhotoDeliveryController`, `OrderController` | Critical — originals are the paid asset. |
| P1-7 | “Notify me when photos are available” | NOT DONE | Keep unadvertised; defer implementation until after launch priorities. | Future event subscription/notification design | Low for MVP; do not build in this audit. |
| P1-8 | Better order recovery using email/order link | PARTIALLY DONE | Existing protected link works; later add controlled lookup/resend and support tooling without weakening token access. | Order controller/routes, mail, future support UI | Medium — lost/failed email can strand a paid customer. |
| P1-9 | Refund/admin order management | PARTIALLY DONE | Keep Stripe as refund initiator for MVP; add documented support procedure. Later consider order detail/search, receipt resend, and app refund controls. | Admin payment views/controllers, Stripe webhook, order models | Medium — support work is manual and partial refunds are not modeled. |
| P1-10 | Bib recognition/search | DONE | Do not rebuild. Run a production AWS/queue smoke test, add monitoring/retry visibility, and avoid absolute accuracy claims. | bib jobs/service, event controller/view, queue/AWS config | Medium operational risk; feature code is present. |
| P1/P2-11 | Facial recognition | PARTIALLY DONE | Keep public flag off. Gate indexing, define cleanup/retention and legal basis, verify policy/consent, then production-test before any public claim. | face config, publish controller/job, cleanup jobs, event state lifecycle, policies | High privacy/compliance/cost risk if indexing continues while “disabled.” |
| Newly Discovered Launch Risk — P0 | Public customer signup/account claims are false and path is broken | NOT DONE | Hide/remove customer-account CTA and promises for the guest MVP, or repair only under a separately approved scope. Preserve photographer authentication. | `auth/login.blade.php`, `resources/lang/en/header.php`, `/signup`, `UserController`, `LoginController`, customer dashboard | High — public promise leads users into failing/nonexistent features. |
| Newly Discovered Launch Risk — P0 | Hard-coded admin passwords in committed/runtime paths | NOT DONE | Remove hard-coded password assignment, rotate any affected deployed accounts, and use a secure one-time invitation/reset flow. Do not print or reuse the credential. | `database/seeders/UserAndRoleSeeder.php:27-34`, `app/Http/Controllers/UserController.php:92` | Critical account-compromise risk. |
| Newly Discovered Launch Risk — P0 | Face flag does not stop biometric indexing | NOT DONE | Make the off state stop publication-time indexing, assess/delete unneeded collections, and document authorized retention before launch. | `PhotographerUploadController.php:190`, face jobs/service/config, event lifecycle | High privacy/compliance and unexpected AWS cost risk. |
| Newly Discovered Launch Risk — P1 | Dead public routes target missing controller methods | NOT DONE | Remove/disable `/testimonials` and the effective `/photobook` route after confirming no external dependency. | `routes/web.php`, related controllers/views | Medium — direct requests can produce 500 errors and stale product impressions. |
| Newly Discovered Launch Risk — P1 | Receipt email has no retry/resend path | PARTIALLY DONE | Verify provider delivery/alerts for launch; document support recovery. Build resend/recovery only as a later bounded task. | `StripeWebhookController`, `OrderReceiptMail`, admin/order support | Medium — one mail failure can hide the only later recovery link. |
| Newly Discovered Launch Risk — P1 | Automated test suite is not green | NOT DONE | Resolve the SEO keywords-tag expectation versus implementation; review seven PHPUnit notices before using green tests as a release gate. | `tests/Feature/PhotoSeoTest.php:39`, `resources/views/layouts/app.blade.php:14-16`, test config | Medium — obscures regressions even though current failure is not commerce-related. |
| Newly Discovered Launch Risk — P1 | Automated photographer payouts are implied but absent | PARTIALLY DONE | Define and document the manual payout operation/timing; reword public copy. Build transfers only in a separately authorized commerce task. | `CheckoutService`, `StripeConnectService`, photographer sales/dashboard copy | High financial/expectation risk. |

## M. Recommended Execution Order

Keep each item as a separate, reviewable change or verification task. Reuse the current Blade/Bootstrap structure and existing components; do not combine these into a redesign.

1. **Secure admin access first.** Remove hard-coded admin password behavior and rotate/verify any affected production accounts. This is the only newly discovered issue that should precede public copy work.
2. **Stop unintended face indexing while disabled.** Make the feature-off state real, inventory any existing collections, and decide cleanup/legal ownership. Do not enable or advertise face search.
3. **Correct public truth one surface at a time.** First the login/customer-account CTA and promises, then the small homepage photographer payout wording. Preserve the functioning guest purchase and photographer login paths.
4. **Confirm/remove production fixtures.** Read-only query the known checkout fixture slug/account/assets, establish ownership, and only then perform a separately approved cleanup if anything exists. Guard the destructive seeder later.
5. **Finish branding consistency.** Replace the exact stale `devpromaster` sources, deploy, clear application/view/CDN caches as applicable, and verify `/`, `/login`, photographer application, and authenticated/admin footers against the deployed commit.
6. **Finish legal/trust accessibility.** Verify that Privacy, Terms, and Refund content are final rather than fallbacks; resolve photographer agreement coverage; add small contextual links without redesigning checkout/onboarding.
7. **Run the commerce production verification.** Use one controlled transaction to trace browse → cart → Stripe → webhook → fulfillment → email → order link → original download. Record identifiers/timestamps without putting secrets in the report.
8. **Run the original-protection verification.** Confirm anonymous denial for originals/listing, watermarked derivative delivery, signed URL expiration, download revocation, and CDN behavior. Treat this as an infrastructure security test, not a code assumption.
9. **Restore the release signal.** Resolve the one SEO test mismatch and PHPUnit notices, then rerun the full suite. This should be its own non-feature task.
10. **Remove dead public routes/template exposure.** Disable `/testimonials`, `/photobook`, and authenticated test/legacy commerce surfaces in small route-focused changes.
11. **Launch with documented manual operations.** Define who handles payment/order support, mail failures, Stripe refunds/disputes, photo-removal requests, and manual photographer payouts.
12. **Defer P1/P2 expansion.** After launch stabilization, handle order recovery/resend, admin order/refund tooling, notifications, and only then a separately approved face-search launch. Bib search needs operational monitoring, not a rebuild. Video remains out of scope.

### Audit stop statement

This report maps the requested customer and photographer flows, verifies current bib and face-search states, identifies video/development/test/legal mismatches, traces commerce and photo security, updates the launch priorities, and gives a one-at-a-time execution order. No application code, data, feature, page design, or copy was changed as part of this audit.
