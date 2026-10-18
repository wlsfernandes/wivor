# WiVor SEO Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the public WiVor pages easy for Google and AI search to understand: one brand name, a canonical address and share preview on every page, a complete sitemap, structured data for the business, the FAQ and events, and guidance that keeps athlete photos out of event covers.

**Architecture:** All SEO tags come from the existing public layout `resources/views/frontend/v1/layouts/app.blade.php`. The layout gets sensible defaults (canonical address, Open Graph with a default image); pages override them with `@section`. Structured data stays in the existing `@yield('structured-data')` slot, built as PHP arrays and printed with `json_encode` using the same flags the photo page already uses. No new packages.

**Tech Stack:** Laravel 13, PHP 8.3, Blade, PHPUnit 12 (`php artisan test`), ImageMagick `convert` (installed) for the default share image.

**Spec:** `docs/WiVor — Especificação do Site.pdf` section 13 ("Técnico e SEO") and the SEO decisions Junior made on 2026-10-18 (recorded below). Open the PDF with `ls docs/WiVor*.pdf` (the file name uses decomposed Unicode).

## Decisions already made (Junior, 2026-10-18)

- Athlete photos must never appear in Google or AI search. Done in commit `ce6fb18` (photo pages and galleries `noindex`, `X-Robots-Tag` on preview images, no photo sitemap, `robots.txt`). This plan must not undo any of it.
- Crawlers, including AI search crawlers, are welcome on public pages.
- Brand name in search results: **WiVor** (the spec's spelling).
- Event cover images stay indexable. Photographers are told covers must not show a recognizable athlete (Task 6).

## Global Constraints

- Brand: `WiVor` in titles, `og:site_name` and structured data. Legal and policy body text (privacy, terms, refund, photographer terms, biometric notice) is **not** renamed; that wording belongs to Victor and his lawyer.
- Domain: `https://www.wivorphotos.com` (spec section 13: HTTPS, redirect non-www to www).
- Never index athlete photos: do not add photo pages, gallery pages or `/events/*/photos/*/image.jpg` to any sitemap, structured data, or `og:image` default.
- Face-recognition copy appears only when `config('face_recognition.enabled')` is true. This also applies to structured data.
- No new Composer or npm dependencies.
- Hardcoded English Blade, matching the existing public pages.
- In tests, strings with `'` need `assertSee($text, false)`.
- Baseline before this plan: `php artisan test` → 219 passed, 3 failed (`FrontendTruthAlignmentTest` legacy video, `PhotoSeoTest` keywords meta, `WebsitePolicyTest` admin menu). Task 1 fixes the `PhotoSeoTest` one; the other two stay out of scope.

## Review Focus

1. **Tracking parameters from Instagram and Facebook** (`/?utm_source=ig`, `/faq?fbclid=…`): the canonical address must drop the query string, or every shared link becomes a duplicate page. Task 2 tests it.
2. **Face search off (production default):** the FAQPage structured data must not contain the face-data question or the word "selfie". Task 4 tests it.
3. **Event without a date:** the SportsEvent structured data must be left out, not printed with an empty `startDate` (Google rejects it). Task 5 tests it.
4. **Event title with quotes or `</script>`:** structured data must stay valid JSON and must not break out of the `<script>` tag. Task 5 tests it.
5. **Pages that set their own preview image** (event pages use the event cover): the default share image must not replace it. Task 2 tests it.

---

## Deploy note (not code, for Junior)

The local `.env` has `APP_URL=https://wivorphotos.com` (no `www`). Canonical addresses are built from `APP_URL`, so production must use `APP_URL=https://www.wivorphotos.com`, and the web server must redirect `wivorphotos.com` to `www.wivorphotos.com`. The repo has no web server config (`infrastructure/` only holds S3 files), so the redirect is a server task.

## File Map

| File | Action | Task |
|---|---|---|
| `config/seo.php` | Modify: default brand `WiVor` | 1 |
| `resources/views/frontend/v1/layouts/app.blade.php` | Modify: brand in `og:site_name`, drop keywords; default canonical and Open Graph | 1, 2 |
| `app/Http/Controllers/EventController.php` | Modify: brand in titles; SportsEvent data | 1, 5 |
| 16 public views with `| WivorPhotos` titles | Modify: title suffix | 1 |
| 11 views with `@section('meta-keywords'` | Modify: delete the section | 1 |
| `public/assets/images/site/og-default.jpg` | Create: 1200×630 share image | 2 |
| `resources/views/sitemaps/events.blade.php` | Modify: add marketing pages | 3 |
| `resources/views/frontend/v1/home/index.blade.php` | Modify: Organization and WebSite data | 4 |
| `resources/views/frontend/v1/pages/faq.blade.php` | Modify: items array, FAQPage data | 4 |
| `resources/views/frontend/v1/events/show.blade.php` | Modify: print SportsEvent data | 5 |
| `resources/views/events/_form.blade.php` | Modify: cover guidance | 6 |
| `tests/Feature/SeoBrandTest.php` | Create | 1 |
| `tests/Feature/SeoCanonicalAndShareTest.php` | Create | 2 |
| `tests/Feature/SitemapTest.php` | Create | 3 |
| `tests/Feature/StructuredDataTest.php` | Create | 4, 5 |
| `tests/Feature/EventMvpTest.php` | Modify: cover guidance test | 6 |

---

### Task 1: One brand name and no keywords meta

**Files:**
- Modify: `config/seo.php:4`
- Modify: `resources/views/frontend/v1/layouts/app.blade.php:14-16, 26`
- Modify: `app/Http/Controllers/EventController.php:132, 179`
- Modify: titles in the views listed in Step 4
- Modify: keyword sections in the views listed in Step 5
- Test: `tests/Feature/SeoBrandTest.php`

**Interfaces:**
- Produces: `config('seo.brand')` returns `'WiVor'` by default. Later tasks use it for `og:site_name` and structured data `name`.

- [ ] **Step 1: Write the failing test**

`tests/Feature/SeoBrandTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoBrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_use_the_wivor_brand_in_titles_and_share_metadata(): void
    {
        foreach (['events.listEvents', 'photographers', 'contact_us', 'privacy', 'terms', 'login'] as $routeName) {
            $response = $this->get(route($routeName))->assertOk();
            $title = $this->titleOf($response->getContent());

            $this->assertStringContainsString('WiVor', $title, "Title of {$routeName}");
            $this->assertStringNotContainsString('WivorPhotos', $title, "Title of {$routeName}");
        }
    }

    public function test_photographers_page_uses_the_specified_seo_title(): void
    {
        $this->get(route('photographers'))
            ->assertOk()
            ->assertSee('<title>Sell Event Photos Online | WiVor for Photographers</title>', false);
    }

    public function test_event_page_title_uses_the_wivor_brand(): void
    {
        $event = $this->publishedEvent('Peachtree Road Race');

        $this->get(route('events.show', ['event' => $event->slug]))
            ->assertOk()
            ->assertSee('<title>Peachtree Road Race Photos | WiVor</title>', false);
    }

    public function test_public_pages_no_longer_print_a_keywords_meta_tag(): void
    {
        $event = $this->publishedEvent('Keyword Free Race');

        $this->get(route('events.show', ['event' => $event->slug]))->assertOk()->assertDontSee('meta name="keywords"', false);
        $this->get(route('events.listEvents'))->assertOk()->assertDontSee('meta name="keywords"', false);
    }

    public function test_share_metadata_names_the_site_wivor(): void
    {
        $event = $this->publishedEvent('Share Name Race');

        $this->get(route('events.show', ['event' => $event->slug]))
            ->assertOk()
            ->assertSee('<meta property="og:site_name" content="WiVor">', false);
    }

    private function titleOf(string $html): string
    {
        preg_match('/<title>(.*?)<\/title>/s', $html, $matches);

        return trim($matches[1] ?? '');
    }

    private function publishedEvent(string $title): Event
    {
        return Event::create([
            'title' => $title,
            'slug' => Event::generateUniqueSlug($title),
            'sport' => 'Running',
            'published' => true,
            'status' => Event::STATUS_PUBLISHED,
            'published_at' => now(),
            'date_of_event' => '2026-10-10',
            'timezone' => 'America/New_York',
            'city' => 'Atlanta',
            'state' => 'GA',
            'country_code' => 'US',
            'content' => 'A community race.',
        ]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SeoBrandTest`
Expected: FAIL. Titles contain `WivorPhotos`; event title is `... | WivorPhotos`; keywords meta present; `og:site_name` is `WivorPhotos`.

- [ ] **Step 3: Change the brand default, layout and event titles**

`config/seo.php` line 4:

```php
    'brand' => env('SEO_BRAND', 'WiVor'),
```

Check that production `.env` does not set `SEO_BRAND=WivorPhotos`: `grep -n SEO_BRAND .env` (prints nothing if unset; never print the whole `.env`).

In `resources/views/frontend/v1/layouts/app.blade.php`, delete these lines (14-16):

```blade
    @hasSection('meta-keywords')
        <meta name="keywords" content="@yield('meta-keywords')">
    @endif
```

and replace

```blade
        <meta property="og:site_name" content="WivorPhotos">
```

with

```blade
        <meta property="og:site_name" content="{{ config('seo.brand') }}">
```

In `app/Http/Controllers/EventController.php`, replace both occurrences (lines 132 and 179) of

```php
            'seoTitle' => "{$event->title} Photos | WivorPhotos",
```

with

```php
            'seoTitle' => "{$event->title} Photos | ".config('seo.brand'),
```

- [ ] **Step 4: Change page titles**

Run from the repo root:

```bash
grep -rl "@section('title', .*| WivorPhotos')" resources/views/frontend resources/views/auth/login.blade.php \
  | xargs sed -i "s/| WivorPhotos')/| WiVor')/"
sed -i "s/@section('title', \$title . ' | WivorPhotos')/@section('title', \$title . ' | WiVor')/" resources/views/frontend/v1/policies/show.blade.php
sed -i "s/@section('title', 'Contact WivorPhotos')/@section('title', 'Contact | WiVor')/" resources/views/frontend/v1/pages/contact.blade.php
```

Then set the spec's SEO title and description on `/photographers` (spec section 7). In `resources/views/frontend/v1/photographers/index.blade.php` replace lines 3 and 5:

```blade
@section('title', 'Sell Event Photos Online | WiVor for Photographers')
@section('meta-description', 'Upload your event photos for free. WiVor matches athletes automatically and handles payments.')
```

Check: `grep -rn "@section('title'" resources/views/frontend resources/views/auth/login.blade.php | grep WivorPhotos` prints nothing.

Do **not** change the visible `<h1>Contact WivorPhotos</h1>` or any policy body text; `PublicPolicyPagesTest` and `PhotographerApprovalTest` pin them, and the contact page restyle is plan 3.

- [ ] **Step 5: Delete the now-unused keyword sections**

The layout no longer prints keywords, so every `@section('meta-keywords', ...)` is dead code created by Step 3. Delete each one with this script (it removes the statement up to its balanced closing parenthesis):

```bash
python3 - <<'EOF'
import pathlib, re
files = [
    'resources/views/auth/login.blade.php',
    'resources/views/frontend/v1/photos/show.blade.php',
    'resources/views/frontend/v1/events/index.blade.php',
    'resources/views/frontend/v1/events/gallery.blade.php',
    'resources/views/frontend/v1/events/show.blade.php',
    'resources/views/frontend/v1/pages/signup.blade.php',
    'resources/views/frontend/v1/pages/team/wilson-fernandes-junior.blade.php',
    'resources/views/frontend/v1/pages/team/carlos.blade.php',
    'resources/views/frontend/v1/pages/team/victor.blade.php',
    'resources/views/frontend/v1/pages/team/index.blade.php',
    'resources/views/frontend/v1/photographers/index.blade.php',
]
for name in files:
    path = pathlib.Path(name)
    text = path.read_text()
    start = text.index("@section('meta-keywords'")
    depth = 0
    for position in range(start + len('@section'), len(text)):
        if text[position] == '(':
            depth += 1
        elif text[position] == ')':
            depth -= 1
            if depth == 0:
                end = position + 1
                break
    if text[end:end + 1] == '\n':
        end += 1
    path.write_text(text[:start] + text[end:])
    print('removed keywords from', name)
EOF
grep -rn "meta-keywords" resources/views/frontend resources/views/auth
```

Expected: 11 "removed" lines, and the final `grep` prints nothing. `resources/views/portal/**` uses a different (logged-in, `noindex`) layout; leave it.

- [ ] **Step 6: Run tests**

Run: `php artisan test --filter='SeoBrandTest|PhotoSeoTest|PublicPolicyPagesTest|FrontendTruthAlignmentTest|EventMvpTest|HowToUploadPhotosTest'`
Expected: `SeoBrandTest` 5 PASS. `PhotoSeoTest` now fully PASSES (its keywords assertion was a baseline failure). Only `FrontendTruthAlignmentTest > authenticated legacy view` still fails (baseline).

- [ ] **Step 7: Commit**

```bash
git add config/seo.php resources/views app/Http/Controllers/EventController.php tests/Feature/SeoBrandTest.php
git commit -m "Use the WiVor brand in search titles and drop the keywords meta tag"
```

---

### Task 2: Canonical address and share preview on every public page

**Files:**
- Create: `public/assets/images/site/og-default.jpg`
- Modify: `resources/views/frontend/v1/layouts/app.blade.php` (head, lines 17-49 before Task 1's deletion)
- Test: `tests/Feature/SeoCanonicalAndShareTest.php`

**Interfaces:**
- Consumes: `config('seo.brand')` (Task 1).
- Produces: every page using the public layout prints `<link rel="canonical">` and the Open Graph and Twitter tags. Pages can still override with `@section('canonical', ...)` and `@section('og-image', ...)`.

- [ ] **Step 1: Write the failing test**

`tests/Feature/SeoCanonicalAndShareTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoCanonicalAndShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_pages_have_a_canonical_address_built_from_the_app_url(): void
    {
        $baseUrl = rtrim(config('app.url'), '/');

        $this->get(route('welcome'))->assertOk()->assertSee('<link rel="canonical" href="'.$baseUrl.'/">', false);
        $this->get(route('faq'))->assertOk()->assertSee('<link rel="canonical" href="'.$baseUrl.'/faq">', false);
    }

    public function test_canonical_address_ignores_tracking_parameters_from_social_links(): void
    {
        $baseUrl = rtrim(config('app.url'), '/');

        $this->get('/faq?utm_source=instagram&fbclid=abc123')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.$baseUrl.'/faq">', false)
            ->assertDontSee('utm_source=instagram"', false);
    }

    public function test_marketing_pages_have_a_share_preview_with_the_default_image(): void
    {
        $this->get(route('how-it-works'))
            ->assertOk()
            ->assertSee('<meta property="og:title" content="How WiVor Works | Photo Matching for Athletes">', false)
            ->assertSee('<meta property="og:image" content="'.asset('assets/images/site/og-default.jpg').'">', false)
            ->assertSee('<meta property="og:site_name" content="WiVor">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
    }

    public function test_event_page_keeps_its_own_cover_as_share_image(): void
    {
        $event = Event::create([
            'title' => 'Cover Share Race',
            'slug' => Event::generateUniqueSlug('Cover Share Race'),
            'sport' => 'Running',
            'published' => true,
            'status' => Event::STATUS_PUBLISHED,
            'published_at' => now(),
            'date_of_event' => '2026-10-10',
            'timezone' => 'America/New_York',
            'city' => 'Atlanta',
            'state' => 'GA',
            'country_code' => 'US',
            'content' => 'A community race.',
        ]);

        $this->get(route('events.show', ['event' => $event->slug]))
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.$event->cover_url.'">', false)
            ->assertDontSee('og-default.jpg', false)
            ->assertSee('<link rel="canonical" href="'.route('events.show', ['event' => $event->slug]).'">', false);
    }

    public function test_default_share_image_exists_with_the_recommended_size(): void
    {
        $path = public_path('assets/images/site/og-default.jpg');

        $this->assertFileExists($path);
        [$width, $height] = getimagesize($path);
        $this->assertSame([1200, 630], [$width, $height]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SeoCanonicalAndShareTest`
Expected: FAIL (no canonical on `/` and `/faq`, no `og:image` on How It Works, missing `og-default.jpg`). `test_event_page_keeps_its_own_cover_as_share_image` may already pass; it guards against regressions.

- [ ] **Step 3: Create the default share image**

The image reuses the licensed hero photo (no logo on the photo, spec logo rule). Use the original from `docs/img/`:

```bash
convert "docs/img/Untitled design (1).jpg" -gravity East -chop 8x0 -resize 1200x630^ -gravity center -extent 1200x630 -quality 82 public/assets/images/site/og-default.jpg
identify public/assets/images/site/og-default.jpg
```

Expected: `og-default.jpg JPEG 1200x630 ...`. (JPEG, not WebP: Facebook and some messaging apps still handle JPEG previews more reliably.)

- [ ] **Step 4: Make the layout print defaults**

In `resources/views/frontend/v1/layouts/app.blade.php`, replace the block that starts with `@hasSection('canonical')` and ends with the `@endif` after the Twitter tags (the whole canonical + Open Graph block) with:

```blade
    @php($defaultCanonicalUrl = rtrim(config('app.url'), '/').'/'.ltrim(request()->path() === '/' ? '' : request()->path(), '/'))
    <link rel="canonical" href="@yield('canonical', $defaultCanonicalUrl)">
    <meta property="og:type" content="@yield('og-type', 'website')">
    <meta property="og:site_name" content="{{ config('seo.brand') }}">
    <meta property="og:title" content="@yield('title')">
    <meta property="og:description" content="@yield('meta-description')">
    <meta property="og:url" content="@yield('canonical', $defaultCanonicalUrl)">
    <meta property="og:image" content="@yield('og-image', asset('assets/images/site/og-default.jpg'))">
    @if (str_starts_with(trim($__env->yieldContent('og-image', asset('assets/images/site/og-default.jpg'))), 'https://'))
        <meta property="og:image:secure_url" content="@yield('og-image', asset('assets/images/site/og-default.jpg'))">
    @endif
    @hasSection('og-image-type')
        <meta property="og:image:type" content="@yield('og-image-type')">
    @endif
    @hasSection('og-image-width')
        <meta property="og:image:width" content="@yield('og-image-width')">
    @endif
    @hasSection('og-image-height')
        <meta property="og:image:height" content="@yield('og-image-height')">
    @endif
    <meta property="og:image:alt" content="@yield('og-image-alt', trim($__env->yieldContent('title')))">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title')">
    <meta name="twitter:description" content="@yield('meta-description')">
    <meta name="twitter:image" content="@yield('og-image', asset('assets/images/site/og-default.jpg'))">
    <meta name="twitter:image:alt" content="@yield('og-image-alt', trim($__env->yieldContent('title')))">
```

`request()->path()` never includes the query string, which handles Review Focus 1.

- [ ] **Step 5: Run tests**

Run: `php artisan test --filter='SeoCanonicalAndShareTest|SeoBrandTest|PhotoSeoTest|EventBibSearchTest|SearchIndexingTest'`
Expected: all PASS.

- [ ] **Step 6: Commit**

```bash
git add resources/views/frontend/v1/layouts/app.blade.php public/assets/images/site/og-default.jpg tests/Feature/SeoCanonicalAndShareTest.php
git commit -m "Give every public page a canonical address and a share preview"
```

---

### Task 3: Sitemap lists the marketing pages

**Files:**
- Modify: `resources/views/sitemaps/events.blade.php:3-10`
- Test: `tests/Feature/SitemapTest.php`

**Interfaces:**
- Consumes: route names `welcome`, `events.listEvents`, `how-it-works`, `photographers`, `organizers`, `faq`, `about`, `contact_us`.

`/athletes` is a temporary redirect until plan 2, so it is not listed yet. Plan 2 adds it.

- [ ] **Step 1: Write the failing test**

`tests/Feature/SitemapTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_sitemap_page_lists_every_marketing_page(): void
    {
        $response = $this->get(route('sitemap.events', ['page' => 1]))->assertOk();

        foreach (['welcome', 'events.listEvents', 'how-it-works', 'photographers', 'organizers', 'faq', 'about', 'contact_us'] as $routeName) {
            $response->assertSee('<loc>'.route($routeName).'</loc>', false);
        }
    }

    public function test_sitemap_does_not_list_redirects_or_private_pages(): void
    {
        $this->get(route('sitemap.events', ['page' => 1]))
            ->assertOk()
            ->assertDontSee('<loc>'.route('athletes').'</loc>', false)
            ->assertDontSee('<loc>'.route('cart.show').'</loc>', false)
            ->assertDontSee('<loc>'.route('login').'</loc>', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SitemapTest`
Expected: first test FAILS (`how-it-works` missing). Second passes and guards the change.

- [ ] **Step 3: Add the pages**

In `resources/views/sitemaps/events.blade.php` replace:

```blade
    @if ($includeSitePages)
        <url>
            <loc>{{ route('welcome') }}</loc>
        </url>
        <url>
            <loc>{{ route('events.listEvents') }}</loc>
        </url>
    @endif
```

with:

```blade
    @if ($includeSitePages)
        @foreach (['welcome', 'events.listEvents', 'how-it-works', 'photographers', 'organizers', 'faq', 'about', 'contact_us'] as $sitePageRoute)
            <url>
                <loc>{{ route($sitePageRoute) }}</loc>
            </url>
        @endforeach
    @endif
```

- [ ] **Step 4: Run tests**

Run: `php artisan test --filter='SitemapTest|PhotoSeoTest'`
Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/views/sitemaps/events.blade.php tests/Feature/SitemapTest.php
git commit -m "List the marketing pages in the sitemap"
```

---

### Task 4: Organization and FAQ structured data

**Files:**
- Modify: `resources/views/frontend/v1/home/index.blade.php` (add `@section('structured-data')`)
- Modify: `resources/views/frontend/v1/pages/faq.blade.php` (items array + `@section('structured-data')`)
- Test: `tests/Feature/StructuredDataTest.php` (create)

**Interfaces:**
- Consumes: `config('seo.brand')` (Task 1).
- Produces: `StructuredDataTest::structuredDataOf(string $html): array` helper (private, in the test class); Task 5 adds tests to the same class and uses it.

- [ ] **Step 1: Write the failing test**

`tests/Feature/StructuredDataTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class StructuredDataTest extends TestCase
{
    public function test_home_page_describes_the_wivor_organization_and_website(): void
    {
        $data = $this->structuredDataOf($this->get(route('welcome'))->assertOk()->getContent());
        $types = array_column($data, '@type');

        $this->assertContains('Organization', $types);
        $this->assertContains('WebSite', $types);

        $organization = $data[array_search('Organization', $types, true)];
        $this->assertSame('WiVor', $organization['name']);
        $this->assertSame(asset('assets/images/logo/wivor.png'), $organization['logo']);
        $this->assertContains('https://www.instagram.com/wivor.photos/', $organization['sameAs']);
    }

    public function test_faq_page_marks_up_every_visible_question_with_face_search_enabled(): void
    {
        config(['face_recognition.enabled' => true]);

        $data = $this->structuredDataOf($this->get(route('faq'))->assertOk()->getContent());
        $faq = $data[0];

        $this->assertSame('FAQPage', $faq['@type']);
        $this->assertCount(8, $faq['mainEntity']);
        $this->assertSame('Is WiVor really free?', $faq['mainEntity'][0]['name']);
        $this->assertSame('Question', $faq['mainEntity'][0]['@type']);
        $this->assertStringContainsString('Athletes only pay', $faq['mainEntity'][0]['acceptedAnswer']['text']);
    }

    public function test_faq_structured_data_leaves_out_face_search_when_it_is_disabled(): void
    {
        config(['face_recognition.enabled' => false]);

        $html = $this->get(route('faq'))->assertOk()->getContent();
        $faq = $this->structuredDataOf($html)[0];

        $this->assertCount(7, $faq['mainEntity']);
        $this->assertStringNotContainsString('selfie', json_encode($faq));
        $this->assertStringNotContainsString('face data', json_encode($faq));
    }

    /** @return list<array<string, mixed>> */
    private function structuredDataOf(string $html): array
    {
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

        $items = [];
        foreach ($matches[1] as $json) {
            $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
            foreach (isset($decoded['@type']) ? [$decoded] : $decoded as $item) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=StructuredDataTest`
Expected: FAIL (no `application/ld+json` on Home or FAQ; `$data[0]` undefined).

- [ ] **Step 3: Add Organization and WebSite data to Home**

In `resources/views/frontend/v1/home/index.blade.php`, after the `@section('meta-description', $metaDescription)` line add:

```blade
@section('structured-data')
    @php($organizationStructuredData = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('seo.brand'),
            'url' => rtrim(config('app.url'), '/').'/',
            'logo' => asset('assets/images/logo/wivor.png'),
            'email' => config('contact.email'),
            'sameAs' => [
                'https://www.instagram.com/wivor.photos/',
                'https://www.facebook.com/p/WiVor-Photos-61573081696201/',
            ],
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('seo.brand'),
            'url' => rtrim(config('app.url'), '/').'/',
        ],
    ])
    <script type="application/ld+json">{!! json_encode($organizationStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection
```

The `JSON_HEX_*` flags are the same ones `photos/show.blade.php` uses; they stop any value from closing the `<script>` tag.

- [ ] **Step 4: Turn the FAQ into one list used for both the page and the data**

Replace all of `resources/views/frontend/v1/pages/faq.blade.php` with the version below. The visible questions, answers, order and face-search gating are unchanged from today's page; only the structure changes.

```blade
@extends('frontend.v1.layouts.app')

@php($isFaceSearchEnabled = (bool) config('face_recognition.enabled'))
@php($supportEmail = e(config('contact.email')))
@php($faqItems = array_values(array_filter([
    ['question' => 'Is WiVor really free?', 'answerHtml' => "Yes. It's free for photographers, event organizers and athletes. Athletes only pay if they like their photos and choose to buy them."],
    ['question' => 'How does WiVor find my photos?', 'answerHtml' => $isFaceSearchEnabled
        ? 'We use bib number recognition and face recognition. Enter your bib number, upload a selfie, or both.'
        : 'We use bib number recognition. Enter your bib number.'],
    ['question' => 'Do I have to buy photos?', 'answerHtml' => 'No. You can browse your photos for free and buy only the ones you want.'],
    ['question' => 'How do I receive my photos?', 'answerHtml' => 'After purchase, you can download high-resolution files right away.'],
    ['question' => "What if I can't find my photos?", 'answerHtml' => 'Photographers may still be uploading. Check back in a day or two, or contact us at <a class="wv-link" href="mailto:'.$supportEmail.'">'.$supportEmail.'</a>.'],
    $isFaceSearchEnabled
        ? ['question' => 'How is my face data used and protected?', 'answerHtml' => 'Your selfie is used only to match you with your event photos. Read our <a class="wv-link" href="'.e(route('biometric-notice')).'">Biometric Notice</a>.']
        : null,
    ['question' => 'How do photographers get paid?', 'answerHtml' => 'WiVor handles payments. When an athlete buys a photo, the photographer receives their share.'],
    ['question' => 'How can my event partner with WiVor?', 'answerHtml' => 'Fill out the form on our <a class="wv-link" href="'.e(route('organizers')).'">Organizers page</a> and we\'ll get in touch.'],
])))

@section('title', 'FAQ | WiVor')
@section('meta-description', 'Answers about finding, previewing and buying your event photos on WiVor, and how photographers and event organizers work with WiVor.')

@section('structured-data')
    @php($faqStructuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn (array $item): array => [
            '@type' => 'Question',
            'name' => $item['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answerHtml']],
        ], $faqItems),
    ])
    <script type="application/ld+json">{!! json_encode($faqStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection

@section('content')
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <h1 class="wv-h1">Frequently Asked Questions</h1>

            <div class="wv-faq">
                @foreach ($faqItems as $faqItem)
                    <details>
                        <summary>{!! $faqItem['question'] !!}</summary>
                        <div class="wv-body">{!! $faqItem['answerHtml'] !!}</div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endsection
```

Every dynamic value inside `answerHtml` (support email, routes) is passed through `e()`; questions and the rest of the answers are fixed text, so `{!! !!}` is safe here. The question uses `{!! !!}` too: `{{ }}` would print the apostrophe in "What if I can't find my photos?" as `&#039;` and break the existing `FaqPageTest` order check.

- [ ] **Step 5: Run tests**

Run: `php artisan test --filter='StructuredDataTest|FaqPageTest|OrganizersPageTest|HomePageTest|FrontendTruthAlignmentTest'`
Expected: `StructuredDataTest` 3 PASS; `FaqPageTest` (existing page copy and order tests) PASS unchanged; only the baseline legacy-video test fails.

- [ ] **Step 6: Commit**

```bash
git add resources/views/frontend/v1/home/index.blade.php resources/views/frontend/v1/pages/faq.blade.php tests/Feature/StructuredDataTest.php
git commit -m "Describe WiVor and its FAQ with structured data for search and AI answers"
```

---

### Task 5: SportsEvent structured data on event pages

**Files:**
- Modify: `app/Http/Controllers/EventController.php` (`show()` at lines 105-141, new private method)
- Modify: `resources/views/frontend/v1/events/show.blade.php` (after the `og-image` section)
- Test: `tests/Feature/StructuredDataTest.php` (add tests)

**Interfaces:**
- Consumes: `structuredDataOf()` helper from Task 4; `config('seo.brand')`.
- Produces: view variable `$eventStructuredData` (`array|null`) on `frontend.v1.events.show`.

- [ ] **Step 1: Write the failing tests**

Add to `tests/Feature/StructuredDataTest.php` (add `use App\Models\Event;` and `use Illuminate\Foundation\Testing\RefreshDatabase;` at the top and `use RefreshDatabase;` inside the class):

```php
    public function test_event_page_describes_the_race_as_a_sports_event(): void
    {
        $event = $this->publishedEvent(['title' => 'Peachtree Road Race', 'venue_name' => 'Lenox Square']);

        $data = $this->structuredDataOf($this->get(route('events.show', ['event' => $event->slug]))->assertOk()->getContent());
        $sportsEvent = $data[array_search('SportsEvent', array_column($data, '@type'), true)];

        $this->assertSame('Peachtree Road Race', $sportsEvent['name']);
        $this->assertSame('2026-07-04', $sportsEvent['startDate']);
        $this->assertSame('Lenox Square', $sportsEvent['location']['name']);
        $this->assertSame('Atlanta', $sportsEvent['location']['address']['addressLocality']);
        $this->assertSame('GA', $sportsEvent['location']['address']['addressRegion']);
        $this->assertSame(route('events.show', ['event' => $event->slug]), $sportsEvent['url']);
        $this->assertSame($event->cover_url, $sportsEvent['image']);
    }

    public function test_event_without_a_date_has_no_sports_event_data(): void
    {
        $event = $this->publishedEvent(['title' => 'Undated Race', 'date_of_event' => null]);

        $html = $this->get(route('events.show', ['event' => $event->slug]))->assertOk()->getContent();

        $this->assertNotContains('SportsEvent', array_column($this->structuredDataOf($html), '@type'));
    }

    public function test_event_title_with_quotes_and_script_tags_keeps_structured_data_valid(): void
    {
        $event = $this->publishedEvent(['title' => 'The "Big" </script><script>alert(1)</script> Run']);

        $html = $this->get(route('events.show', ['event' => $event->slug]))->assertOk()->getContent();
        $data = $this->structuredDataOf($html);
        $sportsEvent = $data[array_search('SportsEvent', array_column($data, '@type'), true)];

        $this->assertSame('The "Big" </script><script>alert(1)</script> Run', $sportsEvent['name']);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    private function publishedEvent(array $overrides): Event
    {
        return Event::create(array_merge([
            'slug' => Event::generateUniqueSlug($overrides['title']),
            'sport' => 'Running',
            'published' => true,
            'status' => Event::STATUS_PUBLISHED,
            'published_at' => now(),
            'date_of_event' => '2026-07-04',
            'timezone' => 'America/New_York',
            'city' => 'Atlanta',
            'state' => 'GA',
            'country_code' => 'US',
            'content' => 'A community race.',
        ], $overrides));
    }
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=StructuredDataTest`
Expected: the first and third new tests FAIL (no SportsEvent data; `array_search` returns `false`). `test_event_without_a_date_has_no_sports_event_data` passes and guards the change.

- [ ] **Step 3: Build the data in the controller**

In `EventController::show()`, after `$canonicalUrl = route('events.show', ['event' => $event->slug]);` add:

```php
        $seoDescription = "Find professional photos from {$event->title} in {$event->location_label}.";
```

and in the returned array replace

```php
            'seoDescription' => "Find professional photos from {$event->title} in {$event->location_label}.",
```

with

```php
            'seoDescription' => $seoDescription,
            'eventStructuredData' => $this->sportsEventStructuredData($event, $canonicalUrl, $seoDescription),
```

Add this private method to `EventController` (next to the other private helpers):

```php
    /**
     * Describe a published event for search engines, or null when it has no date (Google rejects events without one).
     *
     * @return array<string, mixed>|null
     */
    private function sportsEventStructuredData(Event $event, string $canonicalUrl, string $description): ?array
    {
        if ($event->date_of_event === null) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'SportsEvent',
            'name' => $event->title,
            'description' => $description,
            'startDate' => $event->starts_at?->toIso8601String() ?? $event->date_of_event->toDateString(),
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'url' => $canonicalUrl,
            'image' => $event->cover_url,
            'location' => [
                '@type' => 'Place',
                'name' => $event->venue_name ?: $event->location_label,
                'address' => array_filter([
                    '@type' => 'PostalAddress',
                    'addressLocality' => $event->city,
                    'addressRegion' => $event->state,
                    'addressCountry' => $event->country_code,
                ]),
            ],
        ];
    }
```

- [ ] **Step 4: Print it in the view**

In `resources/views/frontend/v1/events/show.blade.php`, after `@section('og-image', $event->cover_url)` add:

```blade
@if ($eventStructuredData)
    @section('structured-data')
        <script type="application/ld+json">{!! json_encode($eventStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endsection
@endif
```

- [ ] **Step 5: Run tests**

Run: `php artisan test --filter='StructuredDataTest|EventMvpTest|EventBibSearchTest|SeoBrandTest|SeoCanonicalAndShareTest'`
Expected: all PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/EventController.php resources/views/frontend/v1/events/show.blade.php tests/Feature/StructuredDataTest.php
git commit -m "Describe event pages as sports events for race-photo searches"
```

---

### Task 6: Cover image guidance for photographers

**Files:**
- Modify: `resources/views/events/_form.blade.php:136`
- Test: `tests/Feature/EventMvpTest.php` (add one test)

Event pages are indexed and their cover is the share and search image (decision 2026-10-18), so the form must tell photographers not to use a recognizable athlete.

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/EventMvpTest.php` (uses its existing `createPhotographerUser()` and `createEvent()` helpers):

```php
    public function test_event_form_tells_photographers_not_to_use_a_recognizable_athlete_as_cover(): void
    {
        [$owner, $ownerPhotographer] = $this->createPhotographerUser();
        $event = $this->createEvent();
        $event->photographers()->attach($ownerPhotographer);

        $this->actingAs($owner)
            ->get(route('events.edit', ['event' => $event->id]))
            ->assertOk()
            ->assertSee('Use a course, start line, banner or crowd photo. Do not use a photo where an athlete can be recognized: the cover can appear in Google search.');
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=test_event_form_tells_photographers_not_to_use_a_recognizable_athlete_as_cover`
Expected: FAIL (text not found).

- [ ] **Step 3: Add the guidance**

In `resources/views/events/_form.blade.php` replace:

```blade
                <div class="form-text">Optional JPEG, PNG, or WebP up to 10 MB.</div>
```

with:

```blade
                <div class="form-text">Optional JPEG, PNG, or WebP up to 10 MB. Use a course, start line, banner or crowd photo. Do not use a photo where an athlete can be recognized: the cover can appear in Google search.</div>
```

- [ ] **Step 4: Run tests**

Run: `php artisan test --filter=EventMvpTest`
Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/views/events/_form.blade.php tests/Feature/EventMvpTest.php
git commit -m "Tell photographers not to use a recognizable athlete as event cover"
```

---

### Task 7: Full verification

- [ ] **Step 1: Full suite**

Run: `php artisan test 2>&1 | tail -5`
Expected: 2 failed (baseline `FrontendTruthAlignmentTest` legacy video and `WebsitePolicyTest` admin menu); everything else passes. `PhotoSeoTest` now passes.

- [ ] **Step 2: Validate the structured data by hand**

Run `php artisan serve`, open `/`, `/faq` and one published event page, copy each `application/ld+json` block and paste it into https://validator.schema.org/ (pasting code there sends no site data). Expected: no errors. Report warnings (for example "missing organizer" on SportsEvent) without fixing them; they are optional fields.

- [ ] **Step 3: Check share previews**

With the site deployed, paste a page URL into https://developers.facebook.com/tools/debug/ and confirm the image, title and description. If the site is not deployed yet, say so in the report; do not claim it was checked.

- [ ] **Step 4: Git hygiene**

Run: `git status && git log --oneline -7`
Expected: 6 commits from this plan, no stray files.

---

## Out of scope

- Web server redirect from `wivorphotos.com` to `www.wivorphotos.com` and production `APP_URL` (deploy note above).
- Google Search Console setup and removing already-indexed photo URLs (do after deploy).
- Pricing and "where we operate" facts for AI answers (needs Victor's content).
- `/athletes` page in the sitemap (plan 2), GA4, Meta Pixel and click events (plan 4).
- Renaming "WivorPhotos" inside policy and legal text (Victor and lawyer).
