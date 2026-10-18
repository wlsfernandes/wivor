# WiVor Marketing Site — Plan 1: Brand Shell and Static Pages

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Apply the WiVor brand system (Alata, brand colors, new header and footer) to the public site and ship the static pages Home, How It Works, FAQ, About, Organizers and Biometric Notice with the exact spec copy.

**Architecture:** A new static stylesheet `public/assets/css/wivor-brand.css` adds `wv-`-prefixed tokens and components on top of the existing template CSS, so old pages keep working. The header and footer partials are rewritten in place. New pages are Blade views under `resources/views/frontend/v1/pages/`, registered with `Route::view` where no data is needed. The Biometric Notice joins the existing DB-managed policy system (`WebsitePolicy` + `PolicyController` with a fallback view). Face-recognition copy is gated on `config('face_recognition.enabled')`.

**Tech Stack:** Laravel 13, PHP 8.3, Blade, Bootstrap 5.3 grid (already loaded), Font Awesome 6 (already loaded), Google Fonts (Alata), PHPUnit 12 (`php artisan test`).

**Spec:** `docs/WiVor — Especificação do Site.pdf` (author: Victor Andrade). Sections 1–5, 8 (without the form), 9, 10, 12 (Biometric Notice page only) are in this plan. The filename uses decomposed Unicode; open it with `ls docs/WiVor*.pdf`.

**Plan series (agreed with Junior on 2026-10-18):**
1. This plan: brand shell + static pages.
2. `/athletes` page with functional event + bib + selfie search and biometric consent checkbox.
3. Forms: `leads` table, Organizers partnership form, Contact form restyle, `/photographers` restyle keeping the existing registration flow (plus "Types of events you shoot").
4. Cookie banner, GA4 + Meta Pixel with CTA click events, Open Graph, WebP images, hero/card photos, favicon.

## Global Constraints

- Brand colors: orange `#ff5722`, orange hover `#ff7e55`, slate `#303841`, light background `#eeeeee`, white `#ffffff`, borders `#d4d4d4`.
- Font: Alata (Google Fonts) on the whole site.
- Text sizes desktop/mobile: H1 52/34px, H2 36/26px, H3 22/20px, body 17/16px.
- Max content width: 1180px. Rounded corners 8–12px (use 10px).
- Mobile first. Buttons at least 48px tall. One column on mobile.
- Buttons: solid orange with white text (primary). Sections alternate white / `#eeeeee` / `#303841`.
- Logo: colored logo only on white background. On colored/dark backgrounds use the white (or black) logo. Never rotate, distort, cover, or place the logo directly on a photo.
- Site copy (marked COPY in the spec) is US English and must be copied **exactly**. Do not translate it and do not route it through `@lang`.
- Items in `[brackets]` in the spec are data Victor must still supply. **Never invent them.** Omit the bracketed part (and any section that depends only on it) until supplied. Exception: `[support email]` uses the existing `config('contact.email')`.
- Face-recognition copy is shown only when `config('face_recognition.enabled')` is true. When false, use the bib-only variants written in each task. (Decision by Junior, 2026-10-18; the launch audit says face search stays unadvertised until legal review.)
- Do not change Stripe, checkout, payout, or photographer registration logic (AGENTS.md).
- No new Composer or npm dependencies.
- Hardcoded English Blade is the existing pattern for FAQ/policy pages. Follow it.
- In tests, any expected string containing `'` must use `assertSee($text, false)` because `assertSee` escapes `'` to `&#039;`.

## Review Focus

1. **Face flag off (production default):** every new page must show no selfie/face claim. Each page task has a `flag off` test that asserts `assertDontSee('selfie')` / `assertDontSee('face recognition')`.
2. **Phone width (375px):** no horizontal scroll, hamburger opens and closes the menu, orange "Find My Photos" stays visible. Task 8 tests the toggle markup; Task 9 checks it in a real browser.
3. **Old links:** `/about-us` must 301 to `/about`; every existing `route('about_us')` call must keep working. Task 5 tests the redirect.
4. **Biometric Notice with and without admin content:** empty DB shows the fallback; saved admin content replaces it; admin editor opens. Task 2 tests all three.
5. **Flash and validation messages:** the old header printed `session('success')`, `session('error')` and `$errors`. Pages like the photographer registration and contact form rely on that. Task 8 keeps the alerts and tests them.

---

## Decisions in this plan (please confirm when reviewing)

- **Home drops the "Recently Published Events" strip and the event search form.** The spec's Home has neither. `HomeController::welcome` stops querying events. `EventMvpTest`'s two homepage tests and `FrontendTruthAlignmentTest::test_homepage_preserves_supported_customer_claims_without_global_face_or_video_marketing` pin the old Home copy; they are rewritten for the spec Home (Task 7). The event directory `/list-events` is unchanged.
- **Header drops the language flags and the search popup.** The spec header has neither. Cart icon and "Log in" stay as small utility links, because guest checkout and photographer login depend on them.
- **Footer Legal column keeps Refund Policy, Photographer Terms and Photo Removal Request** in addition to the spec's three links. These pages exist and `PublicPolicyPagesTest` requires them.
- **FAQ is replaced by the spec's 8 questions.** The old FAQ topics (checkout, cancellations, download periods, photographer topics) are removed.
- **`/athletes` is a temporary 302 redirect to `/list-events`** until Plan 2 builds the page, so every "Find My Photos" link uses `route('athletes')` from day one.
- **Organizers "Become a Partner" links to the Contact page** until Plan 3 adds the partnership form.
- **No hero or card photos yet.** The only banner (`resource/banner_wivor.jpg`) shows a camera, not an athlete, and has a logo mark on the photo, which the logo rules forbid. Sections use icons; photos arrive in Plan 4 with Victor's images.

## Open items for Victor (not blocking this plan)

- How It Works step 4 "Get paid": spec says confirm revenue split before publishing.
- FAQ 4 `[Confirm delivery details]`, FAQ 6 `[retention and deletion policy]`, FAQ 7 `[Confirm payout schedule]`: omitted until supplied.
- Home CTA copy says "Search by your name, bib number or a selfie." The product searches by event name, not athlete name.
- Testimonials (Home 4.6) and "Trusted by Georgia Multiports" (Home, Organizers): not built until real quotes and logo permission exist.
- Biometric Notice text must be validated by a lawyer; this plan ships a factual fallback built from the existing Privacy Policy wording.

## File Map

| File | Action | Responsibility |
|---|---|---|
| `public/assets/css/wivor-brand.css` | Create | Brand tokens, typography, buttons, sections, cards, header, footer |
| `public/assets/js/wivor-nav.js` | Create | Mobile menu toggle |
| `resources/views/frontend/v1/components/head-assets.blade.php` | Modify | Load Alata + brand CSS |
| `resources/views/frontend/v1/components/header.blade.php` | Rewrite | Spec header + flash alerts |
| `resources/views/frontend/v1/components/footer.blade.php` | Rewrite | Spec 4-column footer, keep template scripts |
| `routes/frontend.php` | Modify | New routes, `/about-us` redirect, `/athletes` temporary redirect |
| `app/Models/WebsitePolicy.php` | Modify | `BIOMETRIC_NOTICE` constant + title |
| `app/Http/Controllers/Frontend/PolicyController.php` | Modify | Biometric Notice presentation |
| `resources/views/frontend/v1/policies/biometric-notice.blade.php` | Create | Fallback notice |
| `resources/views/layouts/sidebar.blade.php` | Modify | Admin link to edit the notice |
| `resources/views/frontend/v1/pages/how-it-works.blade.php` | Create | How It Works page |
| `resources/views/frontend/v1/pages/faq.blade.php` | Rewrite | Spec FAQ |
| `resources/views/frontend/v1/pages/about.blade.php` | Rewrite | Spec About |
| `resources/views/frontend/v1/pages/organizers.blade.php` | Create | Organizers page (no form yet) |
| `resources/views/frontend/v1/home/index.blade.php` | Rewrite | Spec Home |
| `app/Http/Controllers/HomeController.php` | Modify | `welcome()` no longer loads events |
| `tests/Feature/BrandAssetsTest.php` | Create | Font + stylesheet loaded |
| `tests/Feature/BiometricNoticePageTest.php` | Create | Notice page + admin editor |
| `tests/Feature/HowItWorksPageTest.php` | Create | |
| `tests/Feature/FaqPageTest.php` | Create | |
| `tests/Feature/AboutPageTest.php` | Create | |
| `tests/Feature/OrganizersPageTest.php` | Create | |
| `tests/Feature/HomePageTest.php` | Create | |
| `tests/Feature/PublicHeaderFooterTest.php` | Create | |
| `tests/Feature/EventMvpTest.php` | Modify | Remove two tests that pin the old Home |
| `tests/Feature/FrontendTruthAlignmentTest.php` | Modify | Home test rewritten for spec Home |
| `tests/Feature/PublicPolicyPagesTest.php` | Modify | Header test rewritten for spec nav |

---

### Task 0: Baseline

**Files:** none changed in git.

Context: `bootstrap/cache/config.php` was built in a different folder (`/home/junior/Projects/wivor`, capital P). With it present, PHPUnit ignores `phpunit.xml` env values and 132 of 191 tests fail. Without it, 188 pass and 3 fail. The 3 failures exist before this plan and are **not** to be fixed here:
- `FrontendTruthAlignmentTest > authenticated legacy view does not advertise video services`
- `PhotoSeoTest > published photo has an indexable page with credit people and social…` (keywords meta)
- `WebsitePolicyTest > admin can open all website policy editors from the admin menu`

- [ ] **Step 1: Ask Junior before clearing the config cache.** It deletes only `bootstrap/cache/config.php` (a generated file, not in git). The local app then reads `.env` on each request. Rollback: `php artisan config:cache`.

Run (after OK): `php artisan config:clear`

- [ ] **Step 2: Record baseline**

Run: `php artisan test 2>&1 | tail -5`
Expected: `3 failed, 188 passed`. If different, stop and report.

- [ ] **Step 3: Create a branch**

```bash
git switch -c feature/marketing-site-shell
```

---

### Task 1: Brand stylesheet and Alata font

**Files:**
- Create: `public/assets/css/wivor-brand.css`
- Modify: `resources/views/frontend/v1/components/head-assets.blade.php`
- Test: `tests/Feature/BrandAssetsTest.php`

**Interfaces:**
- Produces CSS classes used by every later task: `wv-container`, `wv-section` + modifiers `wv-section--white|--light|--dark|--orange`, `wv-h1`, `wv-h2`, `wv-h3`, `wv-body`, `wv-small`, `wv-lead`, `wv-btn` + `wv-btn--primary|--outline|--inverse`, `wv-button-row`, `wv-card`, `wv-icon`, `wv-step-number`, `wv-link`, `wv-faq`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class BrandAssetsTest extends TestCase
{
    public function test_public_pages_load_the_alata_font_and_brand_stylesheet(): void
    {
        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('family=Alata', false)
            ->assertSee('href="'.asset('assets/css/wivor-brand.css').'"', false);
    }

    public function test_brand_stylesheet_defines_every_specified_color(): void
    {
        $stylesheet = file_get_contents(public_path('assets/css/wivor-brand.css'));

        foreach (['#ff5722', '#ff7e55', '#303841', '#eeeeee', '#ffffff', '#d4d4d4'] as $color) {
            $this->assertStringContainsString($color, $stylesheet);
        }
        $this->assertStringContainsString("'Alata'", $stylesheet);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=BrandAssetsTest`
Expected: FAIL (`family=Alata` not found; `file_get_contents(...): Failed to open stream`).

- [ ] **Step 3: Create the stylesheet**

`public/assets/css/wivor-brand.css`:

```css
/*
 * WiVor brand layer (site specification, section 2).
 * Loaded after the template CSS. All classes use the "wv-" prefix so they never collide with template classes.
 */
:root {
    --wv-orange: #ff5722;
    --wv-orange-hover: #ff7e55;
    --wv-slate: #303841;
    --wv-light: #eeeeee;
    --wv-white: #ffffff;
    --wv-border: #d4d4d4;
    --wv-font-family: 'Alata', sans-serif;
    --wv-radius: 10px;
    --wv-content-width: 1180px;
    --wv-button-height: 48px;
}

/* Alata on the whole site. Icon fonts use <i>/<span> with their own classes, so they are not listed here. */
body,
body :is(h1, h2, h3, h4, h5, h6, p, a, li, label, input, select, textarea, button, summary, td, th) {
    font-family: var(--wv-font-family);
}

/* Layout */
.wv-container {
    width: 100%;
    max-width: var(--wv-content-width);
    margin: 0 auto;
    padding: 0 16px;
}

.wv-section {
    padding: 56px 0;
}

.wv-section--white { background: var(--wv-white); color: var(--wv-slate); }
.wv-section--light { background: var(--wv-light); color: var(--wv-slate); }
.wv-section--dark { background: var(--wv-slate); color: var(--wv-white); }
.wv-section--orange { background: var(--wv-orange); color: var(--wv-white); }

.wv-section--dark :is(h1, h2, h3, p, li),
.wv-section--orange :is(h1, h2, h3, p, li) {
    color: var(--wv-white);
}

/* Typography (mobile first) */
.wv-h1 { font-size: 34px; line-height: 1.15; color: var(--wv-slate); margin: 0 0 16px; }
.wv-h2 { font-size: 26px; line-height: 1.2; color: var(--wv-slate); margin: 0 0 24px; }
.wv-h3 { font-size: 20px; line-height: 1.3; color: var(--wv-slate); margin: 0 0 8px; }
.wv-body { font-size: 16px; line-height: 1.6; }
.wv-lead { font-size: 16px; line-height: 1.6; max-width: 720px; }
.wv-small { font-size: 14px; line-height: 1.5; margin-top: 16px; }

@media (min-width: 992px) {
    .wv-section { padding: 88px 0; }
    .wv-h1 { font-size: 52px; }
    .wv-h2 { font-size: 36px; }
    .wv-h3 { font-size: 22px; }
    .wv-body,
    .wv-lead { font-size: 17px; }
}

/* Buttons */
.wv-button-row {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 24px;
}

@media (min-width: 576px) {
    .wv-button-row { flex-direction: row; flex-wrap: wrap; }
}

.wv-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: var(--wv-button-height);
    padding: 12px 24px;
    border: 2px solid transparent;
    border-radius: var(--wv-radius);
    font-size: 16px;
    line-height: 1.2;
    text-decoration: none;
    transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}

.wv-btn--primary { background: var(--wv-orange); border-color: var(--wv-orange); color: var(--wv-white); }
.wv-btn--primary:hover,
.wv-btn--primary:focus-visible { background: var(--wv-orange-hover); border-color: var(--wv-orange-hover); color: var(--wv-white); }

.wv-btn--outline { background: transparent; border-color: var(--wv-slate); color: var(--wv-slate); }
.wv-btn--outline:hover,
.wv-btn--outline:focus-visible { background: var(--wv-slate); color: var(--wv-white); }

.wv-section--dark .wv-btn--outline { border-color: var(--wv-white); color: var(--wv-white); }
.wv-section--dark .wv-btn--outline:hover,
.wv-section--dark .wv-btn--outline:focus-visible { background: var(--wv-white); color: var(--wv-slate); }

.wv-btn--inverse { background: var(--wv-white); border-color: var(--wv-white); color: var(--wv-orange); }
.wv-btn--inverse:hover,
.wv-btn--inverse:focus-visible { background: var(--wv-light); border-color: var(--wv-light); color: var(--wv-orange); }

/* Cards, icons, steps, links */
.wv-card {
    height: 100%;
    padding: 24px;
    background: var(--wv-white);
    border: 1px solid var(--wv-border);
    border-radius: var(--wv-radius);
    color: var(--wv-slate);
}

.wv-icon { font-size: 32px; color: var(--wv-orange); margin-bottom: 12px; }
.wv-step-number { font-size: 48px; line-height: 1; color: var(--wv-orange); margin-bottom: 8px; }

.wv-link { color: var(--wv-orange); text-decoration: none; }
.wv-link:hover,
.wv-link:focus-visible { color: var(--wv-orange); text-decoration: underline; }

/* FAQ accordion (native <details>) */
.wv-faq details {
    margin-bottom: 12px;
    background: var(--wv-white);
    border: 1px solid var(--wv-border);
    border-radius: var(--wv-radius);
}

.wv-faq summary {
    min-height: var(--wv-button-height);
    padding: 14px 20px;
    font-size: 18px;
    color: var(--wv-slate);
    cursor: pointer;
}

.wv-faq details > div { padding: 0 20px 16px; }
```

- [ ] **Step 4: Load the font and stylesheet**

In `resources/views/frontend/v1/components/head-assets.blade.php`, after line 6 (the Poppins link) add:

```blade
<link href="https://fonts.googleapis.com/css2?family=Alata&display=swap" rel="stylesheet">
```

After the last line (`responsive.css`) add:

```blade
<link href="{{ asset('assets/css/wivor-brand.css') }}" rel="stylesheet">
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=BrandAssetsTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Commit**

```bash
git add public/assets/css/wivor-brand.css resources/views/frontend/v1/components/head-assets.blade.php tests/Feature/BrandAssetsTest.php
git commit -m "Add WiVor brand stylesheet and Alata font to public pages"
```

---

### Task 2: Biometric Notice page

**Files:**
- Modify: `app/Models/WebsitePolicy.php:18-29`
- Modify: `app/Http/Controllers/Frontend/PolicyController.php:15-32`
- Modify: `routes/frontend.php:94-96` (add after photographer-terms)
- Create: `resources/views/frontend/v1/policies/biometric-notice.blade.php`
- Modify: `resources/views/layouts/sidebar.blade.php:93-94`
- Test: `tests/Feature/BiometricNoticePageTest.php`

**Interfaces:**
- Produces: route name `biometric-notice` (GET `/biometric-notice`); constant `WebsitePolicy::BIOMETRIC_NOTICE = 'biometric-notice'`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\WebsitePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BiometricNoticePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_biometric_notice_shows_fallback_content_when_no_admin_text_exists(): void
    {
        $this->get('/biometric-notice')
            ->assertOk()
            ->assertSee('Biometric Notice')
            ->assertSee('WivorPhotos does not intentionally save the search selfie')
            ->assertSee('href="'.route('privacy').'"', false)
            ->assertDontSee('Policy content pending');
    }

    public function test_biometric_notice_shows_admin_saved_content_instead_of_fallback(): void
    {
        WebsitePolicy::create([
            'slug' => WebsitePolicy::BIOMETRIC_NOTICE,
            'content' => 'Lawyer approved biometric text.',
        ]);

        $this->get(route('biometric-notice'))
            ->assertOk()
            ->assertSee('Lawyer approved biometric text.')
            ->assertDontSee('WivorPhotos does not intentionally save the search selfie');
    }

    public function test_admin_can_open_the_biometric_notice_editor(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

        $this->actingAs($admin)
            ->get(route('admin.website.policies.edit', ['policy' => WebsitePolicy::BIOMETRIC_NOTICE]))
            ->assertOk()
            ->assertSee('Biometric Notice')
            ->assertSee('name="content"', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=BiometricNoticePageTest`
Expected: FAIL (404 on `/biometric-notice`; `Route [biometric-notice] not defined`; undefined constant `BIOMETRIC_NOTICE`).

- [ ] **Step 3: Add the policy constant and title**

In `app/Models/WebsitePolicy.php`, after `public const PHOTOGRAPHER_TERMS = 'photographer-terms';` add:

```php
    public const BIOMETRIC_NOTICE = 'biometric-notice';
```

and in `TITLES`, after the `PHOTOGRAPHER_TERMS` line add:

```php
        self::BIOMETRIC_NOTICE => 'Biometric Notice',
```

- [ ] **Step 4: Add the presentation entry**

In `PolicyController::PRESENTATION`, after the `PHOTOGRAPHER_TERMS` entry add:

```php
        WebsitePolicy::BIOMETRIC_NOTICE => [
            'meta_description' => 'How WiVor uses face matching to help athletes find their event photos.',
            'default_view' => 'frontend.v1.policies.biometric-notice',
        ],
```

- [ ] **Step 5: Add the route**

In `routes/frontend.php`, after the `photographer-terms` route (line 96) add:

```php
Route::get('/biometric-notice', [PolicyController::class, 'show'])
    ->defaults('policy', WebsitePolicy::BIOMETRIC_NOTICE)
    ->name('biometric-notice');
```

- [ ] **Step 6: Create the fallback view**

The text reuses the existing Privacy Policy wording (`policies/privacy.blade.php:30-33,42`). Do not add new legal promises.

`resources/views/frontend/v1/policies/biometric-notice.blade.php`:

```blade
@extends('frontend.v1.layouts.app')

@section('title', 'Biometric Notice | WiVor')
@section('meta-description', 'How WiVor uses face matching to help athletes find their event photos.')

@section('content')
    <main class="container py-5">
        <div class="row justify-content-center">
            <article class="col-lg-8">
                <h1 class="mb-3">Biometric Notice</h1>
                <p class="text-muted fw-semibold">Last updated: October 18, 2026</p>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4 p-md-5">
                        <p>This notice explains how face matching is used on WiVor. It adds to our <a href="{{ route('privacy') }}">Privacy Policy</a>.</p>

                        <h2 class="h4 mt-4">Face matching in event photographs</h2>
                        <p>When face search is enabled, published event photographs may be processed by Amazon Rekognition. Facial features found in those photographs may be represented as face metadata in an event-specific Rekognition collection and associated with the photograph's WivorPhotos identifier. This is separate from the voluntary selfie-search flow.</p>

                        <h2 class="h4 mt-4">Selfie search</h2>
                        <p>A visitor who chooses selfie search uploads a JPEG or PNG image with consent. WivorPhotos reads that temporary request upload into memory and sends the image bytes to Amazon Rekognition to search the selected event collection. WivorPhotos does not intentionally save the search selfie to Amazon S3, its database, the visitor's session, or a queue payload. The temporary request ends after the search response. Amazon processes the image as WivorPhotos' service provider under Amazon's applicable service terms.</p>

                        <h2 class="h4 mt-4">Deletion</h2>
                        <p>When a photograph is unpublished, removed, or expires through the current retention process, WivorPhotos queues deletion of its associated face metadata from the event collection. Operational retries or provider availability can affect when that cleanup completes.</p>

                        <h2 class="h4 mt-4">Your choices</h2>
                        <p class="mb-0">Selfie search is optional; visitors can browse galleries or use bib-number search instead. A person shown in a photograph can use the <a href="{{ route('photo-removal.create') }}">Photo Removal Request</a> process. For privacy questions or requests, <a href="{{ route('contact_us') }}">contact WivorPhotos</a>.</p>
                    </div>
                </div>
            </article>
        </div>
    </main>
@endsection
```

- [ ] **Step 7: Add the admin sidebar link**

In `resources/views/layouts/sidebar.blade.php`, after the Photographer Terms `<li>` (ends line 94) add:

```blade
                            <li><a href="{{ route('admin.website.policies.edit', ['policy' => 'biometric-notice']) }}">Biometric
                                    Notice</a></li>
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter='BiometricNoticePageTest|PublicPolicyPagesTest|WebsitePolicyTest'`
Expected: `BiometricNoticePageTest` 3 PASS. `PublicPolicyPagesTest` all PASS. `WebsitePolicyTest` same as baseline (only the pre-existing admin-menu failure).

- [ ] **Step 9: Commit**

```bash
git add app/Models/WebsitePolicy.php app/Http/Controllers/Frontend/PolicyController.php routes/frontend.php resources/views/frontend/v1/policies/biometric-notice.blade.php resources/views/layouts/sidebar.blade.php tests/Feature/BiometricNoticePageTest.php
git commit -m "Add admin-editable Biometric Notice page required for face matching"
```

---

### Task 3: How It Works page

**Files:**
- Create: `resources/views/frontend/v1/pages/how-it-works.blade.php`
- Modify: `routes/frontend.php` (near line 102, the `faq` route)
- Test: `tests/Feature/HowItWorksPageTest.php`

**Interfaces:**
- Consumes: route `biometric-notice` (Task 2); CSS classes (Task 1).
- Produces: route names `how-it-works` (GET `/how-it-works`) and `athletes` (GET `/athletes`, temporary 302 to `/list-events`; Plan 2 replaces it with the real page).

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class HowItWorksPageTest extends TestCase
{
    public function test_how_it_works_shows_athlete_and_photographer_steps_with_face_search_enabled(): void
    {
        config(['face_recognition.enabled' => true]);

        $this->get('/how-it-works')
            ->assertOk()
            ->assertSee('<title>How WiVor Works | Photo Matching for Athletes</title>', false)
            ->assertSee('Simple for athletes. Simple for photographers.')
            ->assertSee("Here's how WiVor turns event photos into memories, step by step.", false)
            ->assertSeeInOrder([
                'For Athletes',
                'Find your event', 'Search for yourself', 'Enter your bib number or upload a selfie. We find the matches.',
                'Preview your photos', 'Buy what you love',
                'For Photographers',
                'Create a free account', 'Shoot the event',
                'WiVor reads faces and bib numbers and sorts photos for you.', 'Get paid',
            ])
            ->assertSee('Two ways to find you')
            ->assertSee('href="'.route('biometric-notice').'"', false)
            ->assertSee('href="'.route('athletes').'"', false)
            ->assertSee('href="'.route('photographers').'#register_section"', false);
    }

    public function test_how_it_works_hides_face_search_claims_when_face_search_is_disabled(): void
    {
        config(['face_recognition.enabled' => false]);

        $this->get(route('how-it-works'))
            ->assertOk()
            ->assertSee('Enter your bib number. We find the matches.')
            ->assertSee('WiVor reads bib numbers and sorts photos for you.')
            ->assertDontSee('selfie')
            ->assertDontSee('Face recognition')
            ->assertDontSee('Two ways to find you');
    }

    public function test_athletes_link_temporarily_sends_visitors_to_the_event_directory(): void
    {
        $this->get('/athletes')->assertRedirect(route('events.listEvents'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=HowItWorksPageTest`
Expected: FAIL (404; `Route [how-it-works] not defined`).

- [ ] **Step 3: Add the routes**

In `routes/frontend.php`, after `Route::view('/faq', ...)` (line 102) add:

```php
Route::view('/how-it-works', 'frontend.v1.pages.how-it-works')->name('how-it-works');
// Temporary until the athlete search page ships (marketing site plan 2).
Route::redirect('/athletes', '/list-events')->name('athletes');
```

- [ ] **Step 4: Create the view**

`resources/views/frontend/v1/pages/how-it-works.blade.php`:

```blade
@extends('frontend.v1.layouts.app')

@php($isFaceSearchEnabled = (bool) config('face_recognition.enabled'))

@php($metaDescription = $isFaceSearchEnabled ? 'See how WiVor matches athletes with event photos using face and bib recognition, and how photographers get paid.' : 'See how WiVor matches athletes with event photos using bib number recognition, and how photographers get paid.')

@section('title', 'How WiVor Works | Photo Matching for Athletes')
@section('meta-description', $metaDescription)

@section('content')
    <section class="wv-section wv-section--dark">
        <div class="wv-container">
            <h1 class="wv-h1">Simple for athletes. Simple for photographers.</h1>
            <p class="wv-lead">Here's how WiVor turns event photos into memories, step by step.</p>
        </div>
    </section>

    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <div class="row g-5">
                <div class="col-12 col-lg-6">
                    <h2 class="wv-h2">For Athletes</h2>
                    <ol class="list-unstyled">
                        <li class="mb-4">
                            <div class="wv-step-number">1</div>
                            <h3 class="wv-h3">Find your event</h3>
                            <p class="wv-body">Search for your race or event on WiVor.</p>
                        </li>
                        <li class="mb-4">
                            <div class="wv-step-number">2</div>
                            <h3 class="wv-h3">Search for yourself</h3>
                            @if ($isFaceSearchEnabled)
                                <p class="wv-body">Enter your bib number or upload a selfie. We find the matches.</p>
                            @else
                                <p class="wv-body">Enter your bib number. We find the matches.</p>
                            @endif
                        </li>
                        <li class="mb-4">
                            <div class="wv-step-number">3</div>
                            <h3 class="wv-h3">Preview your photos</h3>
                            <p class="wv-body">Browse every photo of you for free.</p>
                        </li>
                        <li>
                            <div class="wv-step-number">4</div>
                            <h3 class="wv-h3">Buy what you love</h3>
                            <p class="wv-body">Choose your favorites, pay securely and download.</p>
                        </li>
                    </ol>
                </div>
                <div class="col-12 col-lg-6">
                    <h2 class="wv-h2">For Photographers</h2>
                    <ol class="list-unstyled">
                        <li class="mb-4">
                            <div class="wv-step-number">1</div>
                            <h3 class="wv-h3">Create a free account</h3>
                            <p class="wv-body">Sign up in minutes. No fees.</p>
                        </li>
                        <li class="mb-4">
                            <div class="wv-step-number">2</div>
                            <h3 class="wv-h3">Shoot the event</h3>
                            <p class="wv-body">Photograph athletes as usual.</p>
                        </li>
                        <li class="mb-4">
                            <div class="wv-step-number">3</div>
                            <h3 class="wv-h3">Upload your photos</h3>
                            @if ($isFaceSearchEnabled)
                                <p class="wv-body">WiVor reads faces and bib numbers and sorts photos for you.</p>
                            @else
                                <p class="wv-body">WiVor reads bib numbers and sorts photos for you.</p>
                            @endif
                        </li>
                        <li>
                            <div class="wv-step-number">4</div>
                            <h3 class="wv-h3">Get paid</h3>
                            <p class="wv-body">When an athlete buys, WiVor handles the payment and you get your share.</p>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    @if ($isFaceSearchEnabled)
        <section class="wv-section wv-section--light">
            <div class="wv-container">
                <h2 class="wv-h2">Two ways to find you</h2>
                <p class="wv-lead">Bib number recognition reads the number on your race bib. Face recognition matches your face to photos taken at the event. Together, they make sure you don't miss a shot.</p>
                <a class="wv-link" href="{{ route('biometric-notice') }}">Learn how we protect your data →</a>
            </div>
        </section>
    @endif

    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <div class="wv-button-row">
                <a href="{{ route('athletes') }}" class="wv-btn wv-btn--primary">Find My Photos</a>
                <a href="{{ route('photographers') }}#register_section" class="wv-btn wv-btn--outline">Join as a Photographer</a>
            </div>
        </div>
    </section>
@endsection
```

Note: the flag-off test asserts `assertDontSee('selfie')` across the whole page, header and footer included. The footer (Task 8) must not contain "selfie".

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=HowItWorksPageTest`
Expected: PASS (3 tests).

- [ ] **Step 6: Commit**

```bash
git add routes/frontend.php resources/views/frontend/v1/pages/how-it-works.blade.php tests/Feature/HowItWorksPageTest.php
git commit -m "Add How It Works page with flag-gated face search copy"
```

---

### Task 4: FAQ page

**Files:**
- Rewrite: `resources/views/frontend/v1/pages/faq.blade.php`
- Test: `tests/Feature/FaqPageTest.php`

**Interfaces:**
- Consumes: routes `biometric-notice` (Task 2), `organizers` — **not yet defined**; Task 6 creates it. To keep tasks independent, this task links FAQ 8 with `url('/organizers')`, and Task 6 Step 5 switches it to `route('organizers')`.
- Produces: nothing new.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class FaqPageTest extends TestCase
{
    public function test_faq_lists_the_eight_specified_questions_in_order_with_face_search_enabled(): void
    {
        config(['face_recognition.enabled' => true]);

        $this->get(route('faq'))
            ->assertOk()
            ->assertSeeInOrder([
                'Is WiVor really free?',
                'How does WiVor find my photos?',
                'Do I have to buy photos?',
                'How do I receive my photos?',
                "What if I can't find my photos?",
                'How is my face data used and protected?',
                'How do photographers get paid?',
                'How can my event partner with WiVor?',
            ], false)
            ->assertSee('Enter your bib number, upload a selfie, or both.')
            ->assertSee('href="'.route('biometric-notice').'"', false);
    }

    public function test_faq_hides_face_data_question_when_face_search_is_disabled(): void
    {
        config(['face_recognition.enabled' => false]);

        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('We use bib number recognition. Enter your bib number.')
            ->assertDontSee('How is my face data used and protected?')
            ->assertDontSee('selfie');
    }

    public function test_faq_uses_configured_support_email_and_shows_no_unfilled_placeholders(): void
    {
        config(['contact.email' => 'help@example.test']);

        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('href="mailto:help@example.test"', false)
            ->assertDontSee('[support email]')
            ->assertDontSee('[Confirm')
            ->assertDontSee('[Insert');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=FaqPageTest`
Expected: FAIL (`Is WiVor really free?` not found).

- [ ] **Step 3: Rewrite the view**

Replace all of `resources/views/frontend/v1/pages/faq.blade.php` with:

```blade
@extends('frontend.v1.layouts.app')

@php($isFaceSearchEnabled = (bool) config('face_recognition.enabled'))

@section('title', 'FAQ | WiVor')
@section('meta-description', 'Answers about finding, previewing and buying your event photos on WiVor, and how photographers and event organizers work with WiVor.')

@section('content')
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <h1 class="wv-h1">Frequently Asked Questions</h1>

            <div class="wv-faq">
                <details>
                    <summary>Is WiVor really free?</summary>
                    <div class="wv-body">Yes. It's free for photographers, event organizers and athletes. Athletes only pay if they like their photos and choose to buy them.</div>
                </details>

                <details>
                    <summary>How does WiVor find my photos?</summary>
                    @if ($isFaceSearchEnabled)
                        <div class="wv-body">We use bib number recognition and face recognition. Enter your bib number, upload a selfie, or both.</div>
                    @else
                        <div class="wv-body">We use bib number recognition. Enter your bib number.</div>
                    @endif
                </details>

                <details>
                    <summary>Do I have to buy photos?</summary>
                    <div class="wv-body">No. You can browse your photos for free and buy only the ones you want.</div>
                </details>

                <details>
                    <summary>How do I receive my photos?</summary>
                    <div class="wv-body">After purchase, you can download high-resolution files right away.</div>
                </details>

                <details>
                    <summary>What if I can't find my photos?</summary>
                    <div class="wv-body">Photographers may still be uploading. Check back in a day or two, or contact us at <a class="wv-link" href="mailto:{{ config('contact.email') }}">{{ config('contact.email') }}</a>.</div>
                </details>

                @if ($isFaceSearchEnabled)
                    <details>
                        <summary>How is my face data used and protected?</summary>
                        <div class="wv-body">Your selfie is used only to match you with your event photos. Read our <a class="wv-link" href="{{ route('biometric-notice') }}">Biometric Notice</a>.</div>
                    </details>
                @endif

                <details>
                    <summary>How do photographers get paid?</summary>
                    <div class="wv-body">WiVor handles payments. When an athlete buys a photo, the photographer receives their share.</div>
                </details>

                <details>
                    <summary>How can my event partner with WiVor?</summary>
                    <div class="wv-body">Fill out the form on our <a class="wv-link" href="{{ url('/organizers') }}">Organizers page</a> and we'll get in touch.</div>
                </details>
            </div>
        </div>
    </section>
@endsection
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter='FaqPageTest|PublicPolicyPagesTest'`
Expected: `FaqPageTest` 3 PASS. `PublicPolicyPagesTest` unchanged (still PASS; header still old).

- [ ] **Step 5: Commit**

```bash
git add resources/views/frontend/v1/pages/faq.blade.php tests/Feature/FaqPageTest.php
git commit -m "Replace FAQ with the eight specified questions"
```

---

### Task 5: About page and `/about-us` redirect

**Files:**
- Rewrite: `resources/views/frontend/v1/pages/about.blade.php`
- Modify: `routes/frontend.php:101`
- Test: `tests/Feature/AboutPageTest.php`

**Interfaces:**
- Produces: route name `about` (GET `/about`). Route name `about_us` stays defined (now a 301 to `/about`), so existing `route('about_us')` calls keep working.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class AboutPageTest extends TestCase
{
    public function test_about_page_shows_mission_values_and_contact_call_to_action(): void
    {
        config(['face_recognition.enabled' => true]);

        $this->get('/about')
            ->assertOk()
            ->assertSee('Every athlete deserves their photos.')
            ->assertSee('using face and bib number recognition, and we take care of payments along the way.')
            ->assertSeeInOrder(['Our values', 'Simple', 'Fair', 'Professional'])
            ->assertSee('href="'.route('contact_us').'"', false)
            ->assertSee('Get in touch');
    }

    public function test_about_page_omits_face_recognition_when_face_search_is_disabled(): void
    {
        config(['face_recognition.enabled' => false]);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('using bib number recognition, and we take care of payments along the way.')
            ->assertDontSee('face recognition')
            ->assertDontSee('face and bib');
    }

    public function test_old_about_us_address_permanently_redirects_to_about(): void
    {
        $this->get('/about-us')->assertStatus(301)->assertRedirect('/about');
    }
}
```

Note: do not assert `assertDontSee('face')`; the footer's `fa-facebook-f` icon class contains `face`.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AboutPageTest`
Expected: FAIL (404 on `/about`).

- [ ] **Step 3: Change the routes**

Replace line 101 of `routes/frontend.php`:

```php
Route::get('/about-us', [HomeController::class, 'aboutUs'])->name('about_us');
```

with:

```php
Route::get('/about', [HomeController::class, 'aboutUs'])->name('about');
Route::permanentRedirect('/about-us', '/about')->name('about_us');
```

`HomeController::aboutUs` is already excluded from the `auth` middleware.

- [ ] **Step 4: Rewrite the view**

Replace all of `resources/views/frontend/v1/pages/about.blade.php` with:

```blade
@extends('frontend.v1.layouts.app')

@php($isFaceSearchEnabled = (bool) config('face_recognition.enabled'))

@section('title', 'About | WiVor')
@section('meta-description', 'WiVor is an online platform that connects photographers and athletes.')

@section('content')
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <h1 class="wv-h1">Every athlete deserves their photos.</h1>
            <p class="wv-lead">WiVor is an online platform that connects photographers and athletes. Photographers capture the moment. We make sure the right athlete finds the right photo, using {{ $isFaceSearchEnabled ? 'face and bib number recognition' : 'bib number recognition' }}, and we take care of payments along the way. We believe event photos should be easy to find, fair for photographers, and free of risk for athletes.</p>
        </div>
    </section>

    <section class="wv-section wv-section--light">
        <div class="wv-container">
            <h2 class="wv-h2">Our values</h2>
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <div class="wv-card"><h3 class="wv-h3 mb-0">Simple</h3></div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="wv-card"><h3 class="wv-h3 mb-0">Fair</h3></div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="wv-card"><h3 class="wv-h3 mb-0">Professional</h3></div>
                </div>
            </div>
        </div>
    </section>

    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <a href="{{ route('contact_us') }}" class="wv-btn wv-btn--primary">Get in touch</a>
        </div>
    </section>
@endsection
```

Old `about.blade.php` used `messages.about_*` lang keys. Leave those keys in `resources/lang/*/messages.php`; list them in the task report as now unused (Junior decides about deletion).

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --filter=AboutPageTest`
Expected: PASS (3 tests).

- [ ] **Step 6: Commit**

```bash
git add routes/frontend.php resources/views/frontend/v1/pages/about.blade.php tests/Feature/AboutPageTest.php
git commit -m "Move About page to /about with spec copy and redirect old address"
```

---

### Task 6: Organizers page (without form)

**Files:**
- Create: `resources/views/frontend/v1/pages/organizers.blade.php`
- Modify: `routes/frontend.php` (after the `how-it-works` route from Task 3)
- Modify: `resources/views/frontend/v1/pages/faq.blade.php` (FAQ 8 link)
- Test: `tests/Feature/OrganizersPageTest.php`

**Interfaces:**
- Produces: route name `organizers` (GET `/organizers`). Plan 3 adds the partnership form to this view.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrganizersPageTest extends TestCase
{
    public function test_organizers_page_shows_benefits_steps_and_partner_call_to_action(): void
    {
        $this->get('/organizers')
            ->assertOk()
            ->assertSee('<title>Event Photography Partner | WiVor for Organizers</title>', false)
            ->assertSee("Give your athletes the photos they'll remember.", false)
            ->assertSee('Partner with WiVor and offer professional event photos with no cost to you and no extra work for your team.')
            ->assertSeeInOrder(['Zero cost', 'No extra work', 'Better athlete experience', 'Free marketing'])
            ->assertSeeInOrder(['Tell us about your event', 'We coordinate photographers', 'Athletes find and buy their photos'])
            ->assertSee('Become a Partner')
            ->assertSee('href="'.route('contact_us').'"', false)
            ->assertDontSee('Georgia Multiports');
    }

    public function test_faq_partnership_answer_links_to_the_organizers_page(): void
    {
        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('href="'.route('organizers').'"', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=OrganizersPageTest`
Expected: FAIL (404; `Route [organizers] not defined`).

- [ ] **Step 3: Add the route**

In `routes/frontend.php`, after the `how-it-works` route add:

```php
Route::view('/organizers', 'frontend.v1.pages.organizers')->name('organizers');
```

- [ ] **Step 4: Create the view**

`resources/views/frontend/v1/pages/organizers.blade.php`:

```blade
@extends('frontend.v1.layouts.app')

@section('title', 'Event Photography Partner | WiVor for Organizers')
@section('meta-description', 'Give your athletes professional photos at no cost to you. Partner with WiVor for your next race.')

@section('content')
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <h1 class="wv-h1">Give your athletes the photos they'll remember.</h1>
            <p class="wv-lead">Partner with WiVor and offer professional event photos with no cost to you and no extra work for your team.</p>
            <div class="wv-button-row">
                {{-- Points to Contact until the partnership form ships (marketing site plan 3). --}}
                <a href="{{ route('contact_us') }}" class="wv-btn wv-btn--primary">Become a Partner</a>
            </div>
        </div>
    </section>

    <section class="wv-section wv-section--light">
        <div class="wv-container">
            <div class="row g-4">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="wv-card">
                        <i class="fas fa-hand-holding-usd wv-icon" aria-hidden="true"></i>
                        <h3 class="wv-h3">Zero cost</h3>
                        <p class="wv-body mb-0">WiVor is free for organizers.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="wv-card">
                        <i class="fas fa-flag-checkered wv-icon" aria-hidden="true"></i>
                        <h3 class="wv-h3">No extra work</h3>
                        <p class="wv-body mb-0">We connect photographers and athletes. You focus on the race.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="wv-card">
                        <i class="fas fa-running wv-icon" aria-hidden="true"></i>
                        <h3 class="wv-h3">Better athlete experience</h3>
                        <p class="wv-body mb-0">Athletes get their photos fast and share them, which promotes your event.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="wv-card">
                        <i class="fas fa-bullhorn wv-icon" aria-hidden="true"></i>
                        <h3 class="wv-h3">Free marketing</h3>
                        <p class="wv-body mb-0">Every shared photo shows your event to new participants.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <ol class="row g-4 list-unstyled mb-0">
                <li class="col-12 col-md-4">
                    <div class="wv-step-number">1</div>
                    <h3 class="wv-h3">Tell us about your event</h3>
                </li>
                <li class="col-12 col-md-4">
                    <div class="wv-step-number">2</div>
                    <h3 class="wv-h3">We coordinate photographers</h3>
                </li>
                <li class="col-12 col-md-4">
                    <div class="wv-step-number">3</div>
                    <h3 class="wv-h3">Athletes find and buy their photos</h3>
                </li>
            </ol>
        </div>
    </section>
@endsection
```

- [ ] **Step 5: Point FAQ 8 at the named route**

In `resources/views/frontend/v1/pages/faq.blade.php` replace:

```blade
<a class="wv-link" href="{{ url('/organizers') }}">Organizers page</a>
```

with:

```blade
<a class="wv-link" href="{{ route('organizers') }}">Organizers page</a>
```

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --filter='OrganizersPageTest|FaqPageTest'`
Expected: PASS (5 tests).

- [ ] **Step 7: Commit**

```bash
git add routes/frontend.php resources/views/frontend/v1/pages/organizers.blade.php resources/views/frontend/v1/pages/faq.blade.php tests/Feature/OrganizersPageTest.php
git commit -m "Add Organizers page with partner call to action"
```

---

### Task 7: Home page

**Files:**
- Rewrite: `resources/views/frontend/v1/home/index.blade.php`
- Modify: `app/Http/Controllers/HomeController.php:47-56`
- Modify: `tests/Feature/EventMvpTest.php:18-75` (remove two tests)
- Modify: `tests/Feature/FrontendTruthAlignmentTest.php:37-50` (rewrite one test)
- Test: `tests/Feature/HomePageTest.php`

**Interfaces:**
- Consumes: routes `athletes`, `how-it-works` (Task 3), `organizers` (Task 6), `photographers`.

Why existing tests change: `EventMvpTest::test_homepage_prioritizes_existing_event_search_and_recent_published_events` and `test_homepage_explains_when_no_published_events_are_available` pin the event search form and "Recently Published Events" strip; the spec Home has neither (see "Decisions in this plan"). The directory filters stay covered by `test_public_directory_filters_published_events`. `FrontendTruthAlignmentTest`'s homepage test pins the old hero copy; its purpose (no face or video marketing when the flag is off) is kept in the rewrite.

- [ ] **Step 1: Write the failing test**

`tests/Feature/HomePageTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_page_shows_specified_sections_in_order_with_face_search_enabled(): void
    {
        config(['face_recognition.enabled' => true]);

        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('<title>WiVor | Find and Buy Your Race Photos</title>', false)
            ->assertSeeInOrder([
                'Your best moments. Found in seconds.',
                'WiVor uses face and bib number recognition to match you with the photos photographers took of you at your event.',
                'Free for athletes, photographers and event organizers.',
                'Free to join', 'Smart matching', 'Pay only if you love it',
                'How WiVor works',
                'Photographers shoot', 'We match you', 'You choose',
                'Made for everyone at the event',
                'Athletes', 'Photographers', 'Event Organizers',
                'Why athletes and photographers choose WiVor',
                'No more endless searching', 'Fair for photographers', 'No risk for athletes', 'Built for events',
                'Ready to find your photos?',
                'Search by your name, bib number or a selfie.',
            ])
            ->assertSee("I'm a Photographer", false)
            ->assertSee('href="'.route('athletes').'"', false)
            ->assertSee('href="'.route('how-it-works').'"', false)
            ->assertSee('href="'.route('organizers').'"', false)
            ->assertSee('href="'.route('photographers').'#register_section"', false);
    }

    public function test_home_page_hides_unverified_testimonials_and_partner_logos(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertDontSee('What our community says')
            ->assertDontSee('Trusted by')
            ->assertDontSee('[Quote]');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=HomePageTest`
Expected: FAIL (`Your best moments. Found in seconds.` not found).

- [ ] **Step 3: Rewrite the existing homepage truth test**

In `tests/Feature/FrontendTruthAlignmentTest.php` replace the method `test_homepage_preserves_supported_customer_claims_without_global_face_or_video_marketing` (lines 37-50) with:

```php
    public function test_homepage_shows_bib_only_matching_without_face_or_video_marketing_when_face_search_is_disabled(): void
    {
        config(['face_recognition.enabled' => false]);

        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('WiVor uses bib number recognition to match you with the photos photographers took of you at your event.')
            ->assertSee('Bib number recognition finds your photos automatically.')
            ->assertSee('Our platform identifies athletes using bib numbers.')
            ->assertSee('Search by your name or bib number.')
            ->assertDontSee('selfie')
            ->assertDontSee('face recognition')
            ->assertDontSee('Video Editing')
            ->assertDontSee('video marketplace');
    }
```

- [ ] **Step 4: Remove the two homepage tests from `EventMvpTest`**

Delete `test_homepage_prioritizes_existing_event_search_and_recent_published_events` (lines 18-67) and `test_homepage_explains_when_no_published_events_are_available` (lines 69-75) from `tests/Feature/EventMvpTest.php`. Keep the `createEvent` helper; other tests use it. Run `grep -n "createEvent\|Storage\|UploadedFile" tests/Feature/EventMvpTest.php` and remove any `use` import that no longer has a usage.

- [ ] **Step 5: Run the rewritten test to verify it fails**

Run: `php artisan test --filter=FrontendTruthAlignmentTest`
Expected: the new homepage test FAILs (`WiVor uses bib number recognition...` not found). Other tests unchanged from baseline.

- [ ] **Step 6: Simplify the controller**

In `app/Http/Controllers/HomeController.php` replace `welcome()` (lines 47-56) with:

```php
    /** Display the public homepage. */
    public function welcome(): View
    {
        return view('frontend.v1.home.index');
    }
```

Then remove `use App\Models\Event;` (line 5) if `grep -n "Event::" app/Http/Controllers/HomeController.php` shows no other use.

- [ ] **Step 7: Rewrite the view**

Replace all of `resources/views/frontend/v1/home/index.blade.php` with:

```blade
@extends('frontend.v1.layouts.app')

@php($isFaceSearchEnabled = (bool) config('face_recognition.enabled'))

@php($metaDescription = $isFaceSearchEnabled ? 'WiVor connects photographers and athletes. Find your event photos with face and bib recognition. Free to use. Pay only for the photos you love.' : 'WiVor connects photographers and athletes. Find your event photos with bib number recognition. Free to use. Pay only for the photos you love.')

@section('title', 'WiVor | Find and Buy Your Race Photos')
@section('meta-description', $metaDescription)

@section('content')
    {{-- 4.1 Hero. Text only until Victor supplies the athlete photo (marketing site plan 4). --}}
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <h1 class="wv-h1">Your best moments. Found in seconds.</h1>
            @if ($isFaceSearchEnabled)
                <p class="wv-lead">WiVor uses face and bib number recognition to match you with the photos photographers took of you at your event. Browse for free. Pay only for the ones you love.</p>
            @else
                <p class="wv-lead">WiVor uses bib number recognition to match you with the photos photographers took of you at your event. Browse for free. Pay only for the ones you love.</p>
            @endif
            <div class="wv-button-row">
                <a href="{{ route('athletes') }}" class="wv-btn wv-btn--primary">Find My Photos</a>
                <a href="{{ route('photographers') }}" class="wv-btn wv-btn--outline">I'm a Photographer</a>
            </div>
            <p class="wv-small">Free for athletes, photographers and event organizers.</p>
        </div>
    </section>

    {{-- 4.2 Benefits strip --}}
    <section class="wv-section wv-section--dark">
        <div class="wv-container">
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <i class="fas fa-user-plus wv-icon" aria-hidden="true"></i>
                    <h2 class="wv-h3">Free to join</h2>
                    <p class="wv-body mb-0">No sign-up fees for photographers, organizers or athletes.</p>
                </div>
                <div class="col-12 col-md-4">
                    <i class="fas fa-search wv-icon" aria-hidden="true"></i>
                    <h2 class="wv-h3">Smart matching</h2>
                    @if ($isFaceSearchEnabled)
                        <p class="wv-body mb-0">Face and bib number recognition finds your photos automatically.</p>
                    @else
                        <p class="wv-body mb-0">Bib number recognition finds your photos automatically.</p>
                    @endif
                </div>
                <div class="col-12 col-md-4">
                    <i class="fas fa-heart wv-icon" aria-hidden="true"></i>
                    <h2 class="wv-h3">Pay only if you love it</h2>
                    <p class="wv-body mb-0">See your photos first. Buy only the ones you want.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 4.3 How it works summary --}}
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <h2 class="wv-h2">How WiVor works</h2>
            <ol class="row g-4 list-unstyled">
                <li class="col-12 col-md-4">
                    <div class="wv-step-number">1</div>
                    <h3 class="wv-h3">Photographers shoot</h3>
                    <p class="wv-body">Photographers capture athletes at races and events and upload their photos to WiVor.</p>
                </li>
                <li class="col-12 col-md-4">
                    <div class="wv-step-number">2</div>
                    <h3 class="wv-h3">We match you</h3>
                    @if ($isFaceSearchEnabled)
                        <p class="wv-body">Our platform identifies athletes using face recognition and bib numbers.</p>
                    @else
                        <p class="wv-body">Our platform identifies athletes using bib numbers.</p>
                    @endif
                </li>
                <li class="col-12 col-md-4">
                    <div class="wv-step-number">3</div>
                    <h3 class="wv-h3">You choose</h3>
                    <p class="wv-body">Find your photos, pick your favorites and download them in full quality.</p>
                </li>
            </ol>
            <a class="wv-link" href="{{ route('how-it-works') }}">See the full process →</a>
        </div>
    </section>

    {{-- 4.4 Three audiences. Icons until audience photos arrive (plan 4). --}}
    <section class="wv-section wv-section--light">
        <div class="wv-container">
            <h2 class="wv-h2">Made for everyone at the event</h2>
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <div class="wv-card">
                        <i class="fas fa-running wv-icon" aria-hidden="true"></i>
                        <h3 class="wv-h3">Athletes</h3>
                        <p class="wv-body">Find every photo of you, without scrolling through thousands of images.</p>
                        <a class="wv-link" href="{{ route('athletes') }}">Find my photos →</a>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="wv-card">
                        <i class="fas fa-camera wv-icon" aria-hidden="true"></i>
                        <h3 class="wv-h3">Photographers</h3>
                        <p class="wv-body">Upload your work, reach more athletes and let WiVor handle payments.</p>
                        <a class="wv-link" href="{{ route('photographers') }}#register_section">Join as a photographer →</a>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="wv-card">
                        <i class="fas fa-flag-checkered wv-icon" aria-hidden="true"></i>
                        <h3 class="wv-h3">Event Organizers</h3>
                        <p class="wv-body">Offer your athletes professional photos with zero cost and zero extra work.</p>
                        <a class="wv-link" href="{{ route('organizers') }}">Partner with us →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 4.5 Why WiVor --}}
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <div class="row g-5">
                <div class="col-12 col-lg-5">
                    <h2 class="wv-h2">Why athletes and photographers choose WiVor</h2>
                </div>
                <div class="col-12 col-lg-7">
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <i class="fas fa-images wv-icon" aria-hidden="true"></i>
                            <h3 class="wv-h3">No more endless searching</h3>
                            <p class="wv-body mb-0">Skip the folders and galleries. Your photos come to you.</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <i class="fas fa-balance-scale wv-icon" aria-hidden="true"></i>
                            <h3 class="wv-h3">Fair for photographers</h3>
                            <p class="wv-body mb-0">Photographers get paid when athletes buy. WiVor handles checkout and payments.</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <i class="fas fa-shield-alt wv-icon" aria-hidden="true"></i>
                            <h3 class="wv-h3">No risk for athletes</h3>
                            <p class="wv-body mb-0">Viewing is free. You only pay for photos you choose to buy.</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <i class="fas fa-medal wv-icon" aria-hidden="true"></i>
                            <h3 class="wv-h3">Built for events</h3>
                            <p class="wv-body mb-0">From local run clubs to triathlons, WiVor works for any event with bib numbers.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 4.6 Social proof: hidden until real testimonials and partner logo permission exist (spec 4.6). --}}

    {{-- 4.7 Final CTA --}}
    <section class="wv-section wv-section--orange">
        <div class="wv-container">
            <h2 class="wv-h2">Ready to find your photos?</h2>
            @if ($isFaceSearchEnabled)
                <p class="wv-lead">Search by your name, bib number or a selfie.</p>
            @else
                <p class="wv-lead">Search by your name or bib number.</p>
            @endif
            <div class="wv-button-row">
                <a href="{{ route('athletes') }}" class="wv-btn wv-btn--inverse">Find My Photos</a>
            </div>
        </div>
    </section>
@endsection
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter='HomePageTest|FrontendTruthAlignmentTest|EventMvpTest|ExampleTest'`
Expected: `HomePageTest` 2 PASS; new truth test PASS; `EventMvpTest` all PASS; `ExampleTest` PASS. Only the baseline `authenticated legacy view` failure remains.

- [ ] **Step 9: Commit**

```bash
git add resources/views/frontend/v1/home/index.blade.php app/Http/Controllers/HomeController.php tests/Feature/HomePageTest.php tests/Feature/FrontendTruthAlignmentTest.php tests/Feature/EventMvpTest.php
git commit -m "Rebuild Home with spec sections and flag-gated matching copy"
```

---

### Task 8: Header and footer

**Files:**
- Rewrite: `resources/views/frontend/v1/components/header.blade.php`
- Rewrite: `resources/views/frontend/v1/components/footer.blade.php`
- Create: `public/assets/js/wivor-nav.js`
- Modify: `public/assets/css/wivor-brand.css` (append header/footer styles)
- Modify: `tests/Feature/PublicPolicyPagesTest.php:54-67` (rewrite header test)
- Test: `tests/Feature/PublicHeaderFooterTest.php`

**Interfaces:**
- Consumes: routes `how-it-works`, `athletes`, `organizers`, `about`, `biometric-notice`, `faq`, `photographers`, `contact_us`, `privacy`, `terms`, `refund-policy`, `photographer-terms`, `photo-removal.create`, `cart.show`, `login`, `welcome`.
- Contract kept with `layouts/app.blade.php`: the header opens `<div class="boxed_wrapper">`; the footer closes it. Change: the `<header>` now sits **before** the wrapper, because `.boxed_wrapper` has `overflow: hidden !important` (`style.css:112-116`), which breaks `position: sticky`.

Why the existing header test changes: `PublicPolicyPagesTest::test_public_header_keeps_the_customer_navigation_simple_and_searches_events` pins the old nav labels and the search popup. The spec replaces both. The rewrite keeps the test's intent (simple customer navigation) with the spec labels.

- [ ] **Step 1: Write the failing test**

`tests/Feature/PublicHeaderFooterTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHeaderFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_shows_specified_navigation_and_find_my_photos_button(): void
    {
        $this->get(route('faq'))
            ->assertOk()
            ->assertSeeInOrder([
                'href="'.route('how-it-works').'"', 'How It Works',
                'href="'.route('athletes').'"', 'Athletes',
                'href="'.route('photographers').'"', 'Photographers',
                'href="'.route('organizers').'"', 'Organizers',
                'href="'.route('faq').'"', 'FAQ',
            ], false)
            ->assertSee('class="wv-btn wv-btn--primary wv-header__cta"', false)
            ->assertSee('src="'.asset('assets/images/logo/wivor.png').'"', false);
    }

    public function test_header_has_mobile_menu_toggle_and_keeps_cart_and_login(): void
    {
        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('aria-controls="wv-primary-nav"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('href="'.route('cart.show').'"', false)
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('src="'.asset('assets/js/wivor-nav.js').'"', false);
    }

    public function test_header_still_shows_flash_and_validation_messages(): void
    {
        $this->withSession(['success' => 'Saved successfully.', 'error' => 'Something failed.'])
            ->get(route('faq'))
            ->assertOk()
            ->assertSee('Saved successfully.')
            ->assertSee('Something failed.');
    }

    public function test_footer_has_four_link_columns_white_logo_and_copyright(): void
    {
        $response = $this->get(route('faq'))->assertOk();

        $response->assertSeeInOrder(['Platform', 'For You', 'Company', 'Legal'])
            ->assertSee('src="'.asset('assets/images/logo/wivor_white.png').'"', false)
            ->assertSee('© 2026 WiVor. All rights reserved.')
            ->assertSee('href="https://www.instagram.com/wivor.photos/"', false)
            ->assertSee('href="https://www.facebook.com/p/WiVor-Photos-61573081696201/"', false);

        foreach (['how-it-works', 'faq', 'athletes', 'photographers', 'organizers', 'about', 'contact_us', 'privacy', 'terms', 'biometric-notice'] as $routeName) {
            $response->assertSee('href="'.route($routeName).'"', false);
        }
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PublicHeaderFooterTest`
Expected: FAIL (`aria-controls="wv-primary-nav"` not found, etc.). The flash test may already pass; that is fine, it guards the rewrite.

- [ ] **Step 3: Rewrite the existing header test**

In `tests/Feature/PublicPolicyPagesTest.php` replace `test_public_header_keeps_the_customer_navigation_simple_and_searches_events` (lines 54-67) with:

```php
    public function test_public_header_keeps_the_customer_navigation_simple(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertSeeInOrder([
                'How It Works',
                'Athletes',
                'Photographers',
                'Organizers',
                'FAQ',
                'Find My Photos',
            ]);
    }
```

- [ ] **Step 4: Rewrite the header**

Replace all of `resources/views/frontend/v1/components/header.blade.php` with:

```blade
@php($cartCount = count((array) session('wivor_cart', [])))
<header class="wv-header">
    <div class="wv-container wv-header__bar">
        <a href="{{ route('welcome') }}" class="wv-header__logo">
            <img src="{{ asset('assets/images/logo/wivor.png') }}" alt="WiVor home">
        </a>

        <nav id="wv-primary-nav" class="wv-header__nav" aria-label="Main navigation">
            <ul class="wv-header__links">
                <li><a href="{{ route('how-it-works') }}" @class(['is-current' => request()->routeIs('how-it-works')])>How It Works</a></li>
                <li><a href="{{ route('athletes') }}" @class(['is-current' => request()->routeIs('athletes')])>Athletes</a></li>
                <li><a href="{{ route('photographers') }}" @class(['is-current' => request()->routeIs('photographers')])>Photographers</a></li>
                <li><a href="{{ route('organizers') }}" @class(['is-current' => request()->routeIs('organizers')])>Organizers</a></li>
                <li><a href="{{ route('faq') }}" @class(['is-current' => request()->routeIs('faq')])>FAQ</a></li>
            </ul>
            <ul class="wv-header__utility">
                <li>
                    <a href="{{ route('cart.show') }}"
                        aria-label="View cart with {{ $cartCount }} selected {{ $cartCount === 1 ? 'photo' : 'photos' }}">
                        <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                        @if ($cartCount > 0)
                            <span class="wv-header__cart-count" aria-hidden="true">{{ $cartCount }}</span>
                        @endif
                    </a>
                </li>
                <li><a href="{{ route('login') }}">Log in</a></li>
            </ul>
        </nav>

        <a href="{{ route('athletes') }}" class="wv-btn wv-btn--primary wv-header__cta">Find My Photos</a>

        <button type="button" class="wv-header__toggle" aria-controls="wv-primary-nav" aria-expanded="false"
            aria-label="Open menu">
            <span class="wv-header__toggle-bar"></span>
            <span class="wv-header__toggle-bar"></span>
            <span class="wv-header__toggle-bar"></span>
        </button>
    </div>
</header>

<div class="boxed_wrapper">
    @if (session()->has('success'))
        <div class="alert alert-success mb-0" role="alert">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mb-0" role="alert">
            @if (is_array(session('error')))
                {{ implode(', ', session('error')) }}
            @else
                {{ session('error') }}
            @endif
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger mb-0" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
```

(The `boxed_wrapper` div stays open on purpose; the footer closes it.)

- [ ] **Step 5: Rewrite the footer**

Replace all of `resources/views/frontend/v1/components/footer.blade.php` with:

```blade
<div class="scroll-to-top">
    <div>
        <div class="scroll-top-inner">
            <div class="scroll-bar">
                <div class="bar-inner"></div>
            </div>
            <div class="scroll-bar-text">Go To Top</div>
        </div>
    </div>
</div>

</div> <!-- close from header <div class="boxed_wrapper"> -->

<footer class="wv-footer">
    <div class="wv-container">
        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <a href="{{ route('welcome') }}" class="wv-footer__logo">
                    <img src="{{ asset('assets/images/logo/wivor_white.png') }}" alt="WiVor home">
                </a>
                <ul class="wv-footer__social">
                    <li><a href="https://www.instagram.com/wivor.photos/" target="_blank" rel="noopener noreferrer"
                            aria-label="WiVor on Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a></li>
                    <li><a href="https://www.facebook.com/p/WiVor-Photos-61573081696201/" target="_blank"
                            rel="noopener noreferrer" aria-label="WiVor on Facebook"><i class="fab fa-facebook-f"
                                aria-hidden="true"></i></a></li>
                </ul>
            </div>
            <nav class="col-6 col-lg-2" aria-labelledby="wv-footer-platform">
                <h2 id="wv-footer-platform" class="wv-footer__title">Platform</h2>
                <ul>
                    <li><a href="{{ route('how-it-works') }}">How It Works</a></li>
                    <li><a href="{{ route('faq') }}">FAQ</a></li>
                </ul>
            </nav>
            <nav class="col-6 col-lg-2" aria-labelledby="wv-footer-for-you">
                <h2 id="wv-footer-for-you" class="wv-footer__title">For You</h2>
                <ul>
                    <li><a href="{{ route('athletes') }}">Athletes</a></li>
                    <li><a href="{{ route('photographers') }}">Photographers</a></li>
                    <li><a href="{{ route('organizers') }}">Organizers</a></li>
                </ul>
            </nav>
            <nav class="col-6 col-lg-2" aria-labelledby="wv-footer-company">
                <h2 id="wv-footer-company" class="wv-footer__title">Company</h2>
                <ul>
                    <li><a href="{{ route('about') }}">About</a></li>
                    <li><a href="{{ route('contact_us') }}">Contact</a></li>
                </ul>
            </nav>
            <nav class="col-6 col-lg-2" aria-labelledby="wv-footer-legal">
                <h2 id="wv-footer-legal" class="wv-footer__title">Legal</h2>
                <ul>
                    <li><a href="{{ route('privacy') }}">Privacy</a></li>
                    <li><a href="{{ route('terms') }}">Terms</a></li>
                    <li><a href="{{ route('biometric-notice') }}">Biometric Notice</a></li>
                    <li><a href="{{ route('refund-policy') }}">Refund Policy</a></li>
                    <li><a href="{{ route('photographer-terms') }}">Photographer Terms</a></li>
                    <li><a href="{{ route('photo-removal.create') }}">Photo Removal Request</a></li>
                </ul>
            </nav>
        </div>
        <p class="wv-footer__copyright">© 2026 WiVor. All rights reserved.</p>
    </div>
</footer>

<!-- jQuery plugins -->
<script src="{{ asset('assets/js/jquery.js') }}"></script>
<script src="{{ asset('assets/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/owl.js') }}"></script>
<script src="{{ asset('assets/js/wow.js') }}"></script>
<script src="{{ asset('assets/js/validation.js') }}"></script>
<script src="{{ asset('assets/js/jquery.fancybox.js') }}"></script>
<script src="{{ asset('assets/js/appear.js') }}"></script>
<script src="{{ asset('assets/js/scrollbar.js') }}"></script>
<script src="{{ asset('assets/js/isotope.js') }}"></script>
<script src="{{ asset('assets/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('assets/js/parallax-scroll.js') }}"></script>
<script src="{{ asset('assets/js/jquery-ui.js') }}"></script>
<script src="{{ asset('assets/js/nav-tool.js') }}"></script>
<script src="{{ asset('assets/js/jquery.bootstrap-touchspin.js') }}"></script>
<script src="{{ asset('assets/js/bxslider.js') }}"></script>

<!-- main-js -->
<script src="{{ asset('assets/js/script.js') }}"></script>
<script src="{{ asset('assets/js/wivor-nav.js') }}"></script>
```

The template scripts are unchanged (old pages still use owl, fancybox, etc.). The `currentYear` script is removed because the spec fixes the copyright line to 2026.

- [ ] **Step 6: Create the menu toggle script**

The menu uses its own toggle, not Bootstrap's `data-bs-toggle`, because the page loads two Bootstrap JS builds (CDN 5.3.3 in the head and template 4.4.1 in the footer).

`public/assets/js/wivor-nav.js`:

```js
// Opens and closes the public header navigation on small screens.
(function () {
    var toggle = document.querySelector('.wv-header__toggle');
    var navigation = document.getElementById('wv-primary-nav');

    if (!toggle || !navigation) {
        return;
    }

    toggle.addEventListener('click', function () {
        var isOpen = navigation.classList.toggle('is-open');

        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
    });
})();
```

- [ ] **Step 7: Append header and footer styles**

Append to `public/assets/css/wivor-brand.css`:

```css
/* Header (spec section 3): fixed at top, white, colored logo left. */
.wv-header {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: var(--wv-white);
    border-bottom: 1px solid var(--wv-border);
}

.wv-header__bar {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 72px;
}

.wv-header__logo img { height: 40px; width: auto; }

.wv-header__cta { margin-left: auto; padding: 10px 16px; }

.wv-header__toggle {
    display: inline-flex;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    width: 48px;
    height: 48px;
    padding: 12px;
    background: transparent;
    border: 1px solid var(--wv-border);
    border-radius: var(--wv-radius);
}

.wv-header__toggle-bar { display: block; height: 2px; background: var(--wv-slate); }

/* Mobile: links collapse into a panel under the bar. */
.wv-header__nav {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    padding: 8px 16px 16px;
    background: var(--wv-white);
    border-bottom: 1px solid var(--wv-border);
}

.wv-header__nav.is-open { display: block; }

.wv-header__links,
.wv-header__utility { list-style: none; margin: 0; padding: 0; }

.wv-header__nav a {
    display: flex;
    align-items: center;
    min-height: var(--wv-button-height);
    color: var(--wv-slate);
    text-decoration: none;
}

.wv-header__nav a:hover,
.wv-header__nav a.is-current { color: var(--wv-orange); }

.wv-header__utility { display: flex; gap: 20px; border-top: 1px solid var(--wv-border); margin-top: 8px; }

.wv-header__utility a { position: relative; }

.wv-header__cart-count {
    margin-left: 4px;
    padding: 0 6px;
    border-radius: 9px;
    background: var(--wv-orange);
    color: var(--wv-white);
    font-size: 12px;
}

@media (min-width: 992px) {
    .wv-header__toggle { display: none; }

    .wv-header__nav,
    .wv-header__nav.is-open {
        display: flex;
        position: static;
        align-items: center;
        gap: 24px;
        margin-left: auto;
        padding: 0;
        border: 0;
    }

    .wv-header__links { display: flex; gap: 24px; }

    .wv-header__utility { border-top: 0; margin-top: 0; gap: 16px; }

    .wv-header__cta { margin-left: 0; padding: 12px 24px; }
}

/* Footer (spec section 3): slate background, white logo, four columns. */
.wv-footer {
    padding: 56px 0 24px;
    background: var(--wv-slate);
    color: var(--wv-white);
}

.wv-footer__logo img { height: 40px; width: auto; }

.wv-footer__title { font-size: 16px; color: var(--wv-white); margin: 0 0 12px; }

.wv-footer ul { list-style: none; margin: 0; padding: 0; }

.wv-footer nav a,
.wv-footer__social a {
    display: inline-flex;
    align-items: center;
    min-height: 40px;
    color: var(--wv-white);
    text-decoration: none;
}

.wv-footer nav a:hover,
.wv-footer__social a:hover { color: var(--wv-orange-hover); }

.wv-footer__social { display: flex; gap: 16px; margin-top: 16px !important; font-size: 22px; }

.wv-footer__copyright {
    margin: 32px 0 0;
    padding-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    font-size: 14px;
    color: var(--wv-white);
}
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter='PublicHeaderFooterTest|PublicPolicyPagesTest|HowItWorksPageTest|AboutPageTest|FaqPageTest|HomePageTest|FrontendTruthAlignmentTest|HowToUploadPhotosTest'`
Expected: all PASS except the baseline `authenticated legacy view` failure. If a flag-off test now fails on `selfie` or `face`, the cause is header/footer markup; fix the markup, do not weaken the test.

- [ ] **Step 9: Commit**

```bash
git add resources/views/frontend/v1/components/header.blade.php resources/views/frontend/v1/components/footer.blade.php public/assets/js/wivor-nav.js public/assets/css/wivor-brand.css tests/Feature/PublicHeaderFooterTest.php tests/Feature/PublicPolicyPagesTest.php
git commit -m "Replace public header and footer with WiVor spec navigation"
```

---

### Task 9: Full verification

**Files:** none (fixes only if a check fails, in the task that owns the file).

- [ ] **Step 1: Full test suite**

Run: `php artisan test 2>&1 | tail -5`
Expected: only the 3 baseline failures from Task 0. Any other failure: stop and fix in the owning task.

- [ ] **Step 2: Route list sanity**

Run: `php artisan route:list --path=about && php artisan route:list --path=athletes && php artisan route:list --path=organizers && php artisan route:list --path=how-it-works && php artisan route:list --path=biometric-notice`
Expected: each route listed once. New public routes are above the `{any}` catch-all in `routes/frontend.php`.

- [ ] **Step 3: Browser check at phone and desktop width**

Run `php artisan serve` and open `/`, `/how-it-works`, `/faq`, `/about`, `/organizers`, `/biometric-notice`, `/photographers`, `/list-events` at 375px and 1280px width. Check:
- No horizontal scroll at 375px.
- Hamburger opens and closes the menu; "Find My Photos" stays visible.
- Header stays at the top while scrolling.
- Headings and body use Alata.
- Old pages (`/photographers`, `/list-events`, an event page, `/cart`) still render and their template JS (owl sliders, nice-select) still works.

Report any visual issue with the page and width. If no browser is available, say so in the report; do not claim it was checked.

- [ ] **Step 4: Git hygiene**

Run: `git status && git diff main --stat`
Expected: only files listed in the File Map. `.vscode/settings.json` and the spec PDF were already modified/untracked before this plan; do not commit them.

---

## Out of scope (later plans)

- `/athletes` page and search card, consent checkbox text "I agree to the use of my selfie to find my photos, as described in the Biometric Notice." (Plan 2)
- `/photographers` restyle, Organizers form, Contact restyle, `leads` table, form emails (Plan 3)
- Cookie banner, GA4, Meta Pixel, click events, Open Graph, WebP, photos, favicon from the logo (Plan 4)
- `www` redirect and HTTPS (server/infrastructure, not app code)
