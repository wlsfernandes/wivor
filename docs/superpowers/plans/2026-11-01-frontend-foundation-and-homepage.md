# WiVor Frontend Foundation and Homepage Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the old WiVor brand layer with the approved design system and rebuild the homepage as the visual reference for every later public page.

**Architecture:** One token set lives in `public/assets/css/wivor-brand.css`. That file already reaches public pages and the portal through `head-assets.blade.php`, and it does not reach admin. Portal views use no `wv-*` classes, so the portal receives tokens and fonts only. No backend file changes.

**Tech Stack:** Laravel 13, Blade, Bootstrap 5.3, hand-written CSS in `public/assets/css` (not built by Mix), vanilla JS, PHPUnit.

**Spec:** `docs/frontend/00-frontend-guide.md`

**Scope of this plan:** Guide Phase 1 only. The guide makes the homepage the source of truth, so later pages must be planned from the finished reference, not from guesses.

| Follow-up plan | Guide phase | Pages |
| --- | --- | --- |
| 2 | Phase 2 | `/list-events`, shared event card and badges |
| 3 | Phase 3 | `/events/{slug}`, gallery, photo page, cart, checkout result pages |
| 4 | Phase 4 | `/photographers`, `/organizers` |
| 5 | Phase 5 | How It Works, FAQ, About, Contact, auth pages, legal layout |
| 6 | Phase 6 | Final regression and handoff |

## Agreed Decisions

- No mixed look: public pages and portal share one set of colors and fonts.
- Portal scope: portal gets the shared tokens and fonts, plus two color token edits in `portal/layouts/app-sidebar.blade.php`. No portal layout or markup redesign.
- Images: keep the five files in `public/assets/images/site/` as general brand imagery, never as a specific real event. Their license is **unverified**; report this in the phase summary.
- Fonts: Google Fonts, same loading method as today.
- Homepage live events: skipped. `HomeController::welcome` passes no event data, and guide section 8 item 7 says to keep the CTA and note the follow-up.
- Hero H1: `Your race. Your moment.`
- Tests that pin the old brand get rewritten to the new spec at equal or stronger strictness.

## Global Constraints

- Colors come only from the `--wivor-*` tokens in guide section 2.
- Headings use Manrope weights `500, 600, 700, 800`. Body and UI use Inter weights `400, 500, 600, 700`.
- Fallback for both: `system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif`.
- Radius: buttons and inputs `10px`, cards `14px`, major panels `16–20px`.
- Primary controls `48–52px` tall. Touch targets at least `44 x 44px`.
- Content width `1240px` maximum. Mobile sections `48–64px`, desktop sections `80–96px`.
- Transitions `120–240ms`. Honor `prefers-reduced-motion: reduce`.
- Visible focus ring about `3px` in `--wivor-focus`.
- No serif fonts, gradients, large shadows, fake data, or autoplay motion.
- Never claim "no fees" while sales carry a commission (`config/commission.php`).
- Preserve named routes, SEO metadata, structured data, and the `face_recognition.enabled` conditionals.
- No new dependencies, controllers, models, migrations, or admin changes.
- Do not read or print `.env`.

## Review Focus

1. Empty hero search submit: visitor lands on the full event list with status 200. Test in Task 4.
2. Hero search with markup in the text: the value is escaped when the list page renders it back. Test in Task 4.
3. Face search disabled: the word "selfie" appears nowhere on the homepage. Test in Task 4.
4. Flash error after a redirect to the homepage: the error stays visible in the new layout. Test in Task 3.
5. Cart with items: the header still announces the correct photo count. Test in Task 3.

---

### Task 1: Tokens, fonts and shared portal colors

**Files:**
- Modify: `public/assets/css/wivor-brand.css`
- Modify: `resources/views/frontend/v1/components/head-assets.blade.php:4-7`
- Modify: `resources/views/portal/layouts/app-sidebar.blade.php:21-22`
- Test: `tests/Feature/BrandAssetsTest.php`

**Interfaces:**
- Produces: tokens `--wivor-ink`, `--wivor-ink-soft`, `--wivor-text`, `--wivor-muted`, `--wivor-bg`, `--wivor-surface`, `--wivor-surface-subtle`, `--wivor-border`, `--wivor-accent`, `--wivor-accent-hover`, `--wivor-accent-subtle`, `--wivor-on-dark`, `--wivor-on-dark-muted`, `--wivor-success`, `--wivor-focus`, `--wivor-error`, `--wivor-font-heading`, `--wivor-font-body`, `--wivor-radius`, `--wivor-radius-card`, `--wivor-radius-panel`, `--wivor-content-width`, `--wivor-control-height`.

- [ ] **Step 1: Create the branch**

```bash
git switch -c feature/frontend-design-system
```

- [ ] **Step 2: Replace the first two tests in `tests/Feature/BrandAssetsTest.php`**

`test_public_pages_load_the_alata_font_and_brand_stylesheet` and `test_brand_stylesheet_defines_every_specified_color` pin the retired brand. Replace them with the following. Leave the keyboard focus test unchanged.

```php
    public function test_public_pages_load_manrope_inter_and_the_brand_stylesheet(): void
    {
        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('family=Manrope', false)
            ->assertSee('family=Inter', false)
            ->assertDontSee('family=Alata', false)
            ->assertSee('href="'.asset('assets/css/wivor-brand.css').'"', false);
    }

    public function test_brand_stylesheet_defines_every_specified_color_and_no_retired_color(): void
    {
        $stylesheet = file_get_contents(public_path('assets/css/wivor-brand.css'));

        $specifiedColors = [
            '#151b22', '#28323d', '#34404c', '#596675', '#f7f8f6', '#ffffff', '#f1f3f3', '#e1e6e7',
            '#c64526', '#a5381c', '#fff0ea', '#f7f8fa', '#dfe4e8', '#1d7755', '#1174b8', '#b42318',
        ];

        foreach ($specifiedColors as $color) {
            $this->assertStringContainsString($color, $stylesheet);
        }

        foreach (['#ff5722', '#ff7e55', '#303841', "'Alata'", '--wv-'] as $retiredValue) {
            $this->assertStringNotContainsString($retiredValue, $stylesheet);
        }
    }

    public function test_portal_shares_the_accent_and_admin_layout_does_not_load_the_public_brand(): void
    {
        $portalLayout = file_get_contents(resource_path('views/portal/layouts/app-sidebar.blade.php'));
        $adminHead = file_get_contents(resource_path('views/layouts/head.blade.php'));

        $this->assertStringContainsString('--photographer-accent: #c64526', $portalLayout);
        $this->assertStringNotContainsString('wivor-brand', $adminHead);
    }
```

- [ ] **Step 3: Run the tests to verify they fail**

Run: `php artisan test --filter=BrandAssetsTest`
Expected: FAIL on the three new tests.

- [ ] **Step 4: Check that nothing outside the stylesheet depends on old tokens or fonts**

Run:

```bash
grep -rn -E "\-\-wv-" resources/views public/assets/js
grep -n -E "DM Sans|Cabin|Poppins|Alata" public/assets/css/style.css public/assets/css/responsive.css public/assets/css/color.css resources/views/portal -r | head -20
grep -rn -E "ff6700|255, 103, 0" resources/views/portal
```

Expected: first command prints nothing. If the second prints rules, the global font rule in Step 6 overrides them for text elements; list any remaining in the phase report. If the third finds hardcoded accents other than the token, list them in the phase report and do not change them.

- [ ] **Step 5: Rename tokens through the stylesheet**

```bash
sed -i \
  -e 's/--wv-orange-hover/--wivor-accent-hover/g' \
  -e 's/--wv-orange/--wivor-accent/g' \
  -e 's/--wv-slate/--wivor-ink/g' \
  -e 's/--wv-light/--wivor-surface-subtle/g' \
  -e 's/--wv-white/--wivor-surface/g' \
  -e 's/--wv-border/--wivor-border/g' \
  -e 's/--wv-font-family/--wivor-font-body/g' \
  -e 's/--wv-radius/--wivor-radius/g' \
  -e 's/--wv-content-width/--wivor-content-width/g' \
  -e 's/--wv-button-height/--wivor-control-height/g' \
  public/assets/css/wivor-brand.css
```

- [ ] **Step 6: Replace lines 1–22 of `public/assets/css/wivor-brand.css`**

```css
/*
 * WiVor design system (docs/frontend/00-frontend-guide.md, Part A).
 * Loaded after the template CSS on public pages and the portal. Admin does not load it.
 * Component classes use the "wv-" prefix so they never collide with template classes.
 */
:root {
    --wivor-ink: #151b22;
    --wivor-ink-soft: #28323d;
    --wivor-text: #34404c;
    --wivor-muted: #596675;
    --wivor-bg: #f7f8f6;
    --wivor-surface: #ffffff;
    --wivor-surface-subtle: #f1f3f3;
    --wivor-border: #e1e6e7;
    --wivor-accent: #c64526;
    --wivor-accent-hover: #a5381c;
    --wivor-accent-subtle: #fff0ea;
    --wivor-on-dark: #f7f8fa;
    --wivor-on-dark-muted: #dfe4e8;
    --wivor-success: #1d7755;
    --wivor-focus: #1174b8;
    --wivor-error: #b42318;
    --wivor-font-heading: 'Manrope', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    --wivor-font-body: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    --wivor-radius: 10px;
    --wivor-radius-card: 14px;
    --wivor-radius-panel: 18px;
    --wivor-content-width: 1240px;
    --wivor-control-height: 48px;
}

/* Icon fonts use <i>/<span> with their own classes, so they are not listed here. */
body,
body :is(p, a, li, label, input, select, textarea, button, summary, td, th) {
    font-family: var(--wivor-font-body);
}

body :is(h1, h2, h3, h4, h5, h6) {
    font-family: var(--wivor-font-heading);
    letter-spacing: -0.025em;
}
```

- [ ] **Step 7: Replace retired literals**

In `public/assets/css/wivor-brand.css`:
- Replace both `#6b7280` values with `var(--wivor-muted)`.
- Replace `rgba(255, 87, 34, 0.6)` with `rgba(198, 69, 38, 0.6)`.
- Replace `rgba(255, 87, 34, 0)` with `rgba(198, 69, 38, 0)`.

- [ ] **Step 8: Replace lines 4–7 of `head-assets.blade.php`**

```blade
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
```

- [ ] **Step 9: Update the two portal tokens in `app-sidebar.blade.php`**

```css
            --photographer-accent: #c64526;
            --photographer-sidebar: #151b22;
```

- [ ] **Step 10: Run the tests to verify they pass**

Run: `php artisan test --filter=BrandAssetsTest`
Expected: PASS.

- [ ] **Step 11: Commit**

```bash
git add public/assets/css/wivor-brand.css resources/views/frontend/v1/components/head-assets.blade.php resources/views/portal/layouts/app-sidebar.blade.php tests/Feature/BrandAssetsTest.php
git commit -m "Replace WiVor brand tokens and fonts with the approved design system"
```

---

### Task 2: Shared layout, type, buttons, forms and badges

**Files:**
- Modify: `public/assets/css/wivor-brand.css`
- Test: `tests/Feature/BrandAssetsTest.php`

**Interfaces:**
- Consumes: tokens from Task 1.
- Produces: classes `.wv-section`, `.wv-section--dark`, `.wv-h1`, `.wv-h2`, `.wv-h3`, `.wv-lead`, `.wv-btn`, `.wv-btn--primary`, `.wv-btn--outline`, `.wv-card`, `.wv-link`, `.wv-label`, `.wv-input`, `.wv-help`, `.wv-badge`, `.wv-badge--available`, `.wv-badge--upcoming`, `.wv-badge--past`, `.wv-search`, `.wv-search__icon`, `.wv-search__field`.

- [ ] **Step 1: Add the failing test to `BrandAssetsTest`**

```php
    public function test_brand_stylesheet_provides_shared_form_badge_focus_and_motion_rules(): void
    {
        $stylesheet = file_get_contents(public_path('assets/css/wivor-brand.css'));

        foreach (['.wv-input', '.wv-label', '.wv-help', '.wv-search', '.wv-badge--available', '.wv-badge--upcoming', '.wv-badge--past'] as $selector) {
            $this->assertStringContainsString($selector, $stylesheet);
        }

        $this->assertStringContainsString('outline: 3px solid var(--wivor-focus)', $stylesheet);
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $stylesheet);
    }
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=test_brand_stylesheet_provides_shared_form_badge_focus_and_motion_rules`
Expected: FAIL on `.wv-input`.

- [ ] **Step 3: Replace the Layout and Typography blocks**

Replace everything from `/* Layout */` up to, but not including, `/* Buttons */`:

```css
/* Page base */
body {
    background: var(--wivor-bg);
    color: var(--wivor-text);
}

/* Layout */
.wv-container {
    width: 100%;
    max-width: var(--wivor-content-width);
    margin: 0 auto;
    padding: 0 16px;
}

.wv-section {
    padding: 56px 0;
}

.wv-section--white { background: var(--wivor-surface); color: var(--wivor-text); }
.wv-section--light { background: var(--wivor-surface-subtle); color: var(--wivor-text); }
.wv-section--dark { background: var(--wivor-ink); color: var(--wivor-on-dark-muted); }
.wv-section--orange { background: var(--wivor-accent); color: var(--wivor-surface); }

.wv-section--dark :is(h1, h2, h3) { color: var(--wivor-on-dark); }
.wv-section--dark :is(p, li) { color: var(--wivor-on-dark-muted); }

.wv-section--orange :is(h1, h2, h3, p, li) {
    color: var(--wivor-surface);
}

/* Typography (mobile first) */
.wv-h1 { font-size: 34px; font-weight: 700; line-height: 1.12; color: var(--wivor-ink); margin: 0 0 16px; }
.wv-h2 { font-size: 28px; font-weight: 700; line-height: 1.2; color: var(--wivor-ink); margin: 0 0 16px; }
.wv-h3 { font-size: 20px; font-weight: 600; line-height: 1.3; color: var(--wivor-ink); margin: 0 0 8px; }
.wv-body { font-size: 16px; line-height: 1.6; }
.wv-lead { font-size: 17px; line-height: 1.55; max-width: 62ch; }
.wv-small { font-size: 14px; line-height: 1.5; margin-top: 16px; }

@media (min-width: 640px) {
    .wv-container { padding: 0 24px; }
}

@media (min-width: 1024px) {
    .wv-section { padding: 88px 0; }
    .wv-container { padding: 0 32px; }
    .wv-h1 { font-size: 48px; }
    .wv-h2 { font-size: 38px; }
    .wv-h3 { font-size: 24px; }
    .wv-lead { font-size: 19px; }
}

```

- [ ] **Step 4: Replace the base button rule and hover rules**

Replace the `.wv-btn { ... }` rule:

```css
.wv-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: var(--wivor-control-height);
    padding: 12px 24px;
    border: 1px solid transparent;
    border-radius: var(--wivor-radius);
    font-size: 16px;
    font-weight: 600;
    line-height: 1.2;
    text-decoration: none;
    white-space: nowrap;
    transition: background-color 0.16s ease, color 0.16s ease, border-color 0.16s ease;
}
```

Replace the `.wv-btn--outline` rule and its hover rule (the sed in Task 1 produced the current values):

```css
.wv-btn--outline { background: var(--wivor-surface); border-color: var(--wivor-border); color: var(--wivor-ink); }
.wv-btn--outline:hover,
.wv-btn--outline:focus-visible { background: var(--wivor-surface-subtle); border-color: var(--wivor-ink); color: var(--wivor-ink); }
```

Replace the three `.wv-section--dark .wv-btn--outline` lines:

```css
.wv-section--dark .wv-btn--outline { background: transparent; border-color: var(--wivor-on-dark-muted); color: var(--wivor-on-dark); }
.wv-section--dark .wv-btn--outline:hover,
.wv-section--dark .wv-btn--outline:focus-visible { background: var(--wivor-on-dark); color: var(--wivor-ink); }
```

- [ ] **Step 5: Replace the card rule and add link underline**

Replace `.wv-card { ... }`:

```css
.wv-card {
    height: 100%;
    padding: 24px;
    background: var(--wivor-surface);
    border: 1px solid var(--wivor-border);
    border-radius: var(--wivor-radius-card);
    color: var(--wivor-text);
}
```

Replace the three `.wv-link` lines:

```css
.wv-link { color: var(--wivor-accent); font-weight: 600; text-decoration: none; }
.wv-link:hover,
.wv-link:focus-visible { color: var(--wivor-accent-hover); text-decoration: underline; }
.wv-section--dark .wv-link { color: var(--wivor-on-dark); text-decoration: underline; }
```

- [ ] **Step 6: Replace the focus block**

Replace the block under `/* Keyboard focus: the template CSS removes outlines globally. */`, keeping the existing four selectors so the existing test still passes:

```css
/* Keyboard focus: the template CSS removes outlines globally. */
.wv-header__nav a:focus-visible,
.wv-header__toggle:focus-visible,
.wv-footer a:focus-visible,
.wv-faq summary:focus-visible,
.wv-btn:focus-visible,
.wv-link:focus-visible,
.wv-input:focus-visible,
.wv-search__field:focus-visible {
    outline: 3px solid var(--wivor-focus);
    outline-offset: 2px;
}
```

In the rest of the file, replace `outline: 2px solid var(--wivor-accent-hover); outline-offset: 2px;` and `outline: 2px solid var(--wivor-accent); outline-offset: 2px;` with `outline: 3px solid var(--wivor-focus); outline-offset: 2px;`.

- [ ] **Step 7: Append form, search, badge and motion rules at the end of the file**

```css
/* Forms: visible labels, 48px controls, helper text below the field. */
.wv-label { display: block; margin-bottom: 8px; font-size: 15px; font-weight: 600; color: var(--wivor-ink); }

.wv-input {
    display: block;
    width: 100%;
    min-height: var(--wivor-control-height);
    padding: 12px 14px;
    background: var(--wivor-surface);
    border: 1px solid var(--wivor-muted);
    border-radius: var(--wivor-radius);
    font-size: 16px;
    color: var(--wivor-ink);
}

.wv-help { margin: 8px 0 0; font-size: 14px; line-height: 1.5; color: var(--wivor-muted); }

/* Search bar: one field and one action. */
.wv-search { display: flex; flex-direction: column; gap: 12px; }

.wv-search__field-wrap { position: relative; flex: 1; }

.wv-search__icon {
    position: absolute;
    top: 50%;
    left: 16px;
    transform: translateY(-50%);
    color: var(--wivor-muted);
    pointer-events: none;
}

.wv-search__field { min-height: 52px; padding-left: 44px; }

.wv-search .wv-btn { min-height: 52px; }

@media (min-width: 640px) {
    .wv-search { flex-direction: row; align-items: flex-end; }
}

.wv-section--dark .wv-label { color: var(--wivor-on-dark); }

/* Status badges always carry a text label. */
.wv-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.4;
}

.wv-badge--available { background: var(--wivor-success); color: var(--wivor-surface); }
.wv-badge--upcoming { background: var(--wivor-accent-subtle); color: var(--wivor-accent-hover); }
.wv-badge--past { background: var(--wivor-surface-subtle); color: var(--wivor-muted); }

@media (prefers-reduced-motion: reduce) {
    .wv-btn,
    .wv-input { transition: none; }
}
```

- [ ] **Step 8: Run the tests to verify they pass**

Run: `php artisan test --filter=BrandAssetsTest`
Expected: PASS.

- [ ] **Step 9: Commit**

```bash
git add public/assets/css/wivor-brand.css tests/Feature/BrandAssetsTest.php
git commit -m "Add shared layout, form, search and badge styles to the design system"
```

---

### Task 3: Header, footer and mobile menu

**Files:**
- Modify: `public/assets/css/wivor-brand.css`
- Modify: `public/assets/js/wivor-nav.js`
- Test: `tests/Feature/PublicHeaderFooterTest.php`

**Interfaces:**
- Consumes: tokens from Task 1, focus rules from Task 2.
- Produces: no new classes. Header and footer markup stays unchanged.

- [ ] **Step 1: Add the failing test and the review focus tests to `PublicHeaderFooterTest`**

```php
    public function test_header_announces_the_number_of_photos_in_the_cart(): void
    {
        $this->withSession(['wivor_cart' => [11, 12]])
            ->get(route('welcome'))
            ->assertOk()
            ->assertSee('View cart with 2 selected photos');
    }

    public function test_flash_error_stays_visible_on_the_homepage(): void
    {
        $this->withSession(['error' => 'Your session has expired.'])
            ->get(route('welcome'))
            ->assertOk()
            ->assertSee('Your session has expired.');
    }

    public function test_mobile_menu_script_closes_on_escape_and_returns_focus(): void
    {
        $script = file_get_contents(public_path('assets/js/wivor-nav.js'));

        $this->assertStringContainsString("'Escape'", $script);
        $this->assertStringContainsString('toggle.focus()', $script);
    }
```

- [ ] **Step 2: Run the tests to verify the script test fails**

Run: `php artisan test --filter=PublicHeaderFooterTest`
Expected: FAIL only on `test_mobile_menu_script_closes_on_escape_and_returns_focus`. The other two pin behavior that exists today.

- [ ] **Step 3: Replace `public/assets/js/wivor-nav.js`**

```js
// Opens and closes the public header navigation on small screens.
(function () {
    var toggle = document.querySelector('.wv-header__toggle');
    var navigation = document.getElementById('wv-primary-nav');

    if (!toggle || !navigation) {
        return;
    }

    function setMenuOpen(isOpen) {
        navigation.classList.toggle('is-open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
    }

    toggle.addEventListener('click', function () {
        setMenuOpen(!navigation.classList.contains('is-open'));
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && navigation.classList.contains('is-open')) {
            setMenuOpen(false);
            toggle.focus();
        }
    });
})();
```

- [ ] **Step 4: Update the header CSS**

Replace `.wv-header__bar`, `.wv-header__cta` and the nav link rules:

```css
.wv-header__bar {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 64px;
}

.wv-header__logo img { height: 32px; width: auto; }

.wv-header__cta { margin-left: auto; min-height: 44px; padding: 8px 14px; font-size: 15px; }

.wv-header__nav a {
    display: flex;
    align-items: center;
    min-height: var(--wivor-control-height);
    color: var(--wivor-ink);
    font-size: 16px;
    font-weight: 500;
    text-decoration: none;
}

.wv-header__nav a:hover,
.wv-header__nav a.is-current { color: var(--wivor-accent); text-decoration: underline; text-underline-offset: 6px; }
```

Change `.wv-header__toggle` `width` and `height` from `48px` to `44px`, and `padding` from `12px` to `10px`.

Replace the desktop block for the header, and change its breakpoint from `992px` to `1024px`:

```css
@media (min-width: 1024px) {
    .wv-header__bar { min-height: 76px; }

    .wv-header__logo img { height: 40px; }

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

    .wv-header__nav a { font-size: 15px; }

    .wv-header__utility { border-top: 0; margin-top: 0; gap: 16px; }

    .wv-header__cta { margin-left: 0; min-height: var(--wivor-control-height); padding: 12px 20px; }
}
```

- [ ] **Step 5: Update the footer CSS**

Replace these footer rules:

```css
.wv-footer {
    padding: 56px 0 24px;
    background: var(--wivor-ink);
    color: var(--wivor-on-dark-muted);
}

.wv-footer__title { font-size: 15px; font-weight: 600; color: var(--wivor-on-dark); margin: 0 0 12px; }

.wv-footer nav a,
.wv-footer__social a {
    display: inline-flex;
    align-items: center;
    min-height: 44px;
    color: var(--wivor-on-dark-muted);
    text-decoration: none;
}

.wv-footer nav a:hover,
.wv-footer__social a:hover { color: var(--wivor-on-dark); text-decoration: underline; }

.wv-footer__copyright {
    margin: 32px 0 0;
    padding-top: 16px;
    border-top: 1px solid var(--wivor-ink-soft);
    font-size: 14px;
    color: var(--wivor-on-dark-muted);
}

.wv-footer__credit { margin: 8px 0 0; font-size: 13px; color: var(--wivor-on-dark-muted); }

.wv-footer__credit a { color: var(--wivor-on-dark); text-decoration: underline; }

.wv-footer__credit a:hover { color: var(--wivor-surface); }
```

- [ ] **Step 6: Run the tests to verify they pass**

Run: `php artisan test --filter=PublicHeaderFooterTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add public/assets/css/wivor-brand.css public/assets/js/wivor-nav.js tests/Feature/PublicHeaderFooterTest.php
git commit -m "Restyle public header and footer and close the mobile menu with Escape"
```

---

### Task 4: Homepage reference

**Files:**
- Create: `resources/views/frontend/v1/components/event-search.blade.php`
- Modify: `resources/views/frontend/v1/home/index.blade.php:38-209`
- Modify: `public/assets/css/wivor-brand.css`
- Test: `tests/Feature/HomePageTest.php`, `tests/Feature/FrontendTruthAlignmentTest.php`

**Interfaces:**
- Consumes: `.wv-search`, `.wv-search__field-wrap`, `.wv-search__icon`, `.wv-search__field`, `.wv-input`, `.wv-label`, `.wv-btn`, `.wv-card`, `.wv-link`, `.wv-section`, route `events.listEvents`.
- Produces: Blade partial `frontend.v1.components.event-search`, parameter `string $fieldId`. Plan 2 reuses it on `/list-events`. New classes `.wv-hero`, `.wv-hero__grid`, `.wv-hero__media`, `.wv-split`, `.wv-steps`, `.wv-reassurance`.

- [ ] **Step 1: Invoke the frontend-design skill**

Guide section 0 requires it before page work. Run `/frontend-design` with the guide and this task as the brief. It may refine composition and CSS within Part A. It may not change tokens, copy rules, or backend behavior. Record in the report whether it ran.

- [ ] **Step 2: Rewrite the order test in `HomePageTest`**

Replace `test_home_page_shows_specified_sections_in_order_with_face_search_enabled`. The old test pins copy and sections that guide section 8 retires. The new tests keep the same order, title and link checks, and add search form checks. `test_home_page_shows_hero_and_audience_photos_with_descriptive_alt_text` and `test_home_page_hides_unverified_testimonials_and_partner_logos` stay unchanged.

```php
    public function test_home_page_shows_specified_sections_in_order_with_face_search_enabled(): void
    {
        config(['face_recognition.enabled' => true]);

        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('<title>WiVor | Find and Buy Your Race Photos</title>', false)
            ->assertSeeInOrder([
                'Your race. Your moment.',
                'Find your event, search by bib number or selfie, then preview and buy the photos you want.',
                'Search Events',
                'How WiVor works',
                'Find your event', 'Find yourself', 'Choose your photos',
                'For photographers', 'For event organizers',
                'Know what you are buying',
                'Preview first', 'Buy only what you want', 'Your privacy',
                'Ready to find your photos?',
                'Search by event name, bib number or selfie.',
            ])
            ->assertSee("I'm a Photographer", false)
            ->assertSee('href="'.route('athletes').'"', false)
            ->assertSee('href="'.route('how-it-works').'"', false)
            ->assertSee('href="'.route('organizers').'"', false)
            ->assertSee('href="'.route('photographers').'#register_section"', false)
            ->assertSee('href="'.route('biometric-notice').'"', false);
    }

    public function test_home_page_has_one_h1_and_a_working_event_search_form(): void
    {
        $response = $this->get(route('welcome'))->assertOk();

        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
        $response->assertSee('action="'.route('events.listEvents').'"', false)
            ->assertSee('method="GET"', false)
            ->assertSee('name="search"', false)
            ->assertSee('<label', false);
    }

    public function test_home_page_does_not_claim_photographers_use_wivor_for_free(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertDontSee('Free for photographers')
            ->assertDontSee('zero cost')
            ->assertSee('WiVor keeps a commission on each sale.');
    }

    public function test_empty_event_search_opens_the_full_event_list(): void
    {
        $this->get(route('events.listEvents', ['search' => '']))->assertOk();
    }

    public function test_event_search_escapes_markup_in_the_search_text(): void
    {
        $markup = '<script>alert(1)</script>';

        $this->get(route('events.listEvents', ['search' => $markup]))
            ->assertOk()
            ->assertDontSee($markup, false);
    }
```

- [ ] **Step 3: Update the bib-only homepage test in `FrontendTruthAlignmentTest`**

In `test_homepage_shows_bib_only_matching_without_face_or_video_marketing_when_face_search_is_disabled`, replace the three retired `assertSee` copy lines. Keep `Search by event name or bib number.` and all five `assertDontSee` lines.

```php
            ->assertSee('Find your event, search by bib number, then preview and buy the photos you want.')
            ->assertSee('Search this event by bib number, or browse every photo.')
            ->assertSee('Read how WiVor handles your data in our')
            ->assertSee('Search by event name or bib number.')
```

- [ ] **Step 4: Run the tests to verify they fail**

Run: `php artisan test --filter='HomePageTest|FrontendTruthAlignmentTest'`
Expected: FAIL on the new copy and form tests. `test_empty_event_search_opens_the_full_event_list` and `test_event_search_escapes_markup_in_the_search_text` pass because they pin existing behavior. The pre-existing failure `authenticated legacy view does not advertise video services` also still fails.

- [ ] **Step 5: Create `resources/views/frontend/v1/components/event-search.blade.php`**

```blade
{{-- Event search. Submits to the event directory with the same "search" parameter as its filter form. --}}
<form class="wv-search" method="GET" action="{{ route('events.listEvents') }}" role="search">
    <div class="wv-search__field-wrap">
        <label class="wv-label" for="{{ $fieldId }}">Event, city or sport</label>
        <div class="position-relative">
            <i class="fas fa-search wv-search__icon" aria-hidden="true"></i>
            <input class="wv-input wv-search__field" id="{{ $fieldId }}" name="search" type="search"
                placeholder="Try a race name or city" value="{{ $searchValue ?? '' }}">
        </div>
    </div>
    <button class="wv-btn wv-btn--primary wv-search__submit" type="submit">Search Events</button>
</form>
```

- [ ] **Step 6: Replace the content section of `home/index.blade.php`**

Replace from `@section('content')` through the final `@endsection`. Lines 1–37 stay unchanged, including SEO and structured data.

```blade
@section('content')
    {{-- Hero: dark text panel beside the race photo; copy and search come first on mobile. --}}
    <section class="wv-section wv-section--dark wv-hero">
        <div class="wv-container wv-hero__grid">
            <div>
                <h1 class="wv-h1 wv-hero__title">Your race. Your moment.</h1>
                @if ($isFaceSearchEnabled)
                    <p class="wv-lead">Find your event, search by bib number or selfie, then preview and buy the photos you want.</p>
                @else
                    <p class="wv-lead">Find your event, search by bib number, then preview and buy the photos you want.</p>
                @endif
                @include('frontend.v1.components.event-search', ['fieldId' => 'home-event-search'])
                <p class="wv-small"><a class="wv-link" href="{{ route('photographers') }}">I'm a Photographer →</a></p>
            </div>
            <div class="wv-hero__media">
                <img src="{{ asset('assets/images/site/hero-finish-line.webp') }}"
                    alt="Runner with a race bib approaching cheering spectators on a seaside promenade" width="1200" height="494"
                    fetchpriority="high">
            </div>
        </div>
    </section>

    {{-- How it works: three steps beside an athlete photo. --}}
    <section class="wv-section wv-section--white">
        <div class="wv-container wv-split">
            <div>
                <h2 class="wv-h2">How WiVor works</h2>
                <ol class="wv-steps">
                    <li>
                        <h3 class="wv-h3">Find your event</h3>
                        <p class="wv-body">Search by event name, city or sport.</p>
                    </li>
                    <li>
                        <h3 class="wv-h3">Find yourself</h3>
                        @if ($isFaceSearchEnabled)
                            <p class="wv-body">Search this event by bib number or selfie, or browse every photo.</p>
                        @else
                            <p class="wv-body">Search this event by bib number, or browse every photo.</p>
                        @endif
                    </li>
                    <li>
                        <h3 class="wv-h3">Choose your photos</h3>
                        <p class="wv-body">Preview them, buy the ones you want and download them in full quality.</p>
                    </li>
                </ol>
                <a class="wv-link" href="{{ route('how-it-works') }}">See the full process →</a>
            </div>
            <img class="wv-split__image" src="{{ asset('assets/images/site/athletes-bib-runners.webp') }}"
                alt="Two runners with race bibs celebrating on the course" width="800" height="800" loading="lazy">
        </div>
    </section>

    {{-- Photographers and organizers. --}}
    <section class="wv-section wv-section--light">
        <div class="wv-container">
            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <div class="wv-card">
                        <img class="wv-card__image" src="{{ asset('assets/images/site/photographers-telephoto-lens.webp') }}"
                            alt="Photographer with a telephoto lens shooting an event crowd" width="900" height="452"
                            loading="lazy">
                        <h2 class="wv-h3">For photographers</h2>
                        <p class="wv-body">Upload your event photos and sell them to athletes. There is no sign-up fee. WiVor keeps a commission on each sale.</p>
                        <a class="wv-link" href="{{ route('photographers') }}#register_section">Apply as a Photographer →</a>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="wv-card">
                        <img class="wv-card__image" src="{{ asset('assets/images/site/organizers-race-crowd.webp') }}"
                            alt="Crowd of runners on the road at the start of a race" width="900" height="372"
                            loading="lazy">
                        <h2 class="wv-h3">For event organizers</h2>
                        <p class="wv-body">Give your athletes professional photos of their race. WiVor works with photographers to cover your event.</p>
                        <a class="wv-link" href="{{ route('organizers') }}">Become a Partner →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Practical reassurance. --}}
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <h2 class="wv-h2">Know what you are buying</h2>
            <div class="wv-reassurance">
                <div>
                    <h3 class="wv-h3">Preview first</h3>
                    <p class="wv-body">See your photos before you pay.</p>
                </div>
                <div>
                    <h3 class="wv-h3">Buy only what you want</h3>
                    <p class="wv-body">You pay only for the photos you choose.</p>
                </div>
                <div>
                    <h3 class="wv-h3">Your privacy</h3>
                    @if ($isFaceSearchEnabled)
                        <p class="wv-body">Selfie search is optional and needs your consent. Read the <a class="wv-link" href="{{ route('biometric-notice') }}">Biometric Notice</a>.</p>
                    @else
                        <p class="wv-body">Read how WiVor handles your data in our <a class="wv-link" href="{{ route('privacy') }}">Privacy Policy</a>.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Closing action. --}}
    <section class="wv-section wv-section--dark">
        <div class="wv-container">
            <h2 class="wv-h2">Ready to find your photos?</h2>
            @if ($isFaceSearchEnabled)
                <p class="wv-lead">Search by event name, bib number or selfie.</p>
            @else
                <p class="wv-lead">Search by event name or bib number.</p>
            @endif
            <div class="wv-button-row">
                <a href="{{ route('athletes') }}" class="wv-btn wv-btn--primary">Find My Photos</a>
            </div>
        </div>
    </section>
@endsection
```

- [ ] **Step 7: Replace the `/* Home photos */` block's `.wv-hero__image` rule and append homepage composition**

Delete the `.wv-hero__image` rule. Keep `.wv-card__image`, and change its `border-radius` to `var(--wivor-radius)`. Append at the end of the file:

```css
/* Homepage reference */
.wv-hero { padding: 40px 0 0; }

.wv-hero__grid { display: grid; gap: 32px; }

.wv-hero__title { font-size: 40px; font-weight: 800; line-height: 1.1; }

.wv-hero .wv-search { margin-top: 24px; }

.wv-hero__media img {
    display: block;
    width: 100%;
    height: auto;
    aspect-ratio: 4 / 3;
    object-fit: cover;
    object-position: 50% 30%;
}

.wv-split { display: grid; gap: 32px; align-items: center; }

.wv-split__image {
    display: block;
    width: 100%;
    height: auto;
    aspect-ratio: 4 / 3;
    object-fit: cover;
    border-radius: var(--wivor-radius-panel);
}

.wv-steps {
    margin: 24px 0;
    padding: 0;
    list-style: none;
    counter-reset: wv-step;
}

.wv-steps li {
    position: relative;
    padding: 0 0 20px 48px;
    counter-increment: wv-step;
}

.wv-steps li::before {
    content: counter(wv-step);
    position: absolute;
    top: 0;
    left: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--wivor-ink);
    color: var(--wivor-on-dark);
    font-family: var(--wivor-font-heading);
    font-size: 15px;
    font-weight: 700;
    line-height: 32px;
    text-align: center;
}

.wv-reassurance { display: grid; gap: 24px; margin-top: 32px; }

@media (min-width: 768px) {
    .wv-reassurance { grid-template-columns: repeat(3, 1fr); }
}

@media (min-width: 1024px) {
    .wv-hero { padding: 0; }

    .wv-hero__grid {
        grid-template-columns: 48fr 52fr;
        align-items: center;
        gap: 56px;
        max-width: none;
        padding-right: 0;
        padding-left: max(32px, calc((100vw - var(--wivor-content-width)) / 2 + 32px));
    }

    .wv-hero__title { font-size: clamp(2.5rem, 5vw, 4rem); }

    .wv-hero__media img { height: 600px; aspect-ratio: auto; }

    .wv-split { grid-template-columns: 1fr 1fr; gap: 64px; }
}
```

- [ ] **Step 8: Run the tests to verify they pass**

Run: `php artisan test --filter='HomePageTest|FrontendTruthAlignmentTest'`
Expected: PASS, apart from the pre-existing `authenticated legacy view does not advertise video services` failure.

- [ ] **Step 9: Commit**

```bash
git add public/assets/css/wivor-brand.css resources/views/frontend/v1/components/event-search.blade.php resources/views/frontend/v1/home/index.blade.php tests/Feature/HomePageTest.php tests/Feature/FrontendTruthAlignmentTest.php
git commit -m "Rebuild the homepage as the design system reference"
```

---

### Task 5: Phase 1 verification and report

**Files:**
- Modify only if verification finds a defect from Tasks 1–4.

**Interfaces:**
- Consumes: everything above.
- Produces: the guide section 20 report and the approved reference for plan 2.

- [ ] **Step 1: Run the full feature suite**

Run: `php artisan test --testsuite=Feature`
Expected: 2 failures at most, the same pre-existing tests as the baseline: `FrontendTruthAlignmentTest > authenticated legacy view does not advertise video services` and `WebsitePolicyTest > admin can open all website policy editors from the admin menu`.

- [ ] **Step 2: Start the app against a throwaway SQLite database**

The real environment file may point at live services, so override every external connection. Confirm first that `bootstrap/cache/config.php` does not exist, because a cached config ignores these overrides. Set `SCRATCHPAD` to the session scratchpad directory.

```bash
test ! -f bootstrap/cache/config.php
touch "$SCRATCHPAD/wivor-visual-qa.sqlite"
export DB_CONNECTION=sqlite DB_DATABASE="$SCRATCHPAD/wivor-visual-qa.sqlite" SESSION_DRIVER=file CACHE_STORE=file QUEUE_CONNECTION=sync MAIL_MAILER=log
php artisan migrate --force
php artisan serve --port=8123
```

Use the `/run` skill or a background command for the server. Do not complete any purchase, upload, or email action during visual QA.

- [ ] **Step 3: Inspect the homepage in a browser**

Check `http://127.0.0.1:8123/` at 360, 390, 768, 1024 and 1440px wide, and in one landscape phone size. At each width run this in the page console:

```js
document.documentElement.scrollWidth <= document.documentElement.clientWidth
```

Expected: `true` at every width. Also confirm:
- The hero, H1 and search fit the first viewport at 390px.
- The hero image is sharp at 1440px. It is only 1200px wide, so note softness in the report.
- Computed font of `h1` is Manrope and of `p` is Inter.
- Keyboard: Tab reaches every link with a visible ring. On a narrow width, Enter opens the menu and Escape closes it and returns focus to the toggle.
- Reduced motion mode shows no transition on buttons.
- Submitting the hero search reaches `/list-events?search=...`.
- The console shows no JS errors and no failed images.

- [ ] **Step 4: Check the portal for leaks**

Create a local test login in the throwaway database, then load a portal page and `/how-to-upload-photos`. Confirm the accent and sidebar use the new colors, the font is Inter or Manrope, and the layout did not shift. Report anything broken. Do not change portal markup in this plan.

- [ ] **Step 5: Check for scope leaks**

Run:

```bash
git status --short
git diff main --stat
git diff main --name-only | grep -E "^(app|database|routes|config|resources/views/admin|resources/views/layouts)" || echo "no backend or admin files changed"
```

Expected: `no backend or admin files changed`.

- [ ] **Step 6: Report using guide section 20**

Include: whether `/frontend-design` ran, files changed, tests with results, widths checked, the unverified image license, the skipped homepage live events follow-up, and any hardcoded portal accents from Task 1 Step 4. Stop for approval of the homepage before writing plan 2.
