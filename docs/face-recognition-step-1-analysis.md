# WivorPhotos AI Face Search — Step 1 Analysis

## Scope

This document records reconnaissance only. No face-recognition feature, route, controller, service, job, model, migration, view, dependency, environment variable, IAM policy, S3 setting, or queue setting was changed.

The analysis is based on the current application code, tracked migrations, and tests. The local PostgreSQL service was unavailable during review, so the live migration state could not be independently confirmed.

## A. Current Customer Search Flow

The customer experience is public and does not require a customer account.

1. The home page is served by `HomeController::welcome()` and displays three recently published events (`app/Http/Controllers/HomeController.php:47`, `resources/views/site/welcome.blade.php:131`).
2. Customers can search for events by text, city, state, sport, and date. The home-page and header forms submit a GET request to `/list-events` (`routes/web.php:56`, `resources/views/site/welcome.blade.php:131`, `resources/views/partials/header.blade.php`).
3. `EventController::listEvents()` applies the requested filters to published events, orders them by event date, and paginates twelve events per page (`app/Http/Controllers/EventController.php:68`).
4. Each event card links to the public event page at `/events/{event:slug}` (`routes/web.php:68`, `resources/views/partials/event.blade.php`).
5. `EventController::show()` rejects unpublished events, checks the event sales window, and loads only published photos belonging to that event (`app/Http/Controllers/EventController.php:106`). Results are ordered newest first and paginated at 48 per page.
6. On the event page, a customer can browse all available photos or submit a bib number through the “Find your photos” GET form (`resources/views/events/post-show.blade.php:57`). The same photo grid and pagination are used for both unfiltered and bib-filtered results.
7. Selecting a photo opens `/events/{event:slug}/photos/{photo}`. `PhotoDeliveryController::gallery()` verifies that the event and photo are public and renders the detail page (`routes/web.php:69`, `app/Http/Controllers/PhotoDeliveryController.php:28`, `resources/views/photos/show.blade.php`).
8. Preview images are streamed through `/events/{event:slug}/photos/{photo}/image.jpg`; the application verifies publication, ownership, preview availability, and the sales window before reading the private object (`routes/web.php:70`, `app/Http/Controllers/PhotoDeliveryController.php:125`).
9. Customers add and remove photos through the public cart routes. `CartService` stores photo UUIDs in the session, accepts only published and sellable photos, and restricts a cart to one event (`routes/web.php:75`, `app/Services/CartService.php`).
10. Checkout creates a pending order and redirects the customer to Stripe Checkout (`routes/web.php:81`, `app/Http/Controllers/CheckoutController.php`, `app/Services/CheckoutService.php`). After Stripe confirms payment by webhook, the order is fulfilled, downloads are enabled, photo sales counts are updated, and a receipt is sent (`app/Http/Controllers/StripeWebhookController.php`).
11. The customer returns through an access-token URL and downloads purchased originals through an authorized, temporary delivery URL (`routes/web.php:86`, `app/Http/Controllers/OrderController.php`).

There is no JavaScript search application in the current event flow. Event and bib searches are normal GET submissions; the event page is rendered by Laravel. The cart page uses only small progressive-enhancement JavaScript around checkout submission.

## B. Current Bib Recognition Flow

### Recognition during photo processing

1. A photographer uploads an original JPEG directly to the configured private media disk using a presigned upload URL.
2. After the browser reports upload completion, `PhotographerUploadController::complete()` confirms that the object exists and has an acceptable size, marks the photo as processing, and dispatches `ProcessPhoto` (`app/Http/Controllers/PhotographerUploadController.php:118`).
3. `ProcessPhoto` downloads and validates the original, checks image properties and duplicates, creates private watermarked preview and thumbnail derivatives, and marks the photo ready. It then dispatches `DetectPhotoBibNumbers` (`app/Jobs/ProcessPhoto.php:135`).
4. `DetectPhotoBibNumbers` skips an already-scanned photo and calls `BibNumberDetector`. A final Rekognition failure is logged but does not make the processed photo unavailable (`app/Jobs/DetectPhotoBibNumbers.php`).
5. `BibNumberDetector` calls Amazon Rekognition `DetectText` with the original image’s S3 bucket and object key. It keeps exact numeric strings of one to five digits, rejects all-zero values, applies `BIB_RECOGNITION_MIN_CONFIDENCE`, and deduplicates a repeated bib by retaining its highest confidence (`app/Services/BibNumberDetector.php`, `config/bib_recognition.php`).
6. Valid detections are upserted into `photo_bib_numbers` on `(photo_id, bib_number)`, and `photos.bib_scanned_at` is set. A scan with no valid bib is still considered completed.

The schema supports multiple bib numbers on one photo and the same bib number on multiple photos.

### Matching during customer search

1. The customer submits `bib` on the public event-page GET route.
2. `EventController::show()` validates an exact one-to-five-digit value and rejects an all-zero value.
3. The event’s existing published-photo query adds `whereHas('bibNumbers', ...)` with an exact `bib_number` comparison (`app/Http/Controllers/EventController.php:128`).
4. Because the query begins from `$event->photos()`, matching is automatically limited to the selected event. The normal event photo grid, detail page, cart, checkout, and download flow remain unchanged.

Relevant tests confirm event scoping, exact matches, many-to-many matching behavior, deduplication, idempotent scan jobs, nonfatal Rekognition failures, and cart use from filtered results.

## C. Relevant Files

| File | Current responsibility |
| --- | --- |
| `routes/web.php` | Public event, photo, cart, checkout, order, download, and photographer upload routes. |
| `routes/api.php` | Stripe webhook routes only; there is no customer search API. |
| `app/Http/Controllers/HomeController.php` | Home page and recent published events. |
| `app/Http/Controllers/EventController.php` | Event discovery, public event display, published-photo query, and bib filtering. |
| `app/Http/Controllers/PhotoDeliveryController.php` | Public photo detail and authorized preview streaming. |
| `app/Http/Controllers/CartController.php` | Public cart requests. |
| `app/Services/CartService.php` | Session cart state and sellability checks. |
| `app/Http/Controllers/CheckoutController.php` | Checkout request handling and Stripe redirect. |
| `app/Services/CheckoutService.php` | Order creation, price snapshots, order items, and Stripe Checkout session creation. |
| `app/Http/Controllers/StripeWebhookController.php` | Paid-order fulfillment and receipt dispatch. |
| `app/Http/Controllers/OrderController.php` | Access-token order display and purchased-original download. |
| `app/Http/Controllers/PhotographerUploadController.php` | Upload batches, presigned upload details, completion, publishing, removal, and upload retry entry points. |
| `app/Jobs/ProcessPhoto.php` | Original validation, derivative creation, ready/rejected status, and bib-job dispatch. |
| `app/Jobs/DetectPhotoBibNumbers.php` | Asynchronous and idempotent bib recognition. |
| `app/Services/BibNumberDetector.php` | Rekognition client setup, `DetectText`, candidate filtering, and database persistence. |
| `app/Services/PhotoStorage.php` | Presigned upload and temporary delivery URLs. |
| `app/Models/Event.php` | Event/photo/bib/photographer relationships and published-event scopes. |
| `app/Models/Photo.php` | Photo lifecycle, storage keys, UUID route binding, and bib relationship. |
| `app/Models/PhotoBibNumber.php` | A detected bib number and confidence for one photo. |
| `resources/views/site/welcome.blade.php` | Event-search entry point. |
| `resources/views/events/post-show.blade.php` | Bib form, event results, photo grid, cart actions, and pagination. |
| `resources/views/photos/show.blade.php` | Public photo detail and purchase action. |
| `resources/views/photographers/upload.blade.php` | Browser-to-S3 upload, completion call, and processing-status polling. |
| `config/filesystems.php` | Filesystem and S3 disk configuration. |
| `config/photo_uploads.php` | Media disk, image limits, URL lifetimes, and derivative settings. |
| `config/bib_recognition.php` | Bib confidence threshold. |
| `config/queue.php` | Queue connection and database-queue configuration. |
| `app/Console/Commands/EnforceMediaRetention.php` | Removal of abandoned, rejected, expired derivative, and expired original objects. |
| `app/Console/Kernel.php` | Daily media-retention schedule. |
| `database/migrations/*events*`, `*photos*`, `*photo_bib_numbers*`, `*orders*`, `*order_items*` | Relevant persisted structure and constraints. |

## D. Relevant Database Structure

| Table/model | Important fields and relationships |
| --- | --- |
| `events` / `Event` | Integer `id`, public `uuid`, unique `slug`, publication and sales-window state. Has many photos; has many bib detections through photos; belongs to many photographers. |
| `photographers` / `Photographer` | Integer `id`, public/stable `uuid`. Has many photos. |
| `photos` / `Photo` | Integer `id` primary key; unique `uuid`; foreign keys to event, photographer, assignment, and upload batch; original, preview, and thumbnail keys; image metadata; lifecycle status; `bib_scanned_at`; retention timestamps. Belongs to one event and photographer; has many bib detections. |
| `photo_bib_numbers` / `PhotoBibNumber` | Foreign key `photo_id` with cascade delete, indexed `bib_number`, confidence, and a unique `(photo_id, bib_number)` constraint. |
| `orders` / `Order` | Belongs to an event and records payment/fulfillment state plus customer delivery details. |
| `order_items` / `OrderItem` | Optionally references `photo_id` and freezes `photo_uuid`, original object key, and purchase values so a paid order remains traceable. |

The canonical internal photo identifier is `photos.id`; current foreign keys and queued jobs use it. The stable public identifier is `photos.uuid`; route binding, cart state, and order snapshots use the UUID. For Rekognition, a photo UUID is the better `ExternalImageId` because it avoids exposing a sequential database key and remains directly resolvable after a search response. The application must still scope every resolved UUID to the event being viewed.

There is no separate media-object model, face model, face-index table, or event-to-Rekognition-collection mapping today.

## E. Existing AWS/S3 Architecture

The configured WivorPhotos media disk is S3. The reviewed local configuration selects `s3` and the `us-east-2` region. Configuration is supplied through these existing variables:

- `WIVOR_MEDIA_DISK`
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_SESSION_TOKEN`
- `AWS_DEFAULT_REGION`
- `AWS_BUCKET`
- `AWS_URL`

No secret values are recorded in this report.

The project already directly requires the AWS SDK for PHP and the Flysystem S3 adapter (`composer.json`). `BibNumberDetector` already demonstrates a reusable Rekognition-client pattern: region and bucket come from the configured media disk; explicit credentials are supplied only when configured, otherwise the AWS SDK provider chain is used. A face-search MVP does not need another AWS package.

Original photo keys follow this structure:

```text
events/{event.uuid}/photographers/{photographer.uuid}/photos/{photo.uuid}/original.jpg
```

Processed media uses the same base path with `preview.jpg` and `thumbnail.jpg`.

Originals are uploaded directly through time-limited presigned URLs and are not exposed as public objects. `ProcessPhoto` explicitly saves derivatives with private visibility. Public previews pass through application authorization, while thumbnail and purchased-original access use temporary URLs. This architecture can supply an existing S3 original directly to both `IndexFaces` and `DetectText` without making it public.

The current queue connection is configured as the database queue. Bib recognition is already isolated in a queued job, providing the closest established pattern for face indexing.

## F. Photo Processing Flow

```text
Photographer upload page
  -> POST creates UploadBatch and Photo placeholders
  -> application returns a presigned S3 PUT URL
  -> browser uploads original.jpg directly to private S3
  -> browser calls the photo completion endpoint
  -> completion endpoint verifies the object and dispatches ProcessPhoto
  -> ProcessPhoto validates JPEG, dimensions, color, size, and duplicate checksum
       -> invalid: reject photo and remove source/derivatives
       -> valid: normalize orientation, create private preview + thumbnail,
                 mark Photo ready, dispatch DetectPhotoBibNumbers
  -> DetectPhotoBibNumbers calls Rekognition DetectText and saves bib matches
  -> photographer explicitly publishes ready photos
  -> published photos become eligible for the public event/gallery query
```

The upload page performs direct S3 uploads with browser JavaScript, then polls application status. Image processing and recognition happen on the server-side queue. Publication is a distinct later action, so `ready` does not mean customer-visible.

Media retention runs daily. It removes abandoned/rejected source objects and, after configured gallery/protection periods, derivatives and originals. Photographer/admin removal paths can also delete objects or make a photo unavailable. There is presently no corresponding biometric-index cleanup because no face index exists.

## G. Proposed Face Recognition Integration Points

### Indexing event photos with `IndexFaces`

The smallest technical insertion point is immediately after `ProcessPhoto` marks a valid photo ready, beside the existing `DetectPhotoBibNumbers` dispatch. A separate queued `IndexPhotoFaces` job should call a focused service rather than adding another remote AWS operation inside `ProcessPhoto`.

However, indexing at `ready` would include photos that a photographer never publishes. The data-minimizing insertion point is after a successful publish transaction in `PhotographerUploadController`, when a validated photo becomes available to customers. Step 2 should select this safer publish-time hook unless the product explicitly requires face indexing before publication.

Recommended mapping:

- One Rekognition collection per event, with a deterministic collection identifier derived from the event UUID.
- The original private S3 object as the `IndexFaces` image source.
- `photo.uuid` as `ExternalImageId`.
- A queued, retryable, idempotent indexing job.
- A cleanup path for all existing unpublish/remove/retention behavior so biometric records cannot outlive the intended photo availability or retention period.

The job must account for a photo containing multiple faces: one photo UUID can correspond to multiple returned Rekognition FaceIds. Reprocessing must not silently accumulate duplicate face records.

### Customer selfie search with `SearchFacesByImage`

The natural UI location is the existing “Find your photos” area in `resources/views/events/post-show.blade.php`, presented as an additional search method alongside bib search. It should preserve the current event results grid, photo detail page, cart, checkout, and download behavior.

A public POST route scoped to `{event:slug}` is consistent with the current guest customer journey and provides normal Laravel CSRF protection. The controller should:

1. Require a published event whose gallery/sales window permits browsing.
2. Validate that the submitted file is an allowed, bounded image.
3. Read the framework-managed temporary upload directly into the Rekognition `SearchFacesByImage` request.
4. Never copy the selfie to S3, the public disk, a database field, a job payload, or application logs.
5. Discard the temporary upload at the end of the request.
6. Deduplicate returned `ExternalImageId` values because one event photo can contain multiple indexed faces.
7. Resolve UUIDs only through the selected event’s currently published and sellable photos.
8. Render the matches through the existing event photo-result partial/grid.

The current synchronous request lifecycle makes temporary selfie handling straightforward. No global middleware that intentionally logs request bodies or uploaded image contents was found. Operational web-server and platform logging should still be checked before launch.

The existing bib search persists its filter in a GET query and supports pagination. A selfie cannot safely be placed in a URL or replayed for every page. Step 2 must choose either a single bounded result page or short-lived server-side storage of matched photo UUIDs/search state only—never the selfie itself.

The `photos.people` SEO field is manually supplied publication metadata and is not a biometric identity mapping; it should not be used for face-search resolution.

## H. Files That Will Probably Need Changes in Step 2

The smallest likely implementation set is:

- `routes/web.php` — add one event-scoped selfie-search POST route.
- `resources/views/events/post-show.blade.php` — add the selfie option and validation/result states while retaining the existing gallery.
- `app/Http/Controllers/EventFaceSearchController.php` — new, focused upload validation, event authorization, search orchestration, and result rendering, following the current controller layout.
- `app/Services/FaceRecognitionService.php` — new Rekognition collection, indexing, search, and deletion operations using the existing AWS configuration pattern.
- `app/Jobs/IndexPhotoFaces.php` — new queued and retryable event-photo indexing operation.
- Either `app/Http/Controllers/PhotographerUploadController.php` (recommended publish-time dispatch) or `app/Jobs/ProcessPhoto.php` (ready-time dispatch), but not both.
- Focused feature/unit tests for indexing, event isolation, selfie validation and disposal, result deduplication, unpublished-photo exclusion, failure behavior, and cart continuity.
- A small face-recognition configuration file only if the match threshold and collection prefix require configurable values.

Privacy-complete cleanup may additionally require narrowly scoped changes to `app/Console/Commands/EnforceMediaRetention.php` and existing photographer/admin removal paths. Whether a migration is necessary depends on the Step 2 decision for retry/idempotency and recording returned FaceIds. No migration should be added merely to duplicate a deterministic event-UUID/photo-UUID mapping.

No dependency update is expected: the required AWS SDK is already installed.

## I. Questions and Risks Before Step 2

1. **Index timing:** Should only published photos be indexed? Publish-time indexing minimizes biometric data and is recommended; ready-time indexing is earlier and operationally simpler but includes photos customers may never see.
2. **Deletion contract:** What exact action must delete a photo’s faces—unpublish, explicit removal, event archival, gallery close, original-object retention expiry, or all of them? Current media cleanup has no Rekognition equivalent.
3. **Retry/idempotency:** `IndexFaces` can create multiple FaceIds for one photo and repeated runs can create duplicates. Step 2 needs a definite deletion/reconciliation strategy and may need persisted FaceIds or indexing state.
4. **Collection lifecycle:** When should an event collection be created and deleted? Deterministic event-based collection names avoid a basic mapping table, but collection existence, retry behavior, and teardown still need ownership rules.
5. **IAM:** The runtime identity currently used for S3 and `DetectText` must be verified for only the required Rekognition collection, indexing, search, and deletion actions. IAM is external configuration and was not changed.
6. **Search threshold and result cap:** Product owners need to approve an MVP similarity threshold and a maximum number of matches. False positives should be treated as a customer/privacy risk, not only a relevance issue.
7. **Multiple faces in the selfie:** `SearchFacesByImage` searches the largest detected face. The UI and validation message must set that expectation and ask for a clear single-person selfie.
8. **Pagination:** A POSTed selfie must not be persisted or repeatedly submitted. Decide whether MVP results are bounded to one page or whether only matched UUIDs are retained briefly on the server.
9. **Failure experience:** AWS unavailability must leave normal event browsing and bib search operational and show a friendly, nontechnical selfie-search error.
10. **Logging and observability:** Do not log request payloads, selfie bytes, biometric details, or presigned URLs. Log only non-sensitive identifiers and operational status needed for retries.
11. **Live database verification:** The tracked schema and tests were reviewed, but the local PostgreSQL service was unavailable. Confirm the deployed migration state before Step 2.

Step 1 ends here. No implementation has been performed.
