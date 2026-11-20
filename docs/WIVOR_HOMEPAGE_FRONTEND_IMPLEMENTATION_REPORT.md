# WiVor homepage frontend implementation report

Date: 2026-11-20. Branch: `homepage-2026-athlete-first`. Brief: `docs/WIVOR_FRONTEND_2026_HOMEPAGE_MASTER_PLAN.md`.
The `/frontend-design` skill was invoked for the design plan and the build.

## Decisions confirmed with the owner

1. **Fonts:** Barlow Condensed and Manrope apply to the homepage only. Other public pages keep Manrope and Inter, as `docs/frontend/00-frontend-guide.md` says. The two documents conflicted, so the owner chose this.
2. **Hero photo:** the build uses the existing 1200×494 file now. A larger original can replace it later.
3. **Caps:** H1 and H2 are uppercase through CSS only. The HTML stays in sentence case.

## Changed files

| File | Change |
|---|---|
| `resources/views/frontend/v1/home/index.blade.php` | Rebuilt the content section. Added a homepage-only Barlow Condensed font link. The SEO, meta and structured data are unchanged. |
| `public/assets/css/wivor-brand.css` | Replaced the old homepage-only block (`.wv-hero`, `.wv-split`, `.wv-steps`, `.wv-band`, `.wv-reassurance`, `.wv-photographers`) with the new `.wv-home-*` block. Shared rules are unchanged. |
| `tests/Feature/HomePageTest.php` | Updated copy and link assertions. Added tests for the organizer photo, eager hero loading and the homepage-only font. |
| `tests/Feature/BrandAssetsTest.php` | Pointed the keyframe and reduced-motion assertions at the new `wv-home-rise` animation. The orange rule under the title was removed. |
| `tests/Feature/FrontendTruthAlignmentTest.php` | Updated the bib-only copy strings. Removed one assertion for the old closing line "Search by event name or bib number.", which no longer exists. `assertDontSee('selfie')` still guards the bib-only state. |
| `docs/WIVOR_FRONTEND_DESIGN_SYSTEM.md` | New: tokens, type scale, spacing and motion. |
| `docs/img/homepage-2026/*.png` | Before and after screenshots at 1440×900 and 390×844. |

## What was not touched

- Routes, controllers, models, migrations, policies, services, checkout, cart, upload and recognition code.
- The event search contract: `GET route('events.listEvents')` with `name="search"`. The `components/event-search` partial is unchanged and is only restyled inside the hero.
- The header, footer, cookie banner and navigation JS. These are shared by every public page, so they are out of scope under the homepage-only decision.
- Brand colors and logos.

## Homepage sections

1. **Hero:** full-bleed race photo, the "Your race. Your moment. Your photos." H1, the real event search and a "See how it works" link. The photographer link moved out of the hero so the first screen serves athletes only.
2. **How it works:** three steps with race-bib numerals and a link to `/how-it-works`.
3. **Events:** a band that links to `/list-events`. The homepage receives no event data, so there are no event cards (see deferred items).
4. **Story:** the athletes photo with "The race ends. The feeling stays."
5. **Photographers:** 50/50 photo and copy. The CTA goes to `/photographers`. The "no sign-up fee, commission on each sale" wording is kept.
6. **Organizers:** a light panel with the crowd photo. The CTA goes to `/organizers`.
7. **Purchase facts:** preview before you buy, choose the photos you love, and selfie consent with links to the Biometric Notice and the Privacy Policy. The selfie content shows only when `face_recognition.enabled` is on.
8. **Closing band:** solid `#ff3d00` with ink type. "Find My Photos" goes to `/list-events`.

No testimonials, statistics, partner logos or invented events were added.

## How it was verified

- `php artisan test`: 500 passed (2192 assertions).
- Layout was measured in headless Chrome through the DevTools Protocol at 320, 375, 390, 768, 1024, 1366, 1440 and 1920px:
  - No horizontal overflow at any width.
  - The H1 stays at exactly 3 lines.
  - The Barlow Condensed font loads.
  - The hero search button is above the fold at every size: 587px of 640, 606 of 667, 618 of 844, 583 of 768 (1024 wide), 633 of 768 (1366 wide), and 648 of 900.
- Screenshots were reviewed at all sizes above, with reduced motion forced.

## Not verified

- Lighthouse, LCP, INP and CLS were not measured. No field data was checked.
- 200% browser text zoom, screen reader passes and real mobile devices were not tested.
- Keyboard focus was checked in CSS only, not walked through in a browser.
- Text contrast over the photo was estimated from the gradient values, not measured pixel by pixel.

## Risks and follow-ups

- **Shared header button contrast:** the "Find My Photos" button in the header has white 15px text on `#ff3d00`, which is 3.55:1 and fails WCAG AA for normal text. Fixing it changes the shared header, so it needs approval.
- **Hero source resolution:** at 1366px and wider, the 1200px photo is upscaled about 1.4× and looks soft. A larger original (2400px or wider) is needed. To add one, wrap the hero `<img>` in a `<picture>` with a WebP or AVIF `srcset`.
- **First visit on small phones:** the cookie banner covers the bottom of the hero search on 375×667 until the visitor accepts or rejects. This is existing behavior.
- **Extra font request:** Barlow Condensed adds one Google Fonts request on the homepage only.

## Deferred (needs backend or a separate assignment)

- Real event cards on the homepage. `HomeController@welcome` would have to pass event data, which is a controller change.
- Header, footer and mobile drawer polish (focus trap, body scroll lock). These are shared across all pages.
- Barlow Condensed on other public pages. This is a site-wide type decision against the existing guide.
